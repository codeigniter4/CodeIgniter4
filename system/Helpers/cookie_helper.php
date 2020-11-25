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
