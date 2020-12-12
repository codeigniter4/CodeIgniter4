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
	 * Set a cookie.
	 *
	 * Accepts an arbitrary number of binds or an associative array in the
	 * first parameter containing all the values.
	 *
	 * @param string|array $name     The cookie name or array containing binds
	 * @param string       $value    The cookie value
	 * @param integer|null $expires  The cookie expiration time (in seconds)
	 * @param string       $path     The cookie path (default: '/')
	 * @param string       $domain   The cookie domain (e.g.: '.example-domain.com')
	 * @param string       $prefix   The cookie name prefix
	 * @param boolean      $secure   Whether to transfer the cookie over a SSL only
	 * @param boolean      $httponly Whether to access the cookie through HTTP only
	 * @param string|null  $samesite The cookie samesite
	 * @return CookieInterface
	 */
	public function set(
		$name, string $value = '',
		int $expires = null,
		string $path = '/',
		string $domain = '',
		string $prefix = '',
		bool $secure = false,
		bool $httponly = false,
		string $samesite = null
	): CookieInterface;

	//--------------------------------------------------------------------

	/**
	 * Get a cookie
	 *
	 * Return a specific cookie for the given name, if no name was given
	 * it will return all cookies.
	 * 
	 * @param string $name	 The cookie name
	 * @param string $prefix The cookie prefix
	 *
	 * @return array|null
	 */
	public function get(string $name = '', string $prefix = ''): ?array;

	//--------------------------------------------------------------------

	/**
	 * Remove a cookie.
	 *
	 * Delete a specific cookie for the given name.
	 * 
	 * @param string $name	 The cookie name
	 * @param string $path	 The cookie path
	 * @param string $domain The cookie domain
	 * @param string $prefix The cookie prefix
	 *
	 * @return CookieInterface
	 */
	public function remove(string $name, string $path = '/', string $domain = '', string $prefix = ''): CookieInterface;

	//--------------------------------------------------------------------

	/**
	 * Has a cookie.
	 * 
	 * Checks whether cookie exists or not.
	 *
	 * @param string $name	 The cookie name
	 * @param string $value  The cookie value
	 * @param string $prefix The cookie prefix
	 *
	 * @return boolean
	 */
	public function has(string $name, string $value = '', string $prefix = ''): bool;

	//--------------------------------------------------------------------

	/**
	 * Send the cookies.
	 * 
	 * Send the cookies to the remote browser.
	 * 
	 * @return void
	 */
	public function send(): void;

	//--------------------------------------------------------------------

	/**
	 * Fetch a cookie.
	 *
	 * Return an item from the COOKIE array.
	 * 
	 * @param string|array|null $index  Index for item to be fetched
	 * @param integer|null      $filter The filter to be applied
	 * @param integer|null      $flags	The flags to be applied
	 *
	 * @return string|array|null
	 */
	public function fetch($index = null, int $filter = null, int $flags = null);

	//--------------------------------------------------------------------

	/**
	 * Clear stored cookies.
	 *
	 * @return void
	 */
	public function clear(): void;

	//--------------------------------------------------------------------

	/**
	 * Reset configuration to default.
	 *
	 * @return CookieInterface
	 */
	public function reset(): CookieInterface;
}
