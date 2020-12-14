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

use Config\Cookie as CookieConfig;
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
	 * Constructor.
	 *
	 * @param CookieConfig $config
	 */
	public function __construct(CookieConfig $config)
	{
		parent::__construct($config);
	}

	//--------------------------------------------------------------------

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

		$expires  = is_null($expires) ? time() - DAY : ($expires > 0 ? time() + $expires : 0);
		$path     = $path === '/' && $this->path !== '/' ? $this->path : $path;
		$domain   = empty($domain) && ! empty($this->domain) ? $this->domain : $domain;
		$secure   = ! $secure && $this->secure ? $this->secure : $secure;
		$httponly = ! $httponly && $this->httponly ? $this->httponly : $httponly;
		$samesite = is_null($samesite) ? $this->samesite : '';
		$prefix   = empty($prefix) && ! empty($this->prefix) ? $this->prefix : $prefix;

		$cookie = [
			'name'     => $prefix . $name,
			'value'    => $value,
			'expires'  => $expires,
			'path'     => $path,
			'domain'   => $domain,
			'secure'   => $secure,
			'httponly' => $httponly,
		];

		if (! empty($samesite))
		{
			$cookie['samesite'] = $samesite;
		}

		$this->cookies[] = $cookie;

		return $this;
	}

	//--------------------------------------------------------------------

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

		$prefix = empty($prefix) && ! empty($this->prefix) ? $this->prefix : $prefix;

		foreach ($this->cookies as $cookie)
		{
			if ($cookie['name'] === $prefix . $name)
			{
				return $cookie;
			}
		}

		return null;
	}

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
	public function remove(string $name, string $path = '/', string $domain = '', string $prefix = ''): CookieInterface
	{
		$prefix = empty($prefix) && ! empty($this->prefix) ? $this->prefix : $prefix;

		$hasFlag = false;

		foreach ($this->cookies as &$cookie)
		{
			if ($cookie['name'] === $prefix . $name)
			{
				$cookie['value']   = '';
				$cookie['expires'] = null;

				if (! empty($path) && $cookie['path'] !== $path)
				{
					continue;
				}

				if (! empty($domain) && $cookie['domain'] !== $domain)
				{
					continue;
				}

				$hasFlag = true;

				break;
			}
		}

		if (! $hasFlag)
		{
			$this->set($name, '', null, $path, $domain, $prefix);
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Has a cookie.
	 * 
	 * Check whether cookie exists or not.
	 *
	 * @param string $name	 The cookie name
	 * @param string $value  The cookie value
	 * @param string $prefix The cookie prefix
	 *
	 * @return boolean
	 */
	public function has(string $name, string $value = '', string $prefix = ''): bool
	{
		$prefix = empty($prefix) && ! empty($this->prefix) ? $this->prefix : $prefix;

		foreach ($this->cookies as $cookie)
		{
			if ($cookie['name'] !== $prefix . $name)
			{
				continue;
			}

			if (empty($value))
			{
				return true;
			}

			return $cookie['value'] === $value;
		}

		return false;
	}

	//--------------------------------------------------------------------

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

	//--------------------------------------------------------------------

	/**
	 * Fetch a cookie.
	 *
	 * Return an item from the COOKIE array.
	 * 
	 * @param string|array|null $index  Index for item to be fetched
	 * @param integer|null      $filter The filter to be applied
	 * @param integer|null      $flags  The flags to be applied
	 *
	 * @return string|array|null
	 */
	public function fetch($index = null, int $filter = null, int $flags = null)
	{
		return Services::request()->fetchGlobal('cookie', $index, $filter, $flags);
	}

	//--------------------------------------------------------------------

	/**
	 * Clear stored cookies.
	 *
	 * @return void
	 */
	public function clear(): void
	{
		$this->cookies = [];
	}
}
