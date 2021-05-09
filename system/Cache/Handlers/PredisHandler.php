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

use CodeIgniter\Exceptions\CriticalError;
use Config\Cache;
use Exception;
use Predis\Client;
use Predis\Collection\Iterator\Keyspace;

/**
 * Predis cache handler
 */
class PredisHandler extends BaseHandler
{
	/**
	 * Default config
	 *
	 * @var array
	 */
	protected $config = [
		'scheme'   => 'tcp',
		'host'     => '127.0.0.1',
		'password' => null,
		'port'     => 6379,
		'timeout'  => 0,
	];

	/**
	 * Predis connection
	 *
	 * @var Client
	 */
	protected $redis;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * @param Cache $config
	 */
	public function __construct(Cache $config)
	{
		$this->prefix = $config->prefix;

		if (isset($config->redis))
		{
			$this->config = array_merge($this->config, $config->redis);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Takes care of any handler-specific setup that must be done.
	 */
	public function initialize()
	{
		// Try to connect to Redis, if an issue occurs throw a CriticalError exception,
		// so that the CacheFactory can attempt to initiate the next cache handler.
		try
		{
			// Create a new instance of Predis\Client
			$this->redis = new Client($this->config, ['prefix' => $this->prefix]);

			// Check if the connection is valid by trying to get the time.
			$this->redis->time();
		}
		catch (Exception $e)
		{
			// thrown if can't connect to redis server.
			throw new CriticalError('Cache: Predis connection refused (' . $e->getMessage() . ').');
		}
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
		$key = static::validateKey($key);

		$data = array_combine([
			'__ci_type',
			'__ci_value',
		],
			$this->redis->hmget($key, ['__ci_type', '__ci_value'])
		);

		if (! isset($data['__ci_type'], $data['__ci_value']) || $data['__ci_value'] === false)
		{
			return null;
		}

		switch ($data['__ci_type'])
		{
			case 'array':
			case 'object':
				return unserialize($data['__ci_value']);
			case 'boolean':
			case 'integer':
			case 'double': // Yes, 'double' is returned and NOT 'float'
			case 'string':
			case 'NULL':
				return settype($data['__ci_value'], $data['__ci_type']) ? $data['__ci_value'] : null;
			case 'resource':
			default:
				return null;
		}
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
		$key = static::validateKey($key);

		switch ($dataType = gettype($value))
		{
			case 'array':
			case 'object':
				$value = serialize($value);
				break;
			case 'boolean':
			case 'integer':
			case 'double': // Yes, 'double' is returned and NOT 'float'
			case 'string':
			case 'NULL':
				break;
			case 'resource':
			default:
				return false;
		}

		if (! $this->redis->hmset($key, ['__ci_type' => $dataType, '__ci_value' => $value]))
		{
			return false;
		}

		$this->redis->expireat($key, time() + $ttl);

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
		$key = static::validateKey($key);

		return $this->redis->del($key) === 1;
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
		$matchedKeys = [];

		foreach (new Keyspace($this->redis, $pattern) as $key)
		{
			$matchedKeys[] = $key;
		}

		return $this->redis->del($matchedKeys);
	}

	//--------------------------------------------------------------------

	/**
	 * Performs atomic incrementation of a raw stored value.
	 *
	 * @param string  $key    Cache ID
	 * @param integer $offset Step/value to increase by
	 *
	 * @return integer
	 */
	public function increment(string $key, int $offset = 1)
	{
		$key = static::validateKey($key);

		return $this->redis->hincrby($key, 'data', $offset);
	}

	//--------------------------------------------------------------------

	/**
	 * Performs atomic decrementation of a raw stored value.
	 *
	 * @param string  $key    Cache ID
	 * @param integer $offset Step/value to increase by
	 *
	 * @return integer
	 */
	public function decrement(string $key, int $offset = 1)
	{
		$key = static::validateKey($key);

		return $this->redis->hincrby($key, 'data', -$offset);
	}

	//--------------------------------------------------------------------

	/**
	 * Will delete all items in the entire cache.
	 *
	 * @return boolean Success or failure
	 */
	public function clean()
	{
		return $this->redis->flushdb()->getPayload() === 'OK';
	}

	//--------------------------------------------------------------------

	/**
	 * Returns information on the entire cache.
	 *
	 * The information returned and the structure of the data
	 * varies depending on the handler.
	 *
	 * @return array
	 */
	public function getCacheInfo()
	{
		return $this->redis->info();
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
	 */
	public function getMetaData(string $key)
	{
		$key = static::validateKey($key);

		$data = array_combine(['__ci_value'], $this->redis->hmget($key, ['__ci_value']));

		if (isset($data['__ci_value']) && $data['__ci_value'] !== false)
		{
			$time = time();
			$ttl  = $this->redis->ttl($key);

			return [
				'expire' => $ttl > 0 ? time() + $ttl : null,
				'mtime'  => $time,
				'data'   => $data['__ci_value'],
			];
		}

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
		return class_exists('\Predis\Client');
	}
}
