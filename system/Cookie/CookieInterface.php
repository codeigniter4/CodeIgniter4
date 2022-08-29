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

namespace CodeIgniter\Cookie;

/**
 * Interface for a value object representation of an HTTP cookie.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie
 */
interface CookieInterface
{
    /**
     * Cookies will be sent in all contexts, i.e in responses to both
     * first-party and cross-origin requests. If `SameSite=None` is set,
     * the cookie `Secure` attribute must also be set (or the cookie will be blocked).
     */
    public const SAMESITE_NONE = 'none';

    /**
     * Cookies are not sent on normal cross-site subrequests (for example to
     * load images or frames into a third party site), but are sent when a
     * user is navigating to the origin site (i.e. when following a link).
     */
    public const SAMESITE_LAX = 'lax';

    /**
     * Cookies will only be sent in a first-party context and not be sent
     * along with requests initiated by third party websites.
     */
    public const SAMESITE_STRICT = 'strict';

    /**
     * RFC 6265 allowed values for the "SameSite" attribute.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie/SameSite
     */
    public const ALLOWED_SAMESITE_VALUES = [
        self::SAMESITE_NONE,
        self::SAMESITE_LAX,
        self::SAMESITE_STRICT,
    ];

    /**
     * Expires date format.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Date
     * @see https://tools.ietf.org/html/rfc7231#section-7.1.1.2
     */
    public const EXPIRES_FORMAT = 'D, d-M-Y H:i:s T';

    /**
     * Returns a unique identifier for the cookie consisting
     * of its prefixed name, path, and domain.
     */
    public function getId(): string;

    /**
     * Gets the cookie prefix.
     */
    public function getPrefix(): string;

    /**
     * Gets the cookie name.
     */
    public function getName(): string;

    /**
     * Gets the cookie name prepended with the prefix, if any.
     */
    public function getPrefixedName(): string;

    /**
     * Gets the cookie value.
     */
    public function getValue(): string;

    /**
     * Gets the time in Unix timestamp the cookie expires.
     */
    public function getExpiresTimestamp(): int;

    /**
     * Gets the formatted expires time.
     */
    public function getExpiresString(): string;

    /**
     * Checks if the cookie is expired.
     */
    public function isExpired(): bool;

    /**
     * Gets the "Max-Age" cookie attribute.
     */
    public function getMaxAge(): int;

    /**
     * Gets the "Path" cookie attribute.
     */
    public function getPath(): string;

    /**
     * Gets the "Domain" cookie attribute.
     */
    public function getDomain(): string;

    /**
     * Gets the "Secure" cookie attribute.
     *
     * Checks if the cookie is only sent to the server when a request is made
     * with the `https:` scheme (except on `localhost`), and therefore is more
     * resistent to man-in-the-middle attacks.
     */
    public function isSecure(): bool;

    /**
     * Gets the "HttpOnly" cookie attribute.
     *
     * Checks if JavaScript is forbidden from accessing the cookie.
     */
    public function isHTTPOnly(): bool;

    /**
     * Gets the "SameSite" cookie attribute.
     */
    public function getSameSite(): string;

    /**
     * Checks if the cookie should be sent with no URL encoding.
     */
    public function isRaw(): bool;

    /**
     * Gets the options that are passable to the `setcookie` variant
     * available on PHP 7.3+
     *
     * @return array<string, bool|int|string>
     */
    public function getOptions(): array;

    /**
     * Returns the Cookie as a header value.
     */
    public function toHeaderString(): string;

    /**
     * Returns the string representation of the Cookie object.
     *
     * @return string
     */
    public function __toString();

    /**
     * Returns the array representation of the Cookie object.
     *
     * @return array<string, bool|int|string>
     */
    public function toArray(): array;
}
