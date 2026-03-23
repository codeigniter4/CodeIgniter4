<?php

declare(strict_types=1);

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
use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Exceptions\LogicException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Method;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\I18n\Time;
use CodeIgniter\Security\Exceptions\SecurityException;
use CodeIgniter\Session\Session;
use Config\Cookie as CookieConfig;
use Config\Security as SecurityConfig;
use ErrorException;
use JsonException;
use SensitiveParameter;

/**
 * Class Security
 *
 * Provides methods that help protect your site against
 * Cross-Site Request Forgery attacks.
 *
 * @see \CodeIgniter\Security\SecurityTest
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
     *
     * @deprecated 4.4.0 Use $this->config->csrfProtection.
     */
    protected $csrfProtection = self::CSRF_PROTECTION_COOKIE;

    /**
     * CSRF Token Randomization
     *
     * @var bool
     *
     * @deprecated 4.4.0 Use $this->config->tokenRandomize.
     */
    protected $tokenRandomize = false;

    /**
     * CSRF Hash (without randomization)
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
     *
     * @deprecated 4.4.0 Use $this->config->tokenName.
     */
    protected $tokenName = 'csrf_token_name';

    /**
     * CSRF Header Name
     *
     * Header name for Cross Site Request Forgery protection.
     *
     * @var string
     *
     * @deprecated 4.4.0 Use $this->config->headerName.
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
     *
     * @deprecated 4.4.0 Use $this->config->expires.
     */
    protected $expires = 7200;

    /**
     * CSRF Regenerate
     *
     * Regenerate CSRF Token on every request.
     *
     * @var bool
     *
     * @deprecated 4.4.0 Use $this->config->regenerate.
     */
    protected $regenerate = true;

    /**
     * CSRF Redirect
     *
     * Redirect to previous page with error on failure.
     *
     * @var bool
     *
     * @deprecated 4.4.0 Use $this->config->redirect.
     */
    protected $redirect = false;

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

    private readonly IncomingRequest $request;

    /**
     * CSRF Cookie Name without Prefix
     */
    private ?string $rawCookieName = null;

    /**
     * Session instance.
     */
    private ?Session $session = null;

    /**
     * CSRF Hash in Request Cookie
     *
     * The cookie value is always CSRF hash (without randomization) even if
     * $tokenRandomize is true.
     */
    private ?string $hashInCookie = null;

    /**
     * Security Config
     */
    protected SecurityConfig $config;

    /**
     * Constructor.
     *
     * Stores our configuration and fires off the init() method to setup
     * initial state.
     */
    public function __construct(SecurityConfig $config)
    {
        $this->config = $config;

        $this->rawCookieName = $config->cookieName;

        if ($this->isCSRFCookie()) {
            $cookie = config(CookieConfig::class);

            $this->configureCookie($cookie);
        } else {
            // Session based CSRF protection
            $this->configureSession();
        }

        $this->request      = service('request');
        $this->hashInCookie = $this->request->getCookie($this->cookieName);

        $this->restoreHash();
        if ($this->hash === null) {
            $this->generateHash();
        }
    }

    private function isCSRFCookie(): bool
    {
        return $this->config->csrfProtection === self::CSRF_PROTECTION_COOKIE;
    }

    private function configureSession(): void
    {
        $this->session = service('session');
    }

    private function configureCookie(CookieConfig $cookie): void
    {
        $cookiePrefix     = $cookie->prefix;
        $this->cookieName = $cookiePrefix . $this->rawCookieName;
        Cookie::setDefaults($cookie);
    }

    public function verify(RequestInterface $request)
    {
        $method = $request->getMethod();

        // Protect POST, PUT, DELETE, PATCH requests only
        if (! in_array($method, [Method::POST, Method::PUT, Method::DELETE, Method::PATCH], true)) {
            return $this;
        }

        assert($request instanceof IncomingRequest);

        $postedToken = $this->getPostedToken($request);

        try {
            $token = $postedToken !== null && $this->config->tokenRandomize
                ? $this->derandomize($postedToken)
                : $postedToken;
        } catch (InvalidArgumentException) {
            $token = null;
        }

        if (! isset($token, $this->hash) || ! hash_equals($this->hash, $token)) {
            throw SecurityException::forDisallowedAction();
        }

        $this->removeTokenInRequest($request);

        if ($this->config->regenerate) {
            $this->generateHash();
        }

        log_message('info', 'CSRF token verified.');

        return $this;
    }

    /**
     * Remove token in POST or JSON request data
     */
    private function removeTokenInRequest(IncomingRequest $request): void
    {
        $superglobals = service('superglobals');
        $tokenName    = $this->config->tokenName;

        // If the token is found in POST data, we can safely remove it.
        if (is_string($superglobals->post($tokenName))) {
            $superglobals->unsetPost($tokenName);
            $request->setGlobal('post', $superglobals->getPostArray());

            return;
        }

        $body = $request->getBody() ?? '';

        if ($body === '') {
            return;
        }

        // If the token is found in JSON data, we can safely remove it.
        try {
            $json = json_decode($body, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $json = null;
        }

        if (is_object($json)) {
            if (property_exists($json, $tokenName)) {
                unset($json->{$tokenName});
                $request->setBody(json_encode($json));
            }

            return;
        }

        // If the token is found in form-encoded data, we can safely remove it.
        parse_str($body, $result);

        unset($result[$tokenName]);
        $request->setBody(http_build_query($result));
    }

    private function getPostedToken(IncomingRequest $request): ?string
    {
        $tokenName  = $this->config->tokenName;
        $headerName = $this->config->headerName;

        // 1. Check POST data first.
        $token = $request->getPost($tokenName);

        if ($this->isNonEmptyTokenString($token)) {
            return $token;
        }

        // 2. Check header data next.
        if ($request->hasHeader($headerName)) {
            $token = $request->header($headerName)->getValue();

            if ($this->isNonEmptyTokenString($token)) {
                return $token;
            }
        }

        // 3. Finally, check the raw input data for JSON or form-encoded data.
        $body = $request->getBody() ?? '';

        if ($body === '') {
            return null;
        }

        // 3a. Check if a JSON payload exists and contains the token.
        try {
            $json = json_decode($body, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $json = null;
        }

        if (is_object($json) && property_exists($json, $tokenName)) {
            $token = $json->{$tokenName};

            if ($this->isNonEmptyTokenString($token)) {
                return $token;
            }
        }

        // 3b. Check if form-encoded data exists and contains the token.
        parse_str($body, $result);
        $token = $result[$tokenName] ?? null;

        if ($this->isNonEmptyTokenString($token)) {
            return $token;
        }

        return null;
    }

    /**
     * @phpstan-assert-if-true non-empty-string $token
     */
    private function isNonEmptyTokenString(mixed $token): bool
    {
        return is_string($token) && $token !== '';
    }

    /**
     * Returns the CSRF Token.
     */
    public function getHash(): ?string
    {
        return $this->config->tokenRandomize ? $this->randomize($this->hash) : $this->hash;
    }

    /**
     * Randomize hash to avoid BREACH attacks.
     *
     * @params string $hash CSRF hash
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
     * @params string $token CSRF token
     *
     * @return string CSRF hash
     *
     * @throws InvalidArgumentException "hex2bin(): Hexadecimal input string must have an even length"
     */
    protected function derandomize(#[SensitiveParameter] string $token): string
    {
        $key   = substr($token, -static::CSRF_HASH_BYTES * 2);
        $value = substr($token, 0, static::CSRF_HASH_BYTES * 2);

        try {
            return bin2hex((string) hex2bin($value) ^ (string) hex2bin($key));
        } catch (ErrorException $e) {
            // "hex2bin(): Hexadecimal input string must have an even length"
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Returns the CSRF Token Name.
     */
    public function getTokenName(): string
    {
        return $this->config->tokenName;
    }

    /**
     * Returns the CSRF Header Name.
     */
    public function getHeaderName(): string
    {
        return $this->config->headerName;
    }

    /**
     * Returns the CSRF Cookie Name.
     */
    public function getCookieName(): string
    {
        return $this->config->cookieName;
    }

    /**
     * Check if request should be redirect on failure.
     */
    public function shouldRedirect(): bool
    {
        return $this->config->redirect;
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
     * parameter, $relativePath to TRUE.
     *
     * @deprecated 4.6.2 Use `sanitize_filename()` instead
     *
     * @param string $str          Input file name
     * @param bool   $relativePath Whether to preserve paths
     */
    public function sanitizeFilename(string $str, bool $relativePath = false): string
    {
        helper('security');

        return sanitize_filename($str, $relativePath);
    }

    /**
     * Restore hash from Session or Cookie
     */
    private function restoreHash(): void
    {
        if ($this->isCSRFCookie()) {
            if ($this->isHashInCookie()) {
                $this->hash = $this->hashInCookie;
            }
        } elseif ($this->session->has($this->config->tokenName)) {
            // Session based CSRF protection
            $this->hash = $this->session->get($this->config->tokenName);
        }
    }

    /**
     * Generates (Regenerates) the CSRF Hash.
     */
    public function generateHash(): string
    {
        $this->hash = bin2hex(random_bytes(static::CSRF_HASH_BYTES));

        if ($this->isCSRFCookie()) {
            $this->saveHashInCookie();
        } else {
            // Session based CSRF protection
            $this->saveHashInSession();
        }

        return $this->hash;
    }

    private function isHashInCookie(): bool
    {
        if ($this->hashInCookie === null) {
            return false;
        }

        $length  = static::CSRF_HASH_BYTES * 2;
        $pattern = '#^[0-9a-f]{' . $length . '}$#iS';

        return preg_match($pattern, $this->hashInCookie) === 1;
    }

    private function saveHashInCookie(): void
    {
        $this->cookie = new Cookie(
            $this->rawCookieName,
            $this->hash,
            [
                'expires' => $this->config->expires === 0 ? 0 : Time::now()->getTimestamp() + $this->config->expires,
            ],
        );

        $response = service('response');
        $response->setCookie($this->cookie);
    }

    private function saveHashInSession(): void
    {
        $this->session->set($this->config->tokenName, $this->hash);
    }
}
