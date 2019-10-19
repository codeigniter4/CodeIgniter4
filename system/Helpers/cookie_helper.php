<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019 CodeIgniter Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT    MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

// --------------------------------------------------------------------

/**
 * CodeIgniter Cookie Helpers
 *
 * @package CodeIgniter
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
	 *
	 * @see (\Config\Services::response())->setCookie()
	 * @see \CodeIgniter\HTTP\Response::setCookie()
	 */
	function set_cookie($name, string $value = '', string $expire = '', string $domain = '', string $path = '/', string $prefix = '', bool $secure = false, bool $httpOnly = false)
	{
		// The following line shows as a syntax error in NetBeans IDE
		//(\Config\Services::response())->setcookie
		$response = \Config\Services::response();
		$response->setcookie($name, $value, $expire, $domain, $path, $prefix, $secure, $httpOnly);
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
	 * @see    (\Config\Services::request())->getCookie()
	 * @see    \CodeIgniter\HTTP\IncomingRequest::getCookie()
	 * @return mixed
	 */
	function get_cookie($index, bool $xssClean = false)
	{
		$app             = config(\Config\App::class);
		$appCookiePrefix = $app->cookiePrefix;
		$prefix          = isset($_COOKIE[$index]) ? '' : $appCookiePrefix;

		$request = \Config\Services::request();
		$filter  = true === $xssClean ? FILTER_SANITIZE_STRING : null;
		$cookie  = $request->getCookie($prefix . $index, $filter);

		return $cookie;
	}
}

//--------------------------------------------------------------------

if (! function_exists('delete_cookie'))
{
	/**
	 * Delete a COOKIE
	 *
	 * @param  mixed  $name
	 * @param  string $domain the cookie domain. Usually: .yourdomain.com
	 * @param  string $path   the cookie path
	 * @param  string $prefix the cookie prefix
	 * @see    (\Config\Services::response())->setCookie()
	 * @see    \CodeIgniter\HTTP\Response::setcookie()
	 * @return void
	 */
	function delete_cookie($name, string $domain = '', string $path = '/', string $prefix = '')
	{
		\Config\Services::response()->deleteCookie($name, $domain, $path, $prefix);
	}
}
