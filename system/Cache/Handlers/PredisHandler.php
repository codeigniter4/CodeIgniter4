<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
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
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2014-2019 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Cache\Handlers;

use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Exceptions\CriticalError;

/**
 * Predis cache handler
 */
class PredisHandler implements CacheInterface
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
		'scheme'   => 'tcp',
		'host'     => '127.0.0.1',
		'password' => null,
		'port'     => 6379,
		'timeout'  => 0,
	];

	/**
	 * Predis connection
	 *
	 * @var Predis
	 */
	protected $redis;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * @param  type $config
	 * @throws type
	 */
	public function __construct($config)
	{
		$this->prefix = $config->prefix ?: '';

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
		try
		{
			// Create a new instance of Predis\Client
			$this->redis = new \Predis\Client($this->config, ['prefix' => $this->prefix]);

			// Check if the connection is valid by trying to get the time.
			$this->redis->time();
		}
		catch (\Exception $e)
		{
			// thrown if can't connect to redis server.
			throw new CriticalError('Cache: Predis connection refused (' . $e->getMessage() . ')');
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
		$data = array_combine([
			'__ci_type',
			'__ci_value',
		], $this->redis->hmget($key, ['__ci_type', '__ci_value'])
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
	 * @return mixed
	 */
	public function save(string $key, $value, int $ttl = 60)
	{
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

		if (! $this->redis->hmset($key, ['__ci_type' => $data_type, '__ci_value' => $value]))
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
	 * @return mixed
	 */
	public function delete(string $key)
	{
		return ($this->redis->del($key) === 1);
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
		return $this->redis->hincrby($key, 'data', $offset);
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
		return $this->redis->hincrby($key, 'data', -$offset);
	}

	//--------------------------------------------------------------------

	/**
	 * Will delete all items in the entire cache.
	 *
	 * @return mixed
	 */
	public function clean()
	{
		return $this->redis->flushdb();
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
		$data = array_combine(['__ci_value'], $this->redis->hmget($key, ['__ci_value']));

		if (isset($data['__ci_value']) && $data['__ci_value'] !== false)
		{
			return [
				'expire' => time() + $this->redis->ttl($key),
				'data'   => $data['__ci_value'],
			];
		}

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
		return class_exists('\Predis\Client');
	}

}
