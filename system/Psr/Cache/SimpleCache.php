<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Psr\Cache;

use DateInterval;
use Psr\SimpleCache\CacheInterface;
use Traversable;

final class SimpleCache implements CacheInterface
{
	use SupportTrait;

	/**
	 * Fetches a value from the cache.
	 *
	 * @param string $key     The unique key of this item in the cache.
	 * @param mixed  $default Default value to return if the key does not exist.
	 *
	 * @return mixed The value of the item from the cache, or $default in case of cache miss.
	 *
	 * @throws CacheArgumentException
	 *   MUST be thrown if the $key string is not a legal value.
	 */
	public function get($key, $default = null)
	{
		Item::validateKey($key);

		$meta = $this->adapter->getMetaData($key);

		// If the adapter does not return an array or if the item is expired then it is a miss
		if (! is_array($meta) || (is_int($meta['expire']) && $meta['expire'] < time()))
		{
			return $default;
		}

		return $this->adapter->get($key);
	}

	/**
	 * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
	 *
	 * @param string                    $key   The key of the item to store.
	 * @param mixed                     $value The value of the item to store. Must be serializable.
	 * @param null|integer|DateInterval $ttl   Optional. The TTL value of this item. If no value is sent and
	 *                                          the driver supports TTL then the library may set a default value
	 *                                          for it or let the driver take care of that.
	 *
	 * @return boolean True on success and false on failure.
	 *
	 * @throws CacheArgumentException
	 *   MUST be thrown if the $key string is not a legal value.
	 */
	public function set($key, $value, $ttl = null)
	{
		Item::validateKey($key);

		// Get TTL as an integer (seconds)
		if (is_null($ttl))
		{
			$ttl = 60;
		}
		elseif ($ttl instanceof DateInterval)
		{
			$ttl = $ttl->s;
		}
		elseif (! is_int($ttl))
		{
			throw new CacheArgumentException('TTL value must be one of: null, integer, DateInterval.');
		}

		// Do not save expired items
		if ($ttl <= 0)
		{
			$this->delete($key);
			return false;
		}

		return $this->adapter->save($key, $value, $ttl);
	}

	/**
	 * Delete an item from the cache by its unique key.
	 *
	 * @param string $key The unique cache key of the item to delete.
	 *
	 * @return boolean True if the item was successfully removed. False if there was an error.
	 *
	 * @throws CacheArgumentException
	 *   MUST be thrown if the $key string is not a legal value.
	 */
	public function delete($key)
	{
		Item::validateKey($key);

		// Nonexistant keys return true
		if (! is_array($this->adapter->getMetaData($key)))
		{
			return true;
		}

		return $this->adapter->delete($key);
	}

	/**
	 * Wipes clean the entire cache's keys.
	 *
	 * @return boolean True on success and false on failure.
	 */
	public function clear()
	{
		return $this->adapter->clean();
	}

	/**
	 * Obtains multiple cache items by their unique keys.
	 *
	 * @param iterable $keys    A list of keys that can obtained in a single operation.
	 * @param mixed    $default Default value to return for keys that do not exist.
	 *
	 * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
	 *
	 * @throws CacheArgumentException
	 *   MUST be thrown if $keys is neither an array nor a Traversable,
	 *   or if any of the $keys are not a legal value.
	 */
	public function getMultiple($keys, $default = null)
	{
		if (! (is_array($keys) || $keys instanceof Traversable))
		{
			throw new CacheArgumentException('getMultiple only accepts traversable input.');
		}

		// CacheInterface has no spec for multiple item retrieval
		// so we have to power through them individually.
		$items = [];
		foreach ($keys as $key)
		{
			$items[$key] = $this->get($key, $default);
		}

		return $items;
	}

	/**
	 * Persists a set of key => value pairs in the cache, with an optional TTL.
	 *
	 * @param iterable                  $values A list of key => value pairs for a multiple-set operation.
	 * @param null|integer|DateInterval $ttl    Optional. The TTL value of this item. If no value is sent and
	 *                                           the driver supports TTL then the library may set a default value
	 *                                           for it or let the driver take care of that.
	 *
	 * @return boolean True on success and false on failure.
	 *
	 * @throws CacheArgumentException
	 *   MUST be thrown if $values is neither an array nor a Traversable,
	 *   or if any of the $values are not a legal value.
	 */
	public function setMultiple($values, $ttl = null)
	{
		if (! (is_array($values) || $values instanceof Traversable))
		{
			throw new CacheArgumentException('setMultiple only accepts traversable input.');
		}

		// CacheInterface has no spec for multiple item storage
		// so we have to power through them individually.
		$return = true;
		foreach ($values as $key => $value)
		{
			if (is_int($key))
			{
				$key = (string) $key;
			}
			$result = $this->set($key, $value, $ttl);
			$return = $result && $return;
		}

		return $return;
	}

	/**
	 * Deletes multiple cache items in a single operation.
	 *
	 * @param iterable $keys A list of string-based keys to be deleted.
	 *
	 * @return boolean True if the items were successfully removed. False if there was an error.
	 *
	 * @throws CacheArgumentException
	 *   MUST be thrown if $keys is neither an array nor a Traversable,
	 *   or if any of the $keys are not a legal value.
	 */
	public function deleteMultiple($keys)
	{
		if (! (is_array($keys) || $keys instanceof Traversable))
		{
			throw new CacheArgumentException('deleteMultiple only accepts traversable input.');
		}

		// CacheInterface has no spec for multiple item removal
		// so we have to power through them individually.
		$return = true;
		foreach ($keys as $key)
		{
			$result = $this->delete($key);
			$return = $result && $return;
		}

		return $return;
	}

	/**
	 * Determines whether an item is present in the cache.
	 *
	 * NOTE: It is recommended that has() is only to be used for cache warming type purposes
	 * and not to be used within your live applications operations for get/set, as this method
	 * is subject to a race condition where your has() will return true and immediately after,
	 * another script can remove it, making the state of your app out of date.
	 *
	 * @param string $key The cache item key.
	 *
	 * @return boolean
	 *
	 * @throws CacheArgumentException
	 *   MUST be thrown if the $key string is not a legal value.
	 */
	public function has($key)
	{
		Item::validateKey($key);

		$meta = $this->adapter->getMetaData($key);

		// The adapter must return an array that is not expired
		return is_array($meta) && is_int($meta['expire']) && $meta['expire'] > time();
	}
}
