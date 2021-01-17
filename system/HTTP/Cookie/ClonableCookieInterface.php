<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP\Cookie;

use DateTimeInterface;

/**
 * Interface for a fresh Cookie instance with selected attribute(s)
 * only changed from the original instance.
 */
interface ClonableCookieInterface extends CookieInterface
{
	/**
	 * Creates a new Cookie with URL encoding option updated.
	 *
	 * @param boolean $raw
	 *
	 * @return $this
	 */
	public function withRaw(bool $raw = true);

	/**
	 * Creates a new Cookie with a new cookie prefix.
	 *
	 * @param string $prefix
	 *
	 * @return $this
	 */
	public function withPrefix(string $prefix = '');

	/**
	 * Creates a new Cookie with a new name.
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function withName(string $name);

	/**
	 * Creates a new Cookie with new value.
	 *
	 * @param string $value
	 *
	 * @return $this
	 */
	public function withValue(string $value);

	/**
	 * Creates a new Cookie with a new cookie expire time.
	 *
	 * @param DateTimeInterface|integer|string $expires
	 *
	 * @return $this
	 */
	public function withExpiresAt($expires = 0);

	/**
	 * Creates a new Cookie that will expire the cookie from the browser.
	 *
	 * @return $this
	 */
	public function withExpired();

	/**
	 * Creates a new Cookie that will virtually never expire from the browser.
	 *
	 * @return $this
	 */
	public function withNeverExpiring();

	/**
	 * Creates a new Cookie with a new domain the cookie is available.
	 *
	 * @param string|null $domain
	 *
	 * @return $this
	 */
	public function withDomain(?string $domain);

	/**
	 * Creates a new Cookie with a new path on the server the cookie is available.
	 *
	 * @param string|null $path
	 *
	 * @return $this
	 */
	public function withPath(?string $path);

	/**
	 * Creates a new Cookie with a new "Secure" attribute.
	 *
	 * @param boolean $secure
	 *
	 * @return $this
	 */
	public function withSecure(bool $secure = true);

	/**
	 * Creates a new Cookie with a new "HttpOnly" attribute
	 *
	 * @param boolean $httpOnly
	 *
	 * @return $this
	 */
	public function withHttpOnly(bool $httpOnly = true);

	/**
	 * Creates a new Cookie with a new "SameSite" attribute.
	 *
	 * @param string $sameSite
	 *
	 * @return $this
	 */
	public function withSameSite(string $sameSite);
}
