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

use ArrayIterator;
use CodeIgniter\Cookie\Exceptions\CookieException;
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
				log_message('error', $e->getMessage());
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
	final public function __construct(array $cookies)
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
				return true; // for BC
			}

			return $cookie->getValue() === $value;
		}

		return false;
	}

	/**
	 * Retrieves an instance of `Cookie` identified by a name and prefix.
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
	 * @return static
	 */
	public function put(Cookie $cookie)
	{
		$store = clone $this;

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
	 * @return static
	 */
	public function remove(string $name, string $prefix = '')
	{
		$default = Cookie::setDefaults();

		$id = implode(';', [$prefix . $name, $default['path'], $default['domain']]);

		$store = clone $this;

		foreach (array_keys($store->cookies) as $index)
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
	public function dispatch(): void
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
	public function display(): array
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
	public function count(): int
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
			$type = is_object($cookie) ? get_class($cookie) : gettype($cookie);

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
	protected function setRawCookie(string $name, string $value, array $options): void
	{
		setrawcookie($name, $value, $options);
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
	protected function setCookie(string $name, string $value, array $options): void
	{
		setcookie($name, $value, $options);
	}
}
