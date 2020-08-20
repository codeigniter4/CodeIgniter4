<?php namespace CodeIgniter\Test\Mock;

use CodeIgniter\Cache\CacheInterface;

class MockCache implements CacheInterface
{
	/**
	 * Prefixed to all cache names.
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * Mock cache storage.
	 *
	 * @var array
	 */
	protected $cache = [];

	//--------------------------------------------------------------------

	/**
	 * Takes care of any handler-specific setup that must be done.
	 */
	public function initialize()
	{
		// Not to see here...
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
		$key = $this->prefix . $key;

		return array_key_exists($key, $this->cache)
			? $this->cache[$key]
			: null;
	}

	//--------------------------------------------------------------------

	/**
	 * Saves an item to the cache store.
	 *
	 * The $raw parameter is only utilized by Mamcache in order to
	 * allow usage of increment() and decrement().
	 *
	 * @param string  $key   Cache item name
	 * @param mixed   $value the data to save
	 * @param integer $ttl   Time To Live, in seconds (default 60)
	 * @param boolean $raw   Whether to store the raw value.
	 *
	 * @return mixed
	 */
	public function save(string $key, $value, int $ttl = 60, bool $raw = false)
	{
		$key = $this->prefix . $key;

		$this->cache[$key] = $value;

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Deletes a specific item from the cache store.
	 *
	 * @param string $key Cache item name
	 *
	 * @return mixed
	 */
	public function delete(string $key)
	{
		unset($this->cache[$key]);
	}

	//--------------------------------------------------------------------

	/**
	 * Performs atomic incrementation of a raw stored value.
	 *
	 * @param string  $key    Cache ID
	 * @param integer $offset Step/value to increase by
	 *
	 * @return mixed
	 */
	public function increment(string $key, int $offset = 1)
	{
		$key = $this->prefix . $key;

		$data = $this->cache[$key] ?: null;

		if (empty($data))
		{
			$data = 0;
		}
		elseif (! is_int($data))
		{
			return false;
		}

		return $this->save($key, $data + $offset);
	}

	//--------------------------------------------------------------------

	/**
	 * Performs atomic decrementation of a raw stored value.
	 *
	 * @param string  $key    Cache ID
	 * @param integer $offset Step/value to increase by
	 *
	 * @return mixed
	 */
	public function decrement(string $key, int $offset = 1)
	{
		$key = $this->prefix . $key;

		$data = $this->cache[$key] ?: null;

		if (empty($data))
		{
			$data = 0;
		}
		elseif (! is_int($data))
		{
			return false;
		}

		return $this->save($key, $data - $offset);
	}

	//--------------------------------------------------------------------

	/**
	 * Will delete all items in the entire cache.
	 *
	 * @return mixed
	 */
	public function clean()
	{
		$this->cache = [];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns information on the entire cache.
	 *
	 * The information returned and the structure of the data
	 * varies depending on the handler.
	 *
	 * @return mixed
	 */
	public function getCacheInfo()
	{
		return [];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns detailed information about the specific item in the cache.
	 *
	 * @param string $key Cache item name.
	 *
	 * @return mixed
	 */
	public function getMetaData(string $key)
	{
		return false;
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

	//--------------------------------------------------------------------

}
