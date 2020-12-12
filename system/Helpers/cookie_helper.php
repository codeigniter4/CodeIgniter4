<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Config\App;
use Config\Services;

// --------------------------------------------------------------------

/**
 * CodeIgniter Cookie Helpers
 */
if (! function_exists('set_cookie'))
{
	/**
	 * Set cookie
	 *
	 * Accepts seven parameters, or you can submit an associative
	 * array in the first parameter containing all the values.
	 *
	 * @param string|array $name     Cookie name or array containing binds
	 * @param string       $value    The value of the cookie
	 * @param string       $expire   The number of seconds until expiration
	 * @param string       $domain   For site-wide cookie.
	 *                                 Usually: .yourdomain.com
	 * @param string       $path     The cookie path
	 * @param string       $prefix   The cookie prefix
	 * @param boolean      $secure   True makes the cookie secure
	 * @param boolean      $httpOnly True makes the cookie accessible via
	 *                                 http(s) only (no javascript)
	 * @param string|null  $sameSite The cookie SameSite value
	 *
	 * @see (\Config\Services::response())->setCookie()
	 * @see \CodeIgniter\HTTP\Response::setCookie()
	 */
	function set_cookie(
		$name,
		string $value = '',
		string $expire = '',
		string $domain = '',
		string $path = '/',
		string $prefix = '',
		bool $secure = false,
		bool $httpOnly = false,
		string $sameSite = null
	)
	{
		// The following line shows as a syntax error in NetBeans IDE
		//(\Config\Services::response())->setcookie
		$response = Services::response();
		$response->setcookie($name, $value, $expire, $domain, $path, $prefix, $secure, $httpOnly, $sameSite);
	}
}

//--------------------------------------------------------------------

if (! function_exists('get_cookie'))
{
	/**
	 * Fetch an item from the COOKIE array
	 *
	 * @param string  $index
	 * @param boolean $xssClean
	 *
	 * @return mixed
	 *
	 * @see (\Config\Services::request())->getCookie()
	 * @see \CodeIgniter\HTTP\IncomingRequest::getCookie()
	 */
	function get_cookie($index, bool $xssClean = false)
	{
		$app             = config(App::class);
		$appCookiePrefix = $app->cookiePrefix;
		$prefix          = isset($_COOKIE[$index]) ? '' : $appCookiePrefix;

		$request = Services::request();
		$filter  = true === $xssClean ? FILTER_SANITIZE_STRING : null;

		return $request->getCookie($prefix . $index, $filter);
	}
}

//--------------------------------------------------------------------

if (! function_exists('delete_cookie'))
{
	/**
	 * Delete a COOKIE
	 *
	 * @param mixed  $name
	 * @param string $domain the cookie domain. Usually: .yourdomain.com
	 * @param string $path   the cookie path
	 * @param string $prefix the cookie prefix
	 *
	 * @return void
	 *
	 * @see (\Config\Services::response())->deleteCookie()
	 * @see \CodeIgniter\HTTP\Response::deleteCookie()
	 */
	function delete_cookie($name, string $domain = '', string $path = '/', string $prefix = '')
	{
		Services::response()->deleteCookie($name, $domain, $path, $prefix);
	}
}

//--------------------------------------------------------------------

if (! function_exists('set_cookie'))
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
	 * 
	 * @return void
	 *
	 * @see CodeIgniter\Cookie\Cookie::set()
	 */
	function set_cookie(
		$name, string $value = '',
		int $expires = null,
		string $path = '/',
		string $domain = '',
		string $prefix = '',
		bool $secure = false,
		bool $httponly = false,
		string $samesite = null
	): void
	{
		Services::cookie()->set($name, $value, $expires, $path, $domain, $prefix, $secure, $httponly, $samesite);
	}
}

if (! function_exists('get_cookie'))
{
	/**
	 * Get the cookie.
	 *
	 * Return a specific cookie for the given name, if no name was given
	 * it will return all cookies.
	 * 
	 * @param string $name	 The cookie name
	 * @param string $prefix The cookie prefix
	 * 
	 * @return array|null
	 * 
	 * @see CodeIgniter\Cookie\Cookie::get()
	 */
	function get_cookie(string $name = '', string $prefix = ''): ?array
	{
		return Services::cookie()->get($name, $prefix);
	}
}

if (! function_exists('remove_cookie'))
{
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
	 * @return void
	 * 
	 * @see CodeIgniter\Cookie\Cookie::remove()
	 */
	function remove_cookie(string $name, string $path = '/', string $domain = '', string $prefix = ''): void
	{
		Services::cookie()->remove($name, $path, $domain, $prefix);
	}
}

if (! function_exists('has_cookie'))
{
	/**
	 * Remove a cookie.
	 *
	 * Check whether cookie exists or not.
	 *
	 * @param string $name	 The cookie name
	 * @param string $value	 The cookie value
	 * @param string $prefix The cookie prefix
	 *
	 * @return boolean
	 * 
	 * @see CodeIgniter\Cookie\Cookie::has()
	 */
	function has_cookie(string $name, string $value = '', string $prefix = ''): bool
	{
		return Services::cookie()->has($name, $value, $prefix);
	}
}

if (! function_exists('fetch_cookie'))
{
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
	 * 
	 * @see CodeIgniter\Cookie\Cookie::fetch()
	 */
	function fetch_cookie($index = null, int $filter = null, int $flags = null)
	{
		return Services::cookie()->fetch($index, $filter, $flags);
	}
}
