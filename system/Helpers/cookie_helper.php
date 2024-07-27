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

use CodeIgniter\Cookie\Cookie;
use Config\Cookie as CookieConfig;

// =============================================================================
// CodeIgniter Cookie Helpers
// =============================================================================

if (! function_exists('set_cookie')) {
    /**
     * Set cookie
     *
     * Accepts seven parameters, or you can submit an associative
     * array in the first parameter containing all the values.
     *
     * @param array|Cookie|string $name     Cookie name / array containing binds / Cookie object
     * @param string              $value    The value of the cookie
     * @param int                 $expire   The number of seconds until expiration
     * @param string              $domain   For site-wide cookie. Usually: .yourdomain.com
     * @param string              $path     The cookie path
     * @param string              $prefix   The cookie prefix ('': the default prefix)
     * @param bool|null           $secure   True makes the cookie secure
     * @param bool|null           $httpOnly True makes the cookie accessible via http(s) only (no javascript)
     * @param string|null         $sameSite The cookie SameSite value
     *
     * @see \CodeIgniter\HTTP\Response::setCookie()
     */
    function set_cookie(
        $name,
        string $value = '',
        int $expire = 0,
        string $domain = '',
        string $path = '/',
        string $prefix = '',
        ?bool $secure = null,
        ?bool $httpOnly = null,
        ?string $sameSite = null
    ): void {
        $response = service('response');
        $response->setCookie($name, $value, $expire, $domain, $path, $prefix, $secure, $httpOnly, $sameSite);
    }
}

if (! function_exists('get_cookie')) {
    /**
     * Fetch an item from the $_COOKIE array
     *
     * @param string      $index
     * @param string|null $prefix Cookie name prefix.
     *                            '': the prefix in Config\Cookie
     *                            null: no prefix
     *
     * @return array|string|null
     *
     * @see \CodeIgniter\HTTP\IncomingRequest::getCookie()
     */
    function get_cookie($index, bool $xssClean = false, ?string $prefix = '')
    {
        if ($prefix === '') {
            $cookie = config(CookieConfig::class);

            $prefix = $cookie->prefix;
        }

        $request = service('request');
        $filter  = $xssClean ? FILTER_SANITIZE_FULL_SPECIAL_CHARS : FILTER_DEFAULT;

        return $request->getCookie($prefix . $index, $filter);
    }
}

if (! function_exists('delete_cookie')) {
    /**
     * Delete a cookie
     *
     * @param string $name
     * @param string $domain the cookie domain. Usually: .yourdomain.com
     * @param string $path   the cookie path
     * @param string $prefix the cookie prefix
     *
     * @see \CodeIgniter\HTTP\Response::deleteCookie()
     */
    function delete_cookie($name, string $domain = '', string $path = '/', string $prefix = ''): void
    {
        service('response')->deleteCookie($name, $domain, $path, $prefix);
    }
}

if (! function_exists('has_cookie')) {
    /**
     * Checks if a cookie exists by name.
     */
    function has_cookie(string $name, ?string $value = null, string $prefix = ''): bool
    {
        return service('response')->hasCookie($name, $value, $prefix);
    }
}
