<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Security;

use CodeIgniter\Cookie\Cookie;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Security\Exceptions\SecurityException;
use CodeIgniter\Session\Session;
use Config\App;
use Config\Cookie as CookieConfig;
use Config\Security as SecurityConfig;
use Config\Services;
use ErrorException;
use InvalidArgumentException;
use LogicException;

/**
 * Class Security
 *
 * Provides methods that help protect your site against
 * Cross-Site Request Forgery attacks.
 */
class Security implements SecurityInterface
{
    public const CSRF_PROTECTION_COOKIE  = 'cookie';
    public const CSRF_PROTECTION_SESSION = 'session';
    protected const CSRF_HASH_BYTES      = 16;

    /**
     * CSRF Protection Method
     *
     * Protection Method for Cross Site Request Forgery protection.
     *
     * @var string 'cookie' or 'session'
     */
    protected $csrfProtection = self::CSRF_PROTECTION_COOKIE;

    /**
     * CSRF Token Randomization
     *
     * @var bool
     */
    protected $tokenRandomize = false;

    /**
     * CSRF Hash
     *
     * Random hash for Cross Site Request Forgery protection.
     * The Hash value is different from the Token value when Token Randomization is true.
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
     * CSRF Cookie Name (with Prefix)
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
     * @deprecated `Config\Cookie` $samesite property is used.
     */
    protected $samesite = Cookie::SAMESITE_LAX;

    protected IncomingRequest $request;

    /**
     * CSRF Cookie Name without Prefix
     */
    protected ?string $rawCookieName = null;

    /**
     * Session instance.
     */
    protected ?Session $session = null;

    /**
     * CSRF Token in Cookie
     */
    protected ?string $tokenInCookie = null;

    /**
     * Constructor.
     *
     * Stores our configuration and fires off the init() method to setup
     * initial state.
     */
    public function __construct(App $config)
    {
        /** @var SecurityConfig|null $security */
        $security = config('Security');

        // Store CSRF-related configurations
        if ($security instanceof SecurityConfig) {
            $this->csrfProtection = $security->csrfProtection ?? $this->csrfProtection;
            $this->tokenName      = $security->tokenName ?? $this->tokenName;
            $this->headerName     = $security->headerName ?? $this->headerName;
            $this->regenerate     = $security->regenerate ?? $this->regenerate;
            $this->redirect       = $security->redirect ?? $this->redirect;
            $this->rawCookieName  = $security->cookieName ?? $this->rawCookieName;
            $this->expires        = $security->expires ?? $this->expires;
            $this->tokenRandomize = $security->tokenRandomize ?? $this->tokenRandomize;
        } else {
            // `Config/Security.php` is absence
            $this->tokenName     = $config->CSRFTokenName ?? $this->tokenName;
            $this->headerName    = $config->CSRFHeaderName ?? $this->headerName;
            $this->regenerate    = $config->CSRFRegenerate ?? $this->regenerate;
            $this->rawCookieName = $config->CSRFCookieName ?? $this->rawCookieName;
            $this->expires       = $config->CSRFExpire ?? $this->expires;
            $this->redirect      = $config->CSRFRedirect ?? $this->redirect;
        }

        if ($this->isCSRFCookie()) {
            $this->configureCookie($config);
        } else {
            // Session based CSRF protection
            $this->configureSession();
        }

        $this->request       = Services::request();
        $this->tokenInCookie = $this->request->getCookie($this->cookieName);

        if ($this->hash === null) {
            $this->generateHash();
        }
    }

    private function isCSRFCookie(): bool
    {
        return $this->csrfProtection === self::CSRF_PROTECTION_COOKIE;
    }

    private function configureSession(): void
    {
        $this->session = Services::session();
    }

    private function configureCookie(App $config): void
    {
        /** @var CookieConfig|null $cookie */
        $cookie = config('Cookie');

        if ($cookie instanceof CookieConfig) {
            $cookiePrefix     = $cookie->prefix;
            $this->cookieName = $cookiePrefix . $this->rawCookieName;
            Cookie::setDefaults($cookie);
        } else {
            // `Config/Cookie.php` is absence
            $cookiePrefix     = $config->cookiePrefix;
            $this->cookieName = $cookiePrefix . $this->rawCookieName;
        }
    }

    /**
     * CSRF Verify
     *
     * @throws SecurityException
     *
     * @return $this|false
     *
     * @deprecated Use `CodeIgniter\Security\Security::verify()` instead of using this method.
     *
     * @codeCoverageIgnore
     */
    public function CSRFVerify(RequestInterface $request)
    {
        return $this->verify($request);
    }

    /**
     * Returns the CSRF token.
     *
     * @deprecated Use `CodeIgniter\Security\Security::getHash()` instead of using this method.
     *
     * @codeCoverageIgnore
     */
    public function getCSRFHash(): ?string
    {
        return $this->getHash();
    }

    /**
     * Returns the CSRF Token Name.
     *
     * @deprecated Use `CodeIgniter\Security\Security::getTokenName()` instead of using this method.
     *
     * @codeCoverageIgnore
     */
    public function getCSRFTokenName(): string
    {
        return $this->getTokenName();
    }

    /**
     * CSRF Verify
     *
     * @throws SecurityException
     *
     * @return $this
     */
    public function verify(RequestInterface $request)
    {
        // Protects POST, PUT, DELETE, PATCH
        $method           = strtoupper($request->getMethod());
        $methodsToProtect = ['POST', 'PUT', 'DELETE', 'PATCH'];
        if (! in_array($method, $methodsToProtect, true)) {
            return $this;
        }

        $postedToken = $this->getPostedToken($request);

        try {
            $hash = ($postedToken !== null && $this->tokenRandomize)
                ? $this->derandomize($postedToken) : $postedToken;
        } catch (InvalidArgumentException $e) {
            $hash = null;
        }

        // Do the tokens match?
        if (! isset($hash, $this->hash) || ! hash_equals($this->hash, $hash)) {
            throw SecurityException::forDisallowedAction();
        }

        $json = json_decode($request->getBody() ?? '');

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
            if ($this->isCSRFCookie()) {
                $this->tokenInCookie = null;
            } else {
                // Session based CSRF protection
                $this->session->remove($this->tokenName);
            }
        }

        if ($this->hash === null) {
            $this->generateHash();
        }

        log_message('info', 'CSRF token verified.');

        return $this;
    }

    private function getPostedToken(RequestInterface $request): ?string
    {
        // Does the token exist in POST, HEADER or optionally php:://input - json data.
        if ($request->hasHeader($this->headerName) && ! empty($request->header($this->headerName)->getValue())) {
            $tokenName = $request->header($this->headerName)->getValue();
        } else {
            $body = (string) $request->getBody();
            $json = json_decode($body);

            if ($body !== '' && ! empty($json) && json_last_error() === JSON_ERROR_NONE) {
                $tokenName = $json->{$this->tokenName} ?? null;
            } else {
                $tokenName = null;
            }
        }

        return $request->getPost($this->tokenName) ?? $tokenName;
    }

    /**
     * Returns the CSRF token.
     *
     * @deprecated Use getToken()
     */
    public function getHash(): ?string
    {
        return $this->getToken();
    }

    /**
     * Returns the CSRF token.
     */
    public function getToken(): ?string
    {
        return $this->tokenRandomize ? $this->randomize($this->hash) : $this->hash;
    }

    /**
     * Randomize hash to avoid BREACH attacks.
     *
     * @return string CSRF token
     */
    protected function randomize(string $hash): string
    {
        $keyBinary  = random_bytes(static::CSRF_HASH_BYTES);
        $hashBinary = hex2bin($hash);

        if ($hashBinary === false) {
            throw new LogicException('$hash is invalid: ' . $hash);
        }

        return bin2hex(($hashBinary ^ $keyBinary) . $keyBinary);
    }

    /**
     * Derandomize the token.
     *
     * @throws InvalidArgumentException "hex2bin(): Hexadecimal input string must have an even length"
     *
     * @return string CSRF hash
     */
    protected function derandomize(string $token): string
    {
        $key   = substr($token, -static::CSRF_HASH_BYTES * 2);
        $value = substr($token, 0, static::CSRF_HASH_BYTES * 2);

        try {
            return bin2hex(hex2bin($value) ^ hex2bin($key));
        } catch (ErrorException $e) {
            // "hex2bin(): Hexadecimal input string must have an even length"
            throw new InvalidArgumentException($e->getMessage());
        }
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
     * Sanitize Filename
     *
     * Tries to sanitize filenames in order to prevent directory traversal attempts
     * and other security threats, which is particularly useful for files that
     * were supplied via user input.
     *
     * If it is acceptable for the user input to include relative paths,
     * e.g. file/in/some/approved/folder.txt, you can set the second optional
     * parameter, $relative_path to TRUE.
     *
     * @param string $str          Input file name
     * @param bool   $relativePath Whether to preserve paths
     */
    public function sanitizeFilename(string $str, bool $relativePath = false): string
    {
        // List of sanitize filename strings
        $bad = [
            '../',
            '<!--',
            '-->',
            '<',
            '>',
            "'",
            '"',
            '&',
            '$',
            '#',
            '{',
            '}',
            '[',
            ']',
            '=',
            ';',
            '?',
            '%20',
            '%22',
            '%3c',
            '%253c',
            '%3e',
            '%0e',
            '%28',
            '%29',
            '%2528',
            '%26',
            '%24',
            '%3f',
            '%3b',
            '%3d',
        ];

        if (! $relativePath) {
            $bad[] = './';
            $bad[] = '/';
        }

        $str = remove_invisible_characters($str, false);

        do {
            $old = $str;
            $str = str_replace($bad, '', $str);
        } while ($old !== $str);

        return stripslashes($str);
    }

    /**
     * Generates the CSRF Hash.
     */
    protected function generateHash(): string
    {
        // If the cookie exists we will use its value.
        // We don't necessarily want to regenerate it with
        // each page load since a page could contain embedded
        // sub-pages causing this feature to fail
        if ($this->isCSRFCookie()) {
            if ($this->isTokenInCookie()) {
                try {
                    return $this->hash = $this->tokenRandomize
                        ? $this->derandomize($this->tokenInCookie)
                        : $this->tokenInCookie;
                } catch (InvalidArgumentException $e) {
                    // Invalid token
                }
            }
        } elseif ($this->session->has($this->tokenName)) {
            // Session based CSRF protection
            return $this->hash = $this->session->get($this->tokenName);
        }

        $this->hash = bin2hex(random_bytes(static::CSRF_HASH_BYTES));

        if ($this->isCSRFCookie()) {
            $this->saveTokenInCookie();
        } else {
            // Session based CSRF protection
            $this->saveHashInSession();
        }

        return $this->hash;
    }

    private function isTokenInCookie(): bool
    {
        return isset($this->tokenInCookie) && is_string($this->tokenInCookie)
            && preg_match('#^[0-9a-f]{32,64}$#iS', $this->tokenInCookie) === 1;
    }

    private function saveTokenInCookie(): void
    {
        $this->cookie = new Cookie(
            $this->rawCookieName,
            $this->getToken(),
            [
                'expires' => $this->expires === 0 ? 0 : time() + $this->expires,
            ]
        );

        /** @var Response $response */
        $response = Services::response();
        $response->setCookie($this->cookie);
    }

    /**
     * CSRF Send Cookie
     *
     * @return false|Security
     *
     * @deprecated Set cookies to Response object instead.
     */
    protected function sendCookie(RequestInterface $request)
    {
        if ($this->cookie->isSecure() && ! $request->isSecure()) {
            return false;
        }

        $this->doSendCookie();
        log_message('info', 'CSRF cookie sent.');

        return $this;
    }

    /**
     * Actual dispatching of cookies.
     * Extracted for this to be unit tested.
     *
     * @codeCoverageIgnore
     *
     * @deprecated Set cookies to Response object instead.
     */
    protected function doSendCookie(): void
    {
        cookies([$this->cookie], false)->dispatch();
    }

    private function saveHashInSession(): void
    {
        $this->session->set($this->tokenName, $this->hash);
    }
}
