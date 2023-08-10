<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Cookie;

use ArrayAccess;
use CodeIgniter\Cookie\Exceptions\CookieException;
use CodeIgniter\I18n\Time;
use Config\Cookie as CookieConfig;
use DateTimeInterface;
use InvalidArgumentException;
use LogicException;
use ReturnTypeWillChange;

/**
 * A `Cookie` class represents an immutable HTTP cookie value object.
 *
 * Being immutable, modifying one or more of its attributes will return
 * a new `Cookie` instance, rather than modifying itself. Users should
 * reassign this new instance to a new variable to capture it.
 *
 * ```php
 * $cookie = new Cookie('test_cookie', 'test_value');
 * $cookie->getName(); // test_cookie
 *
 * $cookie->withName('prod_cookie');
 * $cookie->getName(); // test_cookie
 *
 * $cookie2 = $cookie->withName('prod_cookie');
 * $cookie2->getName(); // prod_cookie
 * ```
 *
 * @template-implements ArrayAccess<string, bool|int|string>
 */
class Cookie implements ArrayAccess, CloneableCookieInterface
{
    /**
     * @var string
     */
    protected $prefix = '';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var int Unix timestamp
     */
    protected $expires;

    /**
     * @var string
     */
    protected $path = '/';

    /**
     * @var string
     */
    protected $domain = '';

    /**
     * @var bool
     */
    protected $secure = false;

    /**
     * @var bool
     */
    protected $httponly = true;

    /**
     * @var string
     */
    protected $samesite = self::SAMESITE_LAX;

    /**
     * @var bool
     */
    protected $raw = false;

    /**
     * Default attributes for a Cookie object. The keys here are the
     * lowercase attribute names. Do not camelCase!
     *
     * @var array<string, bool|int|string>
     */
    private static array $defaults = [
        'prefix'   => '',
        'expires'  => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => false,
        'httponly' => true,
        'samesite' => self::SAMESITE_LAX,
        'raw'      => false,
    ];

    /**
     * A cookie name can be any US-ASCII characters, except control characters,
     * spaces, tabs, or separator characters.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie#attributes
     * @see https://tools.ietf.org/html/rfc2616#section-2.2
     */
    private static string $reservedCharsList = "=,; \t\r\n\v\f()<>@:\\\"/[]?{}";

    /**
     * Set the default attributes to a Cookie instance by injecting
     * the values from the `CookieConfig` config or an array.
     *
     * This method is called from Response::__construct().
     *
     * @param array<string, bool|int|string>|CookieConfig $config
     *
     * @return array<string, mixed> The old defaults array. Useful for resetting.
     */
    public static function setDefaults($config = [])
    {
        $oldDefaults = self::$defaults;
        $newDefaults = [];

        if ($config instanceof CookieConfig) {
            $newDefaults = [
                'prefix'   => $config->prefix,
                'expires'  => $config->expires,
                'path'     => $config->path,
                'domain'   => $config->domain,
                'secure'   => $config->secure,
                'httponly' => $config->httponly,
                'samesite' => $config->samesite,
                'raw'      => $config->raw,
            ];
        } elseif (is_array($config)) {
            $newDefaults = $config;
        }

        // This array union ensures that even if passed `$config` is not
        // `CookieConfig` or `array`, no empty defaults will occur.
        self::$defaults = $newDefaults + $oldDefaults;

        return $oldDefaults;
    }

    // =========================================================================
    // CONSTRUCTORS
    // =========================================================================

    /**
     * Create a new Cookie instance from a `Set-Cookie` header.
     *
     * @return static
     *
     * @throws CookieException
     */
    public static function fromHeaderString(string $cookie, bool $raw = false)
    {
        $data        = self::$defaults;
        $data['raw'] = $raw;

        $parts = preg_split('/\;[\s]*/', $cookie);
        $part  = explode('=', array_shift($parts), 2);

        $name  = $raw ? $part[0] : urldecode($part[0]);
        $value = isset($part[1]) ? ($raw ? $part[1] : urldecode($part[1])) : '';
        unset($part);

        foreach ($parts as $part) {
            if (strpos($part, '=') !== false) {
                [$attr, $val] = explode('=', $part);
            } else {
                $attr = $part;
                $val  = true;
            }

            $data[strtolower($attr)] = $val;
        }

        return new static($name, $value, $data);
    }

    /**
     * Construct a new Cookie instance.
     *
     * @param string                         $name    The cookie's name
     * @param string                         $value   The cookie's value
     * @param array<string, bool|int|string> $options The cookie's options
     *
     * @throws CookieException
     */
    final public function __construct(string $name, string $value = '', array $options = [])
    {
        $options += self::$defaults;

        $options['expires'] = static::convertExpiresTimestamp($options['expires']);

        // If both `Expires` and `Max-Age` are set, `Max-Age` has precedence.
        if (isset($options['max-age']) && is_numeric($options['max-age'])) {
            $options['expires'] = Time::now()->getTimestamp() + (int) $options['max-age'];
            unset($options['max-age']);
        }

        // to preserve backward compatibility with array-based cookies in previous CI versions
        $prefix = ($options['prefix'] === '') ? self::$defaults['prefix'] : $options['prefix'];
        $path   = $options['path'] ?: self::$defaults['path'];
        $domain = $options['domain'] ?: self::$defaults['domain'];

        // empty string SameSite should use the default for browsers
        $samesite = $options['samesite'] ?: self::$defaults['samesite'];

        $raw      = $options['raw'];
        $secure   = $options['secure'];
        $httponly = $options['httponly'];

        $this->validateName($name, $raw);
        $this->validatePrefix($prefix, $secure, $path, $domain);
        $this->validateSameSite($samesite, $secure);

        $this->prefix   = $prefix;
        $this->name     = $name;
        $this->value    = $value;
        $this->expires  = static::convertExpiresTimestamp($options['expires']);
        $this->path     = $path;
        $this->domain   = $domain;
        $this->secure   = $secure;
        $this->httponly = $httponly;
        $this->samesite = ucfirst(strtolower($samesite));
        $this->raw      = $raw;
    }

    // =========================================================================
    // GETTERS
    // =========================================================================

    /**
     * {@inheritDoc}
     */
    public function getId(): string
    {
        return implode(';', [$this->getPrefixedName(), $this->getPath(), $this->getDomain()]);
    }

    /**
     * {@inheritDoc}
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getPrefixedName(): string
    {
        $name = $this->getPrefix();

        if ($this->isRaw()) {
            $name .= $this->getName();
        } else {
            $search  = str_split(self::$reservedCharsList);
            $replace = array_map('rawurlencode', $search);

            $name .= str_replace($search, $replace, $this->getName());
        }

        return $name;
    }

    /**
     * {@inheritDoc}
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * {@inheritDoc}
     */
    public function getExpiresTimestamp(): int
    {
        return $this->expires;
    }

    /**
     * {@inheritDoc}
     */
    public function getExpiresString(): string
    {
        return gmdate(self::EXPIRES_FORMAT, $this->expires);
    }

    /**
     * {@inheritDoc}
     */
    public function isExpired(): bool
    {
        return $this->expires === 0 || $this->expires < Time::now()->getTimestamp();
    }

    /**
     * {@inheritDoc}
     */
    public function getMaxAge(): int
    {
        $maxAge = $this->expires - Time::now()->getTimestamp();

        return $maxAge >= 0 ? $maxAge : 0;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritDoc}
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * {@inheritDoc}
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     * {@inheritDoc}
     */
    public function isHTTPOnly(): bool
    {
        return $this->httponly;
    }

    /**
     * {@inheritDoc}
     */
    public function getSameSite(): string
    {
        return $this->samesite;
    }

    /**
     * {@inheritDoc}
     */
    public function isRaw(): bool
    {
        return $this->raw;
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions(): array
    {
        // This is the order of options in `setcookie`. DO NOT CHANGE.
        return [
            'expires'  => $this->expires,
            'path'     => $this->path,
            'domain'   => $this->domain,
            'secure'   => $this->secure,
            'httponly' => $this->httponly,
            'samesite' => $this->samesite ?: ucfirst(self::SAMESITE_LAX),
        ];
    }

    // =========================================================================
    // CLONING
    // =========================================================================

    /**
     * {@inheritDoc}
     */
    public function withPrefix(string $prefix = '')
    {
        $this->validatePrefix($prefix, $this->secure, $this->path, $this->domain);

        $cookie = clone $this;

        $cookie->prefix = $prefix;

        return $cookie;
    }

    /**
     * {@inheritDoc}
     */
    public function withName(string $name)
    {
        $this->validateName($name, $this->raw);

        $cookie = clone $this;

        $cookie->name = $name;

        return $cookie;
    }

    /**
     * {@inheritDoc}
     */
    public function withValue(string $value)
    {
        $cookie = clone $this;

        $cookie->value = $value;

        return $cookie;
    }

    /**
     * {@inheritDoc}
     */
    public function withExpires($expires)
    {
        $cookie = clone $this;

        $cookie->expires = static::convertExpiresTimestamp($expires);

        return $cookie;
    }

    /**
     * {@inheritDoc}
     */
    public function withExpired()
    {
        $cookie = clone $this;

        $cookie->expires = 0;

        return $cookie;
    }

    /**
     * @deprecated See https://github.com/codeigniter4/CodeIgniter4/pull/6413
     */
    public function withNeverExpiring()
    {
        $cookie = clone $this;

        $cookie->expires = Time::now()->getTimestamp() + 5 * YEAR;

        return $cookie;
    }

    /**
     * {@inheritDoc}
     */
    public function withPath(?string $path)
    {
        $path = $path ?: self::$defaults['path'];
        $this->validatePrefix($this->prefix, $this->secure, $path, $this->domain);

        $cookie = clone $this;

        $cookie->path = $path;

        return $cookie;
    }

    /**
     * {@inheritDoc}
     */
    public function withDomain(?string $domain)
    {
        $domain ??= self::$defaults['domain'];
        $this->validatePrefix($this->prefix, $this->secure, $this->path, $domain);

        $cookie = clone $this;

        $cookie->domain = $domain;

        return $cookie;
    }

    /**
     * {@inheritDoc}
     */
    public function withSecure(bool $secure = true)
    {
        $this->validatePrefix($this->prefix, $secure, $this->path, $this->domain);
        $this->validateSameSite($this->samesite, $secure);

        $cookie = clone $this;

        $cookie->secure = $secure;

        return $cookie;
    }

    /**
     * {@inheritDoc}
     */
    public function withHTTPOnly(bool $httponly = true)
    {
        $cookie = clone $this;

        $cookie->httponly = $httponly;

        return $cookie;
    }

    /**
     * {@inheritDoc}
     */
    public function withSameSite(string $samesite)
    {
        $this->validateSameSite($samesite, $this->secure);

        $cookie = clone $this;

        $cookie->samesite = ucfirst(strtolower($samesite));

        return $cookie;
    }

    /**
     * {@inheritDoc}
     */
    public function withRaw(bool $raw = true)
    {
        $this->validateName($this->name, $raw);

        $cookie = clone $this;

        $cookie->raw = $raw;

        return $cookie;
    }

    // =========================================================================
    // ARRAY ACCESS FOR BC
    // =========================================================================

    /**
     * Whether an offset exists.
     *
     * @param string $offset
     */
    public function offsetExists($offset): bool
    {
        return $offset === 'expire' ? true : property_exists($this, $offset);
    }

    /**
     * Offset to retrieve.
     *
     * @param string $offset
     *
     * @return bool|int|string
     *
     * @throws InvalidArgumentException
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if (! $this->offsetExists($offset)) {
            throw new InvalidArgumentException(sprintf('Undefined offset "%s".', $offset));
        }

        return $offset === 'expire' ? $this->expires : $this->{$offset};
    }

    /**
     * Offset to set.
     *
     * @param string          $offset
     * @param bool|int|string $value
     *
     * @throws LogicException
     */
    public function offsetSet($offset, $value): void
    {
        throw new LogicException(sprintf('Cannot set values of properties of %s as it is immutable.', static::class));
    }

    /**
     * Offset to unset.
     *
     * @param string $offset
     *
     * @throws LogicException
     */
    public function offsetUnset($offset): void
    {
        throw new LogicException(sprintf('Cannot unset values of properties of %s as it is immutable.', static::class));
    }

    // =========================================================================
    // CONVERTERS
    // =========================================================================

    /**
     * {@inheritDoc}
     */
    public function toHeaderString(): string
    {
        return $this->__toString();
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        $cookieHeader = [];

        if ($this->getValue() === '') {
            $cookieHeader[] = $this->getPrefixedName() . '=deleted';
            $cookieHeader[] = 'Expires=' . gmdate(self::EXPIRES_FORMAT, 0);
            $cookieHeader[] = 'Max-Age=0';
        } else {
            $value = $this->isRaw() ? $this->getValue() : rawurlencode($this->getValue());

            $cookieHeader[] = sprintf('%s=%s', $this->getPrefixedName(), $value);

            if ($this->getExpiresTimestamp() !== 0) {
                $cookieHeader[] = 'Expires=' . $this->getExpiresString();
                $cookieHeader[] = 'Max-Age=' . $this->getMaxAge();
            }
        }

        if ($this->getPath() !== '') {
            $cookieHeader[] = 'Path=' . $this->getPath();
        }

        if ($this->getDomain() !== '') {
            $cookieHeader[] = 'Domain=' . $this->getDomain();
        }

        if ($this->isSecure()) {
            $cookieHeader[] = 'Secure';
        }

        if ($this->isHTTPOnly()) {
            $cookieHeader[] = 'HttpOnly';
        }

        $samesite = $this->getSameSite();

        if ($samesite === '') {
            // modern browsers warn in console logs that an empty SameSite attribute
            // will be given the `Lax` value
            $samesite = self::SAMESITE_LAX;
        }

        $cookieHeader[] = 'SameSite=' . ucfirst(strtolower($samesite));

        return implode('; ', $cookieHeader);
    }

    /**
     * {@inheritDoc}
     */
    public function toArray(): array
    {
        return [
            'name'   => $this->name,
            'value'  => $this->value,
            'prefix' => $this->prefix,
            'raw'    => $this->raw,
        ] + $this->getOptions();
    }

    /**
     * Converts expires time to Unix format.
     *
     * @param DateTimeInterface|int|string $expires
     */
    protected static function convertExpiresTimestamp($expires = 0): int
    {
        if ($expires instanceof DateTimeInterface) {
            $expires = $expires->format('U');
        }

        if (! is_string($expires) && ! is_int($expires)) {
            throw CookieException::forInvalidExpiresTime(gettype($expires));
        }

        if (! is_numeric($expires)) {
            $expires = strtotime($expires);

            if ($expires === false) {
                throw CookieException::forInvalidExpiresValue();
            }
        }

        return $expires > 0 ? (int) $expires : 0;
    }

    // =========================================================================
    // VALIDATION
    // =========================================================================

    /**
     * Validates the cookie name per RFC 2616.
     *
     * If `$raw` is true, names should not contain invalid characters
     * as `setrawcookie()` will reject this.
     *
     * @throws CookieException
     */
    protected function validateName(string $name, bool $raw): void
    {
        if ($raw && strpbrk($name, self::$reservedCharsList) !== false) {
            throw CookieException::forInvalidCookieName($name);
        }

        if ($name === '') {
            throw CookieException::forEmptyCookieName();
        }
    }

    /**
     * Validates the special prefixes if some attribute requirements are met.
     *
     * @throws CookieException
     */
    protected function validatePrefix(string $prefix, bool $secure, string $path, string $domain): void
    {
        if (strpos($prefix, '__Secure-') === 0 && ! $secure) {
            throw CookieException::forInvalidSecurePrefix();
        }

        if (strpos($prefix, '__Host-') === 0 && (! $secure || $domain !== '' || $path !== '/')) {
            throw CookieException::forInvalidHostPrefix();
        }
    }

    /**
     * Validates the `SameSite` to be within the allowed types.
     *
     * @throws CookieException
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie/SameSite
     */
    protected function validateSameSite(string $samesite, bool $secure): void
    {
        if ($samesite === '') {
            $samesite = self::$defaults['samesite'];
        }

        if ($samesite === '') {
            $samesite = self::SAMESITE_LAX;
        }

        if (! in_array(strtolower($samesite), self::ALLOWED_SAMESITE_VALUES, true)) {
            throw CookieException::forInvalidSameSite($samesite);
        }

        if (strtolower($samesite) === self::SAMESITE_NONE && ! $secure) {
            throw CookieException::forInvalidSameSiteNone();
        }
    }
}
