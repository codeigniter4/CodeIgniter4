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

class AerospikeHandler implements CacheInterface
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
	    'hosts'			=> [
	        [
	        	'addr'		=> '127.0.0.1',
				'port'		=> 3000
	        ]
	    ],
		'namespace'		=> 'test',
		'set'			=> 'cache',
	    'persistent'	=> true,
		'prefix'		=> '',
		'options'		=> [
			// \Aerospike::OPT_CONNECT_TIMEOUT => 1250,
			// \Aerospike::OPT_WRITE_TIMEOUT   => 1500
		]
	];

	/**
	 * Aerospike connection
	 *
	 * @var    Aerospike
	 */
	protected $aerospike;

	//--------------------------------------------------------------------

	public function __construct($config)
	{
		$config = (array)$config;
		$this->prefix = $config['prefix'] ?? '';

		if ( ! empty($config))
		{
			$this->config = array_merge($this->config, $config);
		}
	}

	/**
	 * Class destructor
	 *
	 * Closes the connection to Aerospike if present.
	 */
	public function __destruct()
	{
		if ($this->aerospike)
		{
			$this->aerospike->close();
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
	        $persistent = false;

	        if (isset($this->config['persistent']))
	        {
	            $persistent = (bool) $this->config['persistent'];
	        }

	        $opts = [];

	        if (isset($options['options']) && is_array($options['options']))
	        {
	            $opts = $options['options'];
	        }

			$this->aerospike = new \Aerospike([
				'hosts'	=> $this->config['hosts']
			], $persistent, $opts);

			if ( ! $this->aerospike->isConnected())
			{
				throw new CriticalError('Cache: Aerospike connection refused [{'. $this->aerospike->errorno() .'}]: {'. $this->aerospike->error() .'}');
			}
		}
		catch (\Exception $e)
		{
			throw new CriticalError('Cache: Aerospike connection refused ('.$e->getMessage().')');
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
		$key = $this->buildKey($this->prefix . $key);

		$status = $this->aerospike->get($key, $record);

		if ($status === \Aerospike::OK)
		{
			if (! isset($record['bins']['__ci_type'], $record['bins']['__ci_value']) OR $record['bins']['__ci_value'] === false)
			{
				return false;
			}
	
			switch ($record['bins']['__ci_type'])
			{
				case 'array':
				case 'object':
					return unserialize($record['bins']['__ci_value']);
				case 'boolean':
				case 'integer':
				case 'double': // Yes, 'double' is returned and NOT 'float'
				case 'string':
				case 'NULL':
					return settype($record['bins']['__ci_value'], $record['bins']['__ci_type']) ? $record['bins']['__ci_value'] : false;
				case 'resource':
				default:
					return false;
			}
		}
		else
		{
			return false;
		}
	}

    /**
     * {@inheritdoc}
     *
     * @param string $prefix
     * @return array
     */
    public function getAllKeys($filter = '*')
    {
        $keys = [];

        $globalPrefix = $this->prefix;

        $this->aerospike->scan($this->config['namespace'], $this->config['set'], function ($record) use (&$keys, $filter, $globalPrefix)
        {
            $key = $record['key']['key'];

            if (empty($filter) || 0 === strpos($key, $filter))
            {
                $keys[] = preg_replace(sprintf('#^%s(.+)#u', preg_quote($globalPrefix)), '$1', $key);
            }
        });

        return $keys;
	}

	//--------------------------------------------------------------------

	/**
	 * Saves an item to the cache store.
	 *
	 * The $raw parameter is only utilized by Mamcache in order to
	 * allow usage of increment() and decrement().
	 *
	 * @param string $key    Cache item name
	 * @param        $value  the data to save
	 * @param null   $ttl    Time To Live, in seconds (default 60)
	 * @param bool   $raw    Whether to store the raw value.
	 *
	 * @return mixed
	 */
	public function save(string $key, $value, int $ttl = 60)
	{
		$key = $this->buildKey($this->prefix . $key);

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

		$bins	= [
			'__ci_type' 	=> $data_type, 
			'__ci_value' 	=> $value
		];

		$option = [\Aerospike::OPT_POLICY_KEY => \Aerospike::POLICY_KEY_SEND];
		$status = $this->aerospike->put($key, $bins, $ttl, $option);

		if ($status === \Aerospike::OK)
		{
			return true;
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
        $key = $this->buildKey($this->prefix . $key);

        $status = $this->aerospike->remove($key);

        return $status === \Aerospike::OK;
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
        if ( ! $key)
        {
            return false;
        }

        $key = $this->buildKey($prefixedKey);

        $this->aerospike->increment($key, '__ci_value', $offset);

        $status = $this->aerospike->get($key, $record);

        if ($status !== \Aerospike::OK)
        {
            return false;
        }

        return $record['bins']['__ci_value'];
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
        return $this->increment($key, -$offset);
	}

	//--------------------------------------------------------------------

	/**
	 * Will delete all items in the entire cache.
	 *
	 * @return mixed
	 */
	public function clean()
	{
        $keys = $this->getAllKeys();

        $success = true;

        foreach ($keys as $aKey)
        {
            if ( ! $this->delete($aKey))
            {
                $success = false;
            }
        }

        return $success;
	}

	//--------------------------------------------------------------------

    /**
     * {@inheritdoc}
     *
     * @param string $keyName
     * @param int    $lifetime
     * @return boolean
     */
    public function exists(string $key)
    {
        if ( ! $key)
        {
            return false;
        }

		$key = $this->buildKey($this->prefix . $key);

        return $this->aerospike->exists($key, $cache) === \Aerospike::OK;
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
		$status = $this->aerospike->getNodes();

		if ($status == null)
		{
		    return false;
		}
		else
		{
		    return $status;
		}
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
		$key = $this->buildKey($this->prefix . $key);

		$status = $this->aerospike->get($key, $record);

		if ($status === \Aerospike::OK)
		{
			if (isset($record['metadata']['ttl']))
			{
				$time = time();

				return [
					'generation' => $record['metadata']['generation'],
					'expire' => $time + $record['metadata']['ttl'],
					'mtime' => $time,
					'data' => $record['bins']['__ci_value']
				];
			}
		}

		return false;
	}

	//--------------------------------------------------------------------

    /**
     * Generates a unique key used for storing cache data in Aerospike DB.
     *
     * @param string $key Cache key
     * @return array
     */
    protected function buildKey(string $key)
    {
        return $this->aerospike->initKey(
			$this->config['namespace'],
            $this->config['set'],
            $key
        );
    }

	//--------------------------------------------------------------------

	/**
	 * Determines if the driver is supported on this system.
	 *
	 * @return boolean
	 */
	public function isSupported(): bool
	{
		return extension_loaded('aerospike');
	}

	//--------------------------------------------------------------------
}