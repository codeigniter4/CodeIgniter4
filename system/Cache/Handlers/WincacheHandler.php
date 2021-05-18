<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Cache\Handlers;

use Config\Cache;
use Exception;

/**
 * Cache handler for WinCache from Microsoft & IIS.
 *
 * @codeCoverageIgnore
 */
class WincacheHandler extends BaseHandler
{
	/**
	 * Constructor.
	 *
	 * @param Cache $config
	 */
	public function __construct(Cache $config)
	{
		$this->prefix = $config->prefix;
	}

	//--------------------------------------------------------------------

	/**
	 * Takes care of any handler-specific setup that must be done.
	 */
	public function initialize()
	{
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to fetch an item from the cache store.
	 *
	 * @param string $key Cache item name
	 *
	 * @return mixed
	 */
	public function get(string $key)
	{
		$key     = static::validateKey($key, $this->prefix);
		$success = false;

		$data = wincache_ucache_get($key, $success);

		// Success returned by reference from wincache_ucache_get()
		return $success ? $data : null;
	}

	//--------------------------------------------------------------------

	/**
	 * Saves an item to the cache store.
	 *
	 * @param string  $key   Cache item name
	 * @param mixed   $value The data to save
	 * @param integer $ttl   Time To Live, in seconds (default 60)
	 *
	 * @return boolean Success or failure
	 */
	public function save(string $key, $value, int $ttl = 60)
	{
		$key = static::validateKey($key, $this->prefix);

		return wincache_ucache_set($key, $value, $ttl);
	}

	//--------------------------------------------------------------------

	/**
	 * Deletes a specific item from the cache store.
	 *
	 * @param string $key Cache item name
	 *
	 * @return boolean Success or failure
	 */
	public function delete(string $key)
	{
		$key = static::validateKey($key, $this->prefix);

		return wincache_ucache_delete($key);
	}

	//--------------------------------------------------------------------

	/**
	 * Deletes items from the cache store matching a given pattern.
	 *
	 * @param string $pattern Cache items glob-style pattern
	 *
	 * @throws Exception
	 */
	public function deleteMatching(string $pattern)
	{
		throw new Exception('The deleteMatching method is not implemented for Wincache. You must select File, Redis or Predis handlers to use it.');
	}

	//--------------------------------------------------------------------

	/**
	 * Performs atomic incrementation of a raw stored value.
	 *
	 * @param string  $key    Cache ID
	 * @param integer $offset Step/value to increase by
	 *
	 * @return integer|false
	 */
	public function increment(string $key, int $offset = 1)
	{
		$key = static::validateKey($key, $this->prefix);

		return wincache_ucache_inc($key, $offset);
	}

	//--------------------------------------------------------------------

	/**
	 * Performs atomic decrementation of a raw stored value.
	 *
	 * @param string  $key    Cache ID
	 * @param integer $offset Step/value to increase by
	 *
	 * @return integer|false
	 */
	public function decrement(string $key, int $offset = 1)
	{
		$key = static::validateKey($key, $this->prefix);

		return wincache_ucache_dec($key, $offset);
	}

	//--------------------------------------------------------------------

	/**
	 * Will delete all items in the entire cache.
	 *
	 * @return boolean Success or failure
	 */
	public function clean()
	{
		return wincache_ucache_clear();
	}

	//--------------------------------------------------------------------

	/**
	 * Returns information on the entire cache.
	 *
	 * The information returned and the structure of the data
	 * varies depending on the handler.
	 *
	 * @return array|false
	 */
	public function getCacheInfo()
	{
		return wincache_ucache_info(true);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns detailed information about the specific item in the cache.
	 *
	 * @param string $key Cache item name.
	 *
	 * @return array|false|null
	 *   Returns null if the item does not exist, otherwise array<string, mixed>
	 *   with at least the 'expire' key for absolute epoch expiry (or null).
	 *   Some handlers may return false when an item does not exist, which is deprecated.
	 */
	public function getMetaData(string $key)
	{
		$key = static::validateKey($key, $this->prefix);

		if ($stored = wincache_ucache_info(false, $key))
		{
			$age      = $stored['ucache_entries'][1]['age_seconds'];
			$ttl      = $stored['ucache_entries'][1]['ttl_seconds'];
			$hitcount = $stored['ucache_entries'][1]['hitcount'];

			return [
				'expire'   => $ttl > 0 ? time() + $ttl : null,
				'hitcount' => $hitcount,
				'age'      => $age,
				'ttl'      => $ttl,
			];
		}

		return false; // This will return null in a future release
	}

	//--------------------------------------------------------------------

	/**
	 * Determines if the driver is supported on this system.
	 *
	 * @return boolean
	 */
	public function isSupported(): bool
	{
		return extension_loaded('wincache') && ini_get('wincache.ucenabled');
	}
}
