<?php namespace CodeIgniter\Cache\Handlers;

use CodeIgniter\Cache\CacheInterface;

class MemcachedHandler implements CacheInterface
{
	/**
	 * Prefixed to all cache names.
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * The memcached object
	 *
	 * @var string
	 */
	protected $memcached;

	/**
	 * Memcached Configuration
	 *
	 * @var array
	 */
	protected $config = [
		'default' => [
			'host'   => '127.0.0.1',
			'port'   => 11211,
			'weight' => 1,
		],
	];

	//--------------------------------------------------------------------

	public function __construct($config)
	{
		$this->prefix = $config->prefix ?: '';

		if (isset($config->memcached))
		{
			$this->config = $config->memcached;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Takes care of any handler-specific setup that must be done.
	 */
	public function initialize()
	{
		$defaults = $this->config['default'];

		if (class_exists('Memcached'))
		{
			$this->memcached = new Memcached();
		}
		elseif (class_exists('Memcache'))
		{
			$this->memcached = new Memcache();
		}
		else
		{
//			log_message('error', 'Cache: Failed to create Memcache(d) object; extension not loaded?');

			return;
		}

		foreach ($this->config as $cacheName => $cacheServer)
		{
			if (! isset($cacheServer['hostname']))
			{
//				log_message('debug', 'Cache: Memcache(d) configuration "'.$cacheName.'" doesn\'t include a hostname; ignoring.');
				continue;
			}
			elseif ($cacheServer['hostname'][0] === '/')
			{
				$cacheServer['port'] = 0;
			}
			elseif (empty($cacheServer['port']))
			{
				$cacheServer['port'] = $defaults['port'];
			}

			isset($cacheServer['weight']) OR $cacheServer['weight'] = $defaults['weight'];

			if ($this->memcached instanceof Memcache)
			{
				// Third parameter is persistance and defaults to TRUE.
				$this->memcached->addServer(
					$cacheServer['hostname'],
					$cacheServer['port'],
					true,
					$cacheServer['weight']
				);
			}
			elseif ($this->memcached instanceof Memcached)
			{
				$this->memcached->addServer(
					$cacheServer['hostname'],
					$cacheServer['port'],
					$cacheServer['weight']
				);
			}
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
		$key = $this->prefix.$key;

		$data = $this->memcached->get($key);

		return is_array($data) ? $data[0] : $data;
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
	public function save(string $key, $value, int $ttl = 60, bool $raw = false)
	{
		$key = $this->prefix.$key;

		if ($raw !== true)
		{
			$value = [$value, time(), $ttl];
		}

		if ($this->memcached instanceof Memcached)
		{
			return $this->memcached->set($key, $value, $ttl);
		}
		elseif ($this->memcached instanceof Memcache)
		{
			return $this->memcached->set($key, $value, 0, $ttl);
		}

		return false;
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
		$key = $this->prefix.$key;

		return $this->memcached->delete($key);
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
		$key = $this->prefix.$key;

		return $this->memcached->increment($key, $offset);
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
		$key = $this->prefix.$key;

		return $this->memcached->decrement($key, $offset);
	}

	//--------------------------------------------------------------------

	/**
	 * Will delete all items in the entire cache.
	 *
	 * @return mixed
	 */
	public function clean()
	{
		return $this->memcached->flush();
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
		return $this->memcached->getStats();
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
		$key = $this->prefix.$key;

		$stored = $this->memcached->get($key);

		if (count($stored) !== 3)
		{
			return FALSE;
		}

		list($data, $time, $ttl) = $stored;

		return array(
			'expire'	=> $time + $ttl,
			'mtime'		=> $time,
			'data'		=> $data
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
		return (extension_loaded('memcached') OR extension_loaded('memcache'));
	}

	//--------------------------------------------------------------------

	/**
	 * Class destructor
	 *
	 * Closes the connection to Memcache(d) if present.
	 */
	public function __destruct()
	{
		if ($this->memcached instanceof Memcache)
		{
			$this->memcached->close();
		}
		elseif ($this->memcached instanceof Memcached)
		{
			$this->memcached->quit();
		}
	}

	//--------------------------------------------------------------------

}