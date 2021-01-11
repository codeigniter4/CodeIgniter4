<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Cookie;

interface CookieInterface
{
	/**
	 * Get cookie name.
	 *
	 * @return string
	 */
	public function getName(): string;

	/**
	 * Get cookie value.
	 *
	 * @return string
	 */
	public function getValue(): string;

	/**
	 * Get cookie expires.
	 *
	 * @return integer
	 */
    public function getExpires(): int;
    
	/**
	 * Get cookie path.
	 *
	 * @return string
	 */
    public function getPath(): string;
    
	/**
	 * Get cookie domain.
	 *
	 * @return string
	 */
	public function getDomain(): string;

	/**
	 * Check cookie secure status.
	 *
	 * @return boolean
	 */
	public function isSecure(): bool;

	/**
	 * Check cookie httponly status.
	 *
	 * @return boolean
	 */
	public function isHTTPOnly(): bool;

	/**
	 * Get cookie samesite.
	 *
	 * @return string
	 */
	public function getSamesite(): string;

	/**
	 * Creates a new Cookie with a new "name".
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function withName(string $name): CookieInterface;

	/**
	 * Creates a new Cookie with a new "value".
	 *
	 * @param string $value
	 *
	 * @return $this
	 */
	public function withValue(string $value): CookieInterface;

	/**
	 * Creates a new Cookie with a new "expires".
	 *
	 * @param integer $expires
	 *
	 * @return $this
	 */
	public function withExpires(int $expires = 0): CookieInterface;

	/**
	 * Creates a new Cookie with a new "path".
	 *
	 * @param string|null $path
	 *
	 * @return $this
	 */
	public function withPath(?string $path): CookieInterface;

	/**
	 * Creates a new Cookie with a new "domain".
	 *
	 * @param string|null $domain
	 *
	 * @return $this
	 */
	public function withDomain(?string $domain): CookieInterface;

	/**
	 * Creates a new Cookie with a new "secure".
	 *
	 * @param boolean $secure
	 *
	 * @return $this
	 */
	public function withSecure(bool $secure = true): CookieInterface;

	/**
	 * Creates a new Cookie with a new "httponly".
	 *
	 * @param boolean $httponly
	 *
	 * @return $this
	 */
	public function withHTTPOnly(bool $httponly = true): CookieInterface;

	/**
	 * Creates a new Cookie with a new "samesite".
	 *
	 * @param string $samesite
	 *
	 * @return $this
	 */
	public function withSamesite(string $samesite): CookieInterface;
}
