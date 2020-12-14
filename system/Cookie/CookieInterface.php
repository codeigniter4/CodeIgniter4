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
	public function getSameSite(): string;
}
