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

use DateTimeInterface;

/**
 * Interface for a fresh Cookie instance with selected attribute(s)
 * only changed from the original instance.
 */
interface CloneableCookieInterface extends CookieInterface
{
    /**
     * Creates a new Cookie with a new cookie prefix.
     *
     * @return static
     */
    public function withPrefix(string $prefix = '');

    /**
     * Creates a new Cookie with a new name.
     *
     * @return static
     */
    public function withName(string $name);

    /**
     * Creates a new Cookie with new value.
     *
     * @return static
     */
    public function withValue(string $value);

    /**
     * Creates a new Cookie with a new cookie expires time.
     *
     * @param DateTimeInterface|int|string $expires
     *
     * @return static
     */
    public function withExpires($expires);

    /**
     * Creates a new Cookie that will expire the cookie from the browser.
     *
     * @return static
     */
    public function withExpired();

    /**
     * Creates a new Cookie with a new path on the server the cookie is available.
     *
     * @return static
     */
    public function withPath(?string $path);

    /**
     * Creates a new Cookie with a new domain the cookie is available.
     *
     * @return static
     */
    public function withDomain(?string $domain);

    /**
     * Creates a new Cookie with a new "Secure" attribute.
     *
     * @return static
     */
    public function withSecure(bool $secure = true);

    /**
     * Creates a new Cookie with a new "HttpOnly" attribute
     *
     * @return static
     */
    public function withHTTPOnly(bool $httponly = true);

    /**
     * Creates a new Cookie with a new "SameSite" attribute.
     *
     * @return static
     */
    public function withSameSite(string $samesite);

    /**
     * Creates a new Cookie with URL encoding option updated.
     *
     * @return static
     */
    public function withRaw(bool $raw = true);
}
