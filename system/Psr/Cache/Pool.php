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

use CodeIgniter\I18n\Time;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

final class Pool implements CacheItemPoolInterface
{
	use SupportTrait;

	/**
	 * Deferred Items to be saved.
	 *
	 * @var array<string, Item>
	 */
	private $deferred = [];

	/**
	 * Commits any deferred Items.
	 */
	public function __destruct()
	{
		$this->commit();
	}

	/**
	 * Returns a Cache Item representing the specified key.
	 *
	 * This method must always return a CacheItemInterface object, even in case of
	 * a cache miss. It MUST NOT return null.
	 *
	 * @param string $key
	 *   The key for which to return the corresponding Cache Item.
	 *
	 * @throws CacheArgumentException
	 *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
	 *   MUST be thrown.
	 *
	 * @return CacheItemInterface
	 *   The corresponding Cache Item.
	 */
	public function getItem($key): CacheItemInterface
	{
		Item::validateKey($key);

		// First check for a deferred Item
		if (array_key_exists($key, $this->deferred) && ! $this->deferred[$key]->isExpired())
		{
			return (clone $this->deferred[$key])->setHit(true);
		}

		$meta = $this->adapter->getMetaData($key);

		// If the adapter does not return an array or if the item is expired then it is a miss
		if (! is_array($meta) || (is_int($meta['expire']) && $meta['expire'] < time()))
		{
			return new Item($key, null, false);
		}

		// Create the Item with the actual value
		$item = new Item($key, $this->adapter->get($key), true);

		// Check for an expiration
		if ($meta['expire'] !== null)
		{
			$item->expiresAt(Time::createFromTimestamp($meta['expire']));
		}

		return $item;
	}

	/**
	 * Returns a traversable set of cache items.
	 *
	 * @param string[] $keys
	 *   An indexed array of keys of items to retrieve.
	 *
	 * @throws CacheArgumentException
	 *   If any of the keys in $keys are not a legal value a \Psr\Cache\InvalidArgumentException
	 *   MUST be thrown.
	 *
	 * @return array
	 *   A traversable collection of Cache Items keyed by the cache keys of
	 *   each item. A Cache item will be returned for each key, even if that
	 *   key is not found. However, if no keys are specified then an empty
	 *   traversable MUST be returned instead.
	 */
	public function getItems(array $keys = []): array
	{
		// CacheInterface has no spec for multiple item retrieval
		// so we have to power through them individually.
		$items = [];
		foreach ($keys as $key)
		{
			$items[$key] = $this->getItem($key);
		}

		return $items;
	}

	/**
	 * Confirms if the cache contains specified cache item.
	 *
	 * Note: This method MAY avoid retrieving the cached value for performance reasons.
	 * This could result in a race condition with CacheItemInterface::get(). To avoid
	 * such situation use CacheItemInterface::isHit() instead.
	 *
	 * @param string $key
	 *   The key for which to check existence.
	 *
	 * @throws CacheArgumentException
	 *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
	 *   MUST be thrown.
	 *
	 * @return boolean
	 *   True if item exists in the cache, false otherwise.
	 */
	public function hasItem($key): bool
	{
		Item::validateKey($key);

		// First check for a deferred Item
		if (array_key_exists($key, $this->deferred) && ! $this->deferred[$key]->isExpired())
		{
			return true;
		}

		return is_array($this->adapter->getMetaData($key));
	}

	/**
	 * Deletes all items in the pool.
	 *
	 * @return boolean
	 *   True if the pool was successfully cleared. False if there was an error.
	 */
	public function clear()
	{
		$this->deferred = [];

		return $this->adapter->clean();
	}

	/**
	 * Removes the item from the pool.
	 *
	 * @param string $key
	 *   The key to delete.
	 *
	 * @throws CacheArgumentException
	 *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
	 *   MUST be thrown.
	 *
	 * @return boolean
	 *   True if the item was successfully removed. False if there was an error.
	 */
	public function deleteItem($key): bool
	{
		Item::validateKey($key);

		// First check for a deferred Item
		if (array_key_exists($key, $this->deferred))
		{
			unset($this->deferred[$key]);
			return true;
		}

		if ($this->hasItem($key))
		{
			return $this->adapter->delete($key);
		}

		return true;
	}

	/**
	 * Removes multiple items from the pool.
	 *
	 * @param string[] $keys
	 *   An array of keys that should be removed from the pool.
	 *
	 * @throws CacheArgumentException
	 *   If any of the keys in $keys are not a legal value a \Psr\Cache\InvalidArgumentException
	 *   MUST be thrown.
	 *
	 * @return boolean
	 *   True if the items were successfully removed. False if there was an error.
	 */
	public function deleteItems(array $keys): bool
	{
		// CacheInterface has no spec for multiple item removal
		// so we have to power through them individually.
		$return = true;
		foreach ($keys as $key)
		{
			$result = $this->deleteItem($key);
			$return = $return && $result;
		}

		return $return;
	}

	/**
	 * Persists a cache item immediately.
	 *
	 * @param CacheItemInterface $item
	 *   The cache item to save.
	 *
	 * @return boolean
	 *   True if the item was successfully persisted. False if there was an error.
	 */
	public function save(CacheItemInterface $item)
	{
		// Only deal in our Pool's Items
		if (! $item instanceof Item)
		{
			return false;
		}

		// Do not save expired Items
		if ($item->isExpired())
		{
			$this->deleteItem($item->getKey());
			return false;
		}

		// Deteremine TTL
		$ttl = ($expiration = $item->getExpiration()) ? Time::now()->difference($expiration)->getSeconds() : 60;

		return $this->adapter->save($item->getKey(), $item->get(), $ttl);
	}

	/**
	 * Sets a cache item to be persisted later.
	 *
	 * @param CacheItemInterface $item
	 *   The cache item to save.
	 *
	 * @return boolean
	 *   False if the item could not be queued or if a commit was attempted and failed. True otherwise.
	 */
	public function saveDeferred(CacheItemInterface $item): bool
	{
		// Only deal in our Pool's Items
		if (! $item instanceof Item)
		{
			return false;
		}

		// Do not save expired Items
		if ($item->isExpired())
		{
			return false;
		}

		$this->deferred[$item->getKey()] = clone $item;

		return true;
	}

	/**
	 * Persists any deferred cache items.
	 *
	 * @return boolean
	 *   True if all not-yet-saved items were successfully saved or there were none. False otherwise.
	 */
	public function commit(): bool
	{
		if ($this->deferred === [])
		{
			return true;
		}

		$failed = [];
		foreach ($this->deferred as $item)
		{
			if (! $this->save($item))
			{
				$failed[$item->getKey()] = $item;
			}
		}

		if ($failed === [])
		{
			return true;
		}

		$this->deferred = $failed;
		return false;
	}
}
