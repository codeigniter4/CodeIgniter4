<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Cookie\Collection;

use Config\Services;

class Cookie extends BaseCookie implements CookieInterface
{
	/**
	 * Stores all cookies.
	 *
	 * @var array
	 */
	protected $cookies = [];

	//--------------------------------------------------------------------

	/**
	 * Set a cookie.
	 *
	 * Accepts an arbitrary number of binds or an associative array in the
	 * first parameter containing all the values.
	 *
	 * @param string|array $name     The cookie name or array containing binds
	 * @param string       $value    The cookie value
	 * @param integer      $expires  The cookie expiration time (in seconds)
	 * @param string       $path     The cookie path (default: '/')
	 * @param string       $domain   The cookie domain (e.g.: '.example-domain.com')
	 * @param string       $prefix   The cookie name prefix (e.g.: 'mk_')
	 * @param boolean      $secure   Whether to transfer the cookie over a SSL only
	 * @param boolean      $httponly Whether to access the cookie through HTTP only
	 * @param string       $samesite The cookie samesite
	 *
	 * @return CookieInterface
	 */
	public function set(
		$name,
		string $value = '',
		int $expires = 0,
		string $path = '/',
		string $domain = '',
		string $prefix = '',
		bool $secure = false,
		bool $httponly = false,
		string $samesite = ''
	): CookieInterface
	{
		if (is_array($name))
		{
			// always leave 'name' in last place, as the loop will break otherwise, due to $$item
			$params = [
				'value', 'expires', 'path', 'domain', 'secure', 'httponly', 'samesite', 'prefix', 'name'
			];

			foreach ($params as $item)
			{
				if (isset($name[$item]))
				{
					$$item = $name[$item];
				}
			}
		}

		$this->cookies[] = [
			'name'     => $this->prefixName($name, $prefix),
			'value'    => $value,
			'expires'  => $expires > 0 ? time() + $expires : time() - YEAR,
			'path'     => $path === '/' && $this->path !== '/' ? $this->path : $path,
			'domain'   => empty($domain) && ! empty($this->domain) ? $this->domain : $domain,
			'secure'   => ! $secure && $this->secure ? $this->secure : $secure,
			'httponly' => ! $httponly && $this->httponly ? $this->httponly : $httponly,
			'samesite' => ucfirst(strtolower(empty($samesite) && ! empty($this->samesite) ? $this->samesite : $samesite)),
		];

		return $this;
	}

	/**
	 * Get a cookie.
	 *
	 * Return a specific cookie for the given name, if no name was given
	 * it will return all cookies.
	 * 
	 * @param string $name	 The cookie name
	 * @param string $prefix The cookie prefix
	 *
	 * @return array|null
	 */
	public function get(string $name = '', string $prefix = ''): ?array
	{
		if (empty($name))
		{
			return $this->cookies;
		}

		foreach ($this->cookies as $cookie)
		{
			if ($cookie['name'] === $this->prefixName($name, $prefix))
			{
				return $cookie;
			}
		}

		return null;
	}

	/**
	 * Remove a cookie.
	 *
	 * Delete a specific cookie for the given name.
	 *
	 * @param string $name	 The cookie name
	 * @param string $prefix The cookie prefix
	 *
	 * @return CookieInterface
	 */
	public function remove(string $name, string $prefix = ''): CookieInterface
	{
		$hasFlag = false;

		foreach ($this->cookies as $index => $cookie)
		{
			if ($cookie['name'] === $this->prefixName($name, $prefix))
			{
				unset($this->cookies[$index]);

				$hasFlag = true;
			}
		}

		if (! $hasFlag)
		{
			$this->set($this->prefixName($name, $prefix));
		}

		return $this;
	}

	/**
	 * Put a cookie.
	 *
	 * Merges a new cookie with the current collection, and returns instance 
	 * of the new collection without changing the original collection.
	 *
	 * @param array $cookie
	 *
	 * @return CookieInterface
	 */
	public function put(array $cookie): CookieInterface
	{
		$collection = clone $this;

		$collection->cookies[] = $cookie;

		return $collection;
	}

	/**
	 * Push a cookie.
	 *
	 * Merges a new cookie with the current collection, and returns instance
	 * of the current collection with the new merged values.
	 *
	 * @param array $cookie
	 *
	 * @return CookieInterface
	 */
	public function push(array $cookie): CookieInterface
	{
		$this->cookies[] = $cookie;

		return $this;
	}

	/**
	 * Has a cookie.
	 * 
	 * Check whether cookie exists or not.
	 *
	 * @param string $name	 The cookie name
	 * @param string $prefix The cookie prefix
	 *
	 * @return boolean
	 */
	public function has(string $name, string $prefix = ''): bool
	{
		foreach ($this->cookies as $cookie)
		{
			if ($cookie['name'] === $this->prefixName($name, $prefix))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Send the cookies.
	 * 
	 * Send the cookies to the remote browser.
	 * 
	 * @return void
	 */
	public function send(): void
	{
		foreach ($this->cookies as $params)
		{
			if (PHP_VERSION_ID < 70300)
			{
				if (isset($params['samesite']))
				{
					$params['path'] .= '; samesite=' . $params['samesite'];
					unset($params['samesite']);
				}

				setcookie(...array_values($params));
			}
			else
			{
				$name  = $params['name'];
				$value = $params['value'];
				unset($params['name'], $params['value']);

				setcookie($name, $value, $params);
			}
		}
		
		$this->clear();
	}

	/**
	 * Clear stored cookies.
	 *
	 * @return void
	 */
	public function clear(): void
	{
		$this->cookies = [];
	}

	/**
	 * Fetch a cookie.
	 *
	 * Return an item from the $_COOKIE array.
	 * 
	 * @param string|null $name     The cookie name
	 * @param string      $prefix 	The cookie prefix
	 * @param boolean     $xssClean Whether to apply filter
	 *
	 * @return string|array|null
	 */
	public function fetch(string $name = null, string $prefix = '', bool $xssClean = false)
	{
		$name 	= empty($prefix) ? $name : $prefix . $name;
		$filter = $xssClean ? FILTER_SANITIZE_STRING : null;

		return Services::request()->fetchGlobal('cookie', $name, $filter);
	}

	/**
	 * Prefix cookie name.
	 *
	 * Prepends a prefix to the cookie name if exists.
	 *
	 * @param string $name	 The cookie name
	 * @param string $prefix The cookie prefix
	 *
	 * @return string
	 */
	private function prefixName(string $name, string $prefix = ''): string
	{
		if (empty($prefix) && ! empty($this->prefix))
		{
			$prefix = $this->prefix;
		}

		return $prefix . $name;
	}
}
