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

use ArrayIterator;
use CodeIgniter\HTTP\Exceptions\CookieException;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * The CookieStore object represents an immutable collection of `Cookie` value objects.
 *
 * @implements IteratorAggregate<string, Cookie>
 */
class CookieStore implements Countable, IteratorAggregate
{
	/**
	 * The cookie collection.
	 *
	 * @var array<string, Cookie>
	 */
	protected $cookies = [];

	/**
	 * Creates a CookieStore from an array of `Set-Cookie` headers.
	 *
	 * @param string[] $headers
	 * @param boolean  $raw
	 *
	 * @throws CookieException
	 *
	 * @return static
	 */
	public static function fromCookieHeaders(array $headers, bool $raw = false)
	{
		/**
		 * @var Cookie[] $cookies
		 */
		$cookies = array_filter(array_map(function (string $header) use ($raw) {
			try
			{
				return Cookie::fromHeaderString($header, $raw);
			}
			catch (CookieException $e)
			{
				return false;
			}
		}, $headers));

		return new static($cookies);
	}

	/**
	 * @param Cookie[] $cookies
	 *
	 * @throws CookieException
	 */
	public function __construct(array $cookies)
	{
		$this->validateCookies($cookies);

		foreach ($cookies as $cookie)
		{
			$this->cookies[$cookie->getId()] = $cookie;
		}
	}

	/**
	 * Checks if a `Cookie` object identified by name and
	 * prefix is present in the collection.
	 *
	 * @param string      $name
	 * @param string      $prefix
	 * @param string|null $value
	 *
	 * @return boolean
	 */
	public function has(string $name, string $prefix = '', string $value = null): bool
	{
		$name = $prefix . $name;

		foreach ($this->cookies as $cookie)
		{
			if ($cookie->getPrefixedName() !== $name)
			{
				continue;
			}

			if ($value === null)
			{
				return true;
			}

			return $cookie->getValue() === $value;
		}

		return false;
	}

	/**
	 * Retrieves an instance of CookieInterface identified by a name and prefix.
	 * This throws an exception if not found.
	 *
	 * @param string $name
	 * @param string $prefix
	 *
	 * @throws CookieException
	 *
	 * @return Cookie
	 */
	public function get(string $name, string $prefix = ''): Cookie
	{
		$name = $prefix . $name;

		foreach ($this->cookies as $cookie)
		{
			if ($cookie->getPrefixedName() === $name)
			{
				return $cookie;
			}
		}

		throw CookieException::forUnknownCookieInstance([$name, $prefix]);
	}

	/**
	 * Store a new cookie and return a new collection. The original collection
	 * is left unchanged.
	 *
	 * @param Cookie $cookie
	 *
	 * @return $this
	 */
	public function put(Cookie $cookie)
	{
		$store                            = clone $this;
		$store->cookies[$cookie->getId()] = $cookie;

		return $store;
	}

	/**
	 * Removes a cookie from a collection and returns an updated collection.
	 * The original collection is left unchanged.
	 *
	 * Removing a cookie from the store **DOES NOT** delete it from the browser.
	 * If you intend to delete a cookie *from the browser*, you must put an empty
	 * value cookie with the same name to the store.
	 *
	 * @param string $name
	 * @param string $prefix
	 *
	 * @return $this
	 */
	public function remove(string $name, string $prefix = '')
	{
		$default = Cookie::setDefaults();
		$name    = $prefix . $name;
		$domain  = $default['domain'];
		$path    = $default['path'];

		$id = implode(';', [$name, $domain, $path]);

		$store = clone $this;

		foreach ($store->cookies as $index => $cookie)
		{
			if ($index === $id)
			{
				unset($store->cookies[$index]);
			}
		}

		return $store;
	}

	/**
	 * Dispatches all cookies in store.
	 *
	 * @return void
	 */
	public function dispatch()
	{
		foreach ($this->cookies as $cookie)
		{
			$name    = $cookie->getPrefixedName();
			$value   = $cookie->getValue();
			$options = $cookie->getOptions();

			if ($cookie->isRaw())
			{
				$this->setRawCookie($name, $value, $options);
			}
			else
			{
				$this->setCookie($name, $value, $options);
			}
		}

		$this->clear();
	}

	/**
	 * Returns all cookie instances in store.
	 *
	 * @return array<string, Cookie>
	 */
	public function display()
	{
		return $this->cookies;
	}

	/**
	 * Clears the cookie collection.
	 *
	 * @return void
	 */
	public function clear(): void
	{
		$this->cookies = [];
	}

	/**
	 * Gets the Cookie count in this collection.
	 *
	 * @return integer
	 */
	public function count()
	{
		return count($this->cookies);
	}

	/**
	 * Gets the iterator for the cookie collection.
	 *
	 * @return Traversable<string, Cookie>
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->cookies);
	}

	/**
	 * Validates all cookies passed to be instances of Cookie.
	 *
	 * @param array $cookies
	 *
	 * @throws CookieException
	 *
	 * @return void
	 */
	protected function validateCookies(array $cookies): void
	{
		foreach ($cookies as $index => $cookie)
		{
			$class = is_object($cookie);
			$type  = $class ? get_class($cookie) : gettype($cookie);

			if (! $cookie instanceof Cookie)
			{
				throw CookieException::forInvalidCookieInstance([static::class, Cookie::class, $type, $index]);
			}
		}
	}

	/**
	 * Extracted call to `setrawcookie()` in order to run unit tests on it.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param string $name
	 * @param string $value
	 * @param array  $options
	 *
	 * @return void
	 */
	protected function setRawCookie(string $name, string $value, array $options)
	{
		if (PHP_VERSION_ID < 70300)
		{
			$options['path'] .= '; SameSite=' . $options['samesite'];
			unset($options['samesite']);

			$options = array_values($options);
			setrawcookie($name, $value, ...$options);
		}
		else
		{
			setrawcookie($name, $value, $options);
		}
	}

	/**
	 * Extracted call to `setcookie()` in order to run unit tests on it.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param string $name
	 * @param string $value
	 * @param array  $options
	 *
	 * @return void
	 */
	protected function setCookie(string $name, string $value, array $options)
	{
		if (PHP_VERSION_ID < 70300)
		{
			$options['path'] .= '; SameSite=' . $options['samesite'];
			unset($options['samesite']);

			$options = array_values($options);
			setcookie($name, $value, ...$options);
		}
		else
		{
			setcookie($name, $value, $options);
		}
	}
}
