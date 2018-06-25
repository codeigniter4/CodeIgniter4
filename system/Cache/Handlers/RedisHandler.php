<?php namespace CodeIgniter\Cache\Handlers;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2018 British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */
use CodeIgniter\Exceptions\CriticalError;
use CodeIgniter\Cache\CacheInterface;

class RedisHandler implements CacheInterface
{

	/**
	 * Prefixed to all cache names.
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * Default config
	 *
	 * @static
	 * @var    array
	 */
	protected $config = [
		'host'		 => '127.0.0.1',
		'password'	 => null,
		'port'		 => 6379,
		'timeout'	 => 0,
	];

	/**
	 * Redis connection
	 *
	 * @var    Redis
	 */
	protected $redis;

	//--------------------------------------------------------------------

	public function __construct($config)
	{
		$config = (array)$config;
		$this->prefix = $config['prefix'] ?? '';

		if ( ! empty($config))
		{
			$this->config = array_merge($this->config, $config['redis']);
		}
	}

	/**
	 * Class destructor
	 *
	 * Closes the connection to Memcache(d) if present.
	 */
	public function __destruct()
	{
		if ($this->redis)
		{
			$this->redis->close();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Takes care of any handler-specific setup that must be done.
	 */
	public function initialize()
	{
		$config = $this->config;

		$this->redis = new \Redis();

		try
		{
			if ( ! $this->redis->connect($config['host'], ($config['host'][0] === '/' ? 0 : $config['port']), $config['timeout'])
			)
			{
//				log_message('error', 'Cache: Redis connection failed. Check your configuration.');
			}

			if (isset($config['password']) && ! $this->redis->auth($config['password']))
			{
//				log_message('error', 'Cache: Redis authentication failed.');
			}
		} catch (\RedisException $e)
		{
			throw new CriticalError('Cache: Redis connection refused (' . $e->getMessage() . ')');
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
		$key = $this->prefix . $key;

		$data = $this->redis->hMGet($key, ['__ci_type', '__ci_value']);

		if ( ! isset($data['__ci_type'], $data['__ci_value']) || $data['__ci_value'] === false)
		{
			return false;
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
				return settype($data['__ci_value'], $data['__ci_type']) ? $data['__ci_value'] : false;
			case 'resource':
			default:
				return false;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Saves an item to the cache store.
	 *
	 * @param string $key   Cache item name
	 * @param mixed  $value The data to save
	 * @param int    $ttl   Time To Live, in seconds (default 60)
	 *
	 * @return mixed
	 */
	public function save(string $key, $value, int $ttl = 60)
	{
		$key = $this->prefix . $key;

		switch ($data_type = gettype($value))
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

		if ( ! $this->redis->hMSet($key, ['__ci_type' => $data_type, '__ci_value' => $value]))
		{
			return false;
		}
		elseif ($ttl)
		{
			$this->redis->expireAt($key, time() + $ttl);
		}

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
		$key = $this->prefix . $key;

		return ($this->redis->delete($key) === 1);
	}

	//--------------------------------------------------------------------

	/**
	 * Performs atomic incrementation of a raw stored value.
	 *
	 * @param string $key    Cache ID
	 * @param int    $offset Step/value to increase by
	 *
	 * @return mixed
	 */
	public function increment(string $key, int $offset = 1)
	{
		$key = $this->prefix . $key;

		return $this->redis->hIncrBy($key, 'data', $offset);
	}

	//--------------------------------------------------------------------

	/**
	 * Performs atomic decrementation of a raw stored value.
	 *
	 * @param string $key    Cache ID
	 * @param int    $offset Step/value to increase by
	 *
	 * @return mixed
	 */
	public function decrement(string $key, int $offset = 1)
	{
		$key = $this->prefix . $key;

		return $this->redis->hIncrBy($key, 'data', -$offset);
	}

	//--------------------------------------------------------------------

	/**
	 * Will delete all items in the entire cache.
	 *
	 * @return mixed
	 */
	public function clean()
	{
		return $this->redis->flushDB();
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
		return $this->redis->info();
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
		$key = $this->prefix . $key;

		$value = $this->get($key);

		if ($value !== FALSE)
		{
			$time = time();
			return [
				'expire' => $time + $this->redis->ttl($key),
				'mtime' => $time,
				'data' => $value
			];
		}

		return FALSE;
	}

	//--------------------------------------------------------------------

	/**
	 * Determines if the driver is supported on this system.
	 *
	 * @return boolean
	 */
	public function isSupported(): bool
	{
		return extension_loaded('redis');
	}

	//--------------------------------------------------------------------
}
