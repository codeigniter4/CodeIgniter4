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

use Closure;

/**
 * Dummy cache handler
 */
class DummyHandler extends BaseHandler
{
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
	 * @return null
	 */
	public function get(string $key)
	{
		return null;
	}

	//--------------------------------------------------------------------

	/**
	 * Get an item from the cache, or execute the given Closure and store the result.
	 *
	 * @param string  $key      Cache item name
	 * @param integer $ttl      Time to live
	 * @param Closure $callback Callback return value
	 *
	 * @return null
	 */
	public function remember(string $key, int $ttl, Closure $callback)
	{
		return null;
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
		return true;
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
		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Deletes items from the cache store matching a given pattern.
	 *
	 * @param string $pattern Cache items glob-style pattern
	 *
	 * @return integer The number of deleted items
	 */
	public function deleteMatching(string $pattern)
	{
		return 0;
	}

	//--------------------------------------------------------------------

	/**
	 * Performs atomic incrementation of a raw stored value.
	 *
	 * @param string  $key    Cache ID
	 * @param integer $offset Step/value to increase by
	 *
	 * @return boolean
	 */
	public function increment(string $key, int $offset = 1)
	{
		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Performs atomic decrementation of a raw stored value.
	 *
	 * @param string  $key    Cache ID
	 * @param integer $offset Step/value to increase by
	 *
	 * @return boolean
	 */
	public function decrement(string $key, int $offset = 1)
	{
		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Will delete all items in the entire cache.
	 *
	 * @return boolean Success or failure
	 */
	public function clean()
	{
		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns information on the entire cache.
	 *
	 * The information returned and the structure of the data
	 * varies depending on the handler.
	 *
	 * @return null
	 */
	public function getCacheInfo()
	{
		return null;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns detailed information about the specific item in the cache.
	 *
	 * @param string $key Cache item name.
	 *
	 * @return null
	 */
	public function getMetaData(string $key)
	{
		return null;
	}

	//--------------------------------------------------------------------

	/**
	 * Determines if the driver is supported on this system.
	 *
	 * @return boolean
	 */
	public function isSupported(): bool
	{
		return true;
	}
}
