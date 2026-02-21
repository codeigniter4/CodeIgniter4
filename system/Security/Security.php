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
     * CSRF Hash (without randomization)
     *
     * Random hash for Cross Site Request Forgery protection.
     *
     * @var string|null
     */
    protected $hash;

    /**
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

    protected SecurityConfig $config;

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

    public function getHash(): ?string
    {
        return $this->config->tokenRandomize ? $this->randomize($this->hash) : $this->hash;
    }

    public function getTokenName(): string
    {
        return $this->config->tokenName;
    }

    public function getHeaderName(): string
    {
        return $this->config->headerName;
    }

    public function getCookieName(): string
    {
        return $this->config->cookieName;
    }

    public function shouldRedirect(): bool
    {
        return $this->config->redirect;
    }

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

    /**
     * Randomize hash to avoid BREACH attacks.
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
     * @throws InvalidArgumentException
     */
    protected function derandomize(#[SensitiveParameter] string $token): string
    {
        $key   = substr($token, -static::CSRF_HASH_BYTES * 2);
        $value = substr($token, 0, static::CSRF_HASH_BYTES * 2);

        try {
            return bin2hex((string) hex2bin($value) ^ (string) hex2bin($key));
        } catch (ErrorException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
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

    /**
     * Remove token in POST, JSON, or form-encoded data to prevent it from being accidentally leaked.
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

        if (is_object($json) && property_exists($json, $tokenName)) {
            unset($json->{$tokenName});
            $request->setBody(json_encode($json));

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
