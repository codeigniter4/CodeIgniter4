<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Security\CSRF;

use CodeIgniter\Cookie\Cookie;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Security\Exceptions\SecurityException;
use Config\Cookie as CookieConfig;
use Config\Security as SecurityConfig;

use function cookies;
use function log_message;

/**
 * Provides methods that help protect your site against
 * Cross-Site Request Forgery attacks with Cookie.
 */
class CSRFCookie
{
    /**
     * CSRF Hash
     *
     * Random hash for Cross Site Request Forgery protection.
     *
     * @var string|null
     */
    protected $hash;

    /**
     * CSRF Token Name
     *
     * Token name for Cross Site Request Forgery protection.
     *
     * @var string
     */
    protected $tokenName = 'csrf_token_name';

    /**
     * CSRF Header Name
     *
     * Header name for Cross Site Request Forgery protection.
     *
     * @var string
     */
    protected $headerName = 'X-CSRF-TOKEN';

    /**
     * The CSRF Cookie instance.
     *
     * @var Cookie
     */
    protected $cookie;

    /**
     * CSRF Cookie Name
     *
     * Cookie name for Cross Site Request Forgery protection.
     *
     * @var string
     */
    protected $cookieName = 'csrf_cookie_name';

    /**
     * CSRF Expires
     *
     * Expiration time for Cross Site Request Forgery protection cookie.
     *
     * Defaults to two hours (in seconds).
     *
     * @var int
     *
     * @deprecated
     */
    protected $expires = 7200;

    /**
     * CSRF Regenerate
     *
     * Regenerate CSRF Token on every request.
     *
     * @var bool
     */
    protected $regenerate = true;

    /**
     * CSRF Redirect
     *
     * Redirect to previous page with error on failure.
     *
     * @var bool
     */
    protected $redirect = true;

    /**
     * CSRF SameSite
     *
     * Setting for CSRF SameSite cookie token.
     *
     * Allowed values are: None - Lax - Strict - ''.
     *
     * Defaults to `Lax` as recommended in this link:
     *
     * @see https://portswigger.net/web-security/csrf/samesite-cookies
     *
     * @var string
     *
     * @deprecated
     */
    protected $samesite = Cookie::SAMESITE_LAX;

    /**
     * @param CSRFConfig|SecurityConfig    $csrfConfig
     * @param CookieConfig|TmpCookieConfig $cookieConfig
     */
    public function __construct($csrfConfig, $cookieConfig)
    {
        $this->tokenName  = $csrfConfig->tokenName;
        $this->headerName = $csrfConfig->headerName;
        $this->regenerate = $csrfConfig->regenerate;
        $this->redirect   = $csrfConfig->redirect;
        $this->cookieName = $cookieConfig->prefix . $csrfConfig->cookieName;

        Cookie::setDefaults($cookieConfig);
        $this->cookie = new Cookie($csrfConfig->cookieName, $this->generateHash(), [
            'expires' => $csrfConfig->expires === 0 ? 0 : time() + $csrfConfig->expires,
        ]);
    }

    /**
     * CSRF Verify
     *
     * @throws SecurityException
     */
    public function verify(RequestInterface $request): bool
    {
        // If it's not a POST request we will set the CSRF cookie.
        if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
            return $this->sendCookie($request);
        }

        // Does the token exist in POST, HEADER or optionally php:://input - json data.
        if ($request->hasHeader($this->headerName) && ! empty($request->getHeader($this->headerName)->getValue())) {
            $tokenName = $request->getHeader($this->headerName)->getValue();
        } else {
            $json = json_decode($request->getBody());

            if (! empty($request->getBody()) && ! empty($json) && json_last_error() === JSON_ERROR_NONE) {
                $tokenName = $json->{$this->tokenName} ?? null;
            } else {
                $tokenName = null;
            }
        }

        $token = $_POST[$this->tokenName] ?? $tokenName;

        // Does the tokens exist in both the POST/POSTed JSON and COOKIE arrays and match?
        if (! isset($token, $_COOKIE[$this->cookieName]) || ! hash_equals($token, $_COOKIE[$this->cookieName])) {
            throw SecurityException::forDisallowedAction();
        }

        if (isset($_POST[$this->tokenName])) {
            // We kill this since we're done and we don't want to pollute the POST array.
            unset($_POST[$this->tokenName]);
            $request->setGlobal('post', $_POST);
        } elseif (isset($json->{$this->tokenName})) {
            // We kill this since we're done and we don't want to pollute the JSON data.
            unset($json->{$this->tokenName});
            $request->setBody(json_encode($json));
        }

        if ($this->regenerate) {
            $this->hash = null;
            unset($_COOKIE[$this->cookieName]);
        }

        $this->cookie = $this->cookie->withValue($this->generateHash());
        $this->sendCookie($request);

        log_message('info', 'CSRF token verified.');

        return true;
    }

    /**
     * Returns the CSRF Hash.
     */
    public function getHash(): ?string
    {
        return $this->hash;
    }

    /**
     * Returns the CSRF Token Name.
     */
    public function getTokenName(): string
    {
        return $this->tokenName;
    }

    /**
     * Returns the CSRF Header Name.
     */
    public function getHeaderName(): string
    {
        return $this->headerName;
    }

    /**
     * Returns the CSRF Cookie Name.
     */
    public function getCookieName(): string
    {
        return $this->cookieName;
    }

    /**
     * Check if CSRF cookie is expired.
     *
     * @deprecated
     *
     * @codeCoverageIgnore
     */
    public function isExpired(): bool
    {
        return $this->cookie->isExpired();
    }

    /**
     * Check if request should be redirect on failure.
     */
    public function shouldRedirect(): bool
    {
        return $this->redirect;
    }

    /**
     * Generates the CSRF Hash.
     */
    protected function generateHash(): string
    {
        if ($this->hash === null) {
            // If the cookie exists we will use its value.
            // We don't necessarily want to regenerate it with
            // each page load since a page could contain embedded
            // sub-pages causing this feature to fail
            if (isset($_COOKIE[$this->cookieName])
                && is_string($_COOKIE[$this->cookieName])
                && preg_match('#^[0-9a-f]{32}$#iS', $_COOKIE[$this->cookieName]) === 1
            ) {
                return $this->hash = $_COOKIE[$this->cookieName];
            }

            $this->hash = bin2hex(random_bytes(16));
        }

        return $this->hash;
    }

    /**
     * CSRF Send Cookie
     */
    protected function sendCookie(RequestInterface $request): bool
    {
        if ($this->cookie->isSecure() && ! $request->isSecure()) {
            return false;
        }

        $this->doSendCookie();
        log_message('info', 'CSRF cookie sent.');

        return true;
    }

    /**
     * Actual dispatching of cookies.
     * Extracted for this to be unit tested.
     *
     * @codeCoverageIgnore
     */
    protected function doSendCookie(): void
    {
        cookies([$this->cookie], false)->dispatch();
    }
}
