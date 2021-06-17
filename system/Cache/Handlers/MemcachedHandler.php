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
use Memcache;
use Memcached;

/**
 * Mamcached cache handler
 */
class MemcachedHandler extends BaseHandler
{
    /**
     * The memcached object
     *
     * @var Memcache|Memcached
     */
    protected $memcached;

    /**
     * Memcached Configuration
     *
     * @var array
     */
    protected $config = [
        'host'   => '127.0.0.1',
        'port'   => 11211,
        'weight' => 1,
        'raw'    => false,
    ];

    //--------------------------------------------------------------------

    /**
     * Constructor.
     *
     * @param Cache $config
     */
    public function __construct(Cache $config)
    {
        $this->prefix = $config->prefix;

        if (! empty($config)) {
            $this->config = array_merge($this->config, $config->memcached);
        }
    }

    /**
     * Class destructor
     *
     * Closes the connection to Memcache(d) if present.
     */
    public function __destruct()
    {
        if ($this->memcached instanceof Memcached) {
            $this->memcached->quit();
        } elseif ($this->memcached instanceof Memcache) {
            $this->memcached->close();
        }
    }

    //--------------------------------------------------------------------

    /**
     * Takes care of any handler-specific setup that must be done.
     */
    public function initialize()
    {
        // Try to connect to Memcache or Memcached, if an issue occurs throw a CriticalError exception,
        // so that the CacheFactory can attempt to initiate the next cache handler.
        try {
            if (class_exists(Memcached::class)) {
                // Create new instance of Memcached
                $this->memcached = new Memcached();
                if ($this->config['raw']) {
                    $this->memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
                }

                // Add server
                $this->memcached->addServer(
                    $this->config['host'],
                    $this->config['port'],
                    $this->config['weight']
                );

                // attempt to get status of servers
                $stats = $this->memcached->getStats();

                // $stats should be an associate array with a key in the format of host:port.
                // If it doesn't have the key, we know the server is not working as expected.
                if (! isset($stats[$this->config['host'] . ':' . $this->config['port']])) {
                    throw new CriticalError('Cache: Memcached connection failed.');
                }
            } elseif (class_exists(Memcache::class)) {
                // Create new instance of Memcache
                $this->memcached = new Memcache();

                // Check if we can connect to the server
                $canConnect = $this->memcached->connect(
                    $this->config['host'],
                    $this->config['port']
                );

                // If we can't connect, throw a CriticalError exception
                if ($canConnect === false) {
                    throw new CriticalError('Cache: Memcache connection failed.');
                }

                // Add server, third parameter is persistence and defaults to TRUE.
                $this->memcached->addServer(
                    $this->config['host'],
                    $this->config['port'],
                    true,
                    $this->config['weight']
                );
            } else {
                throw new CriticalError('Cache: Not support Memcache(d) extension.');
            }
        } catch (CriticalError $e) {
            throw $e;
        } catch (Exception $e) {
            throw new CriticalError('Cache: Memcache(d) connection refused (' . $e->getMessage() . ').');
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
        $key = static::validateKey($key, $this->prefix);

        if ($this->memcached instanceof Memcached) {
            $data = $this->memcached->get($key);

            // check for unmatched key
            if ($this->memcached->getResultCode() === Memcached::RES_NOTFOUND) {
                return null;
            }
        } elseif ($this->memcached instanceof Memcache) {
            $flags = false;
            $data  = $this->memcached->get($key, $flags); // @phpstan-ignore-line

            // check for unmatched key (i.e. $flags is untouched)
            if ($flags === false) {
                return null;
            }
        }

        return is_array($data) ? $data[0] : $data; // @phpstan-ignore-line
    }

    //--------------------------------------------------------------------

    /**
     * Saves an item to the cache store.
     *
     * @param string $key   Cache item name
     * @param mixed  $value The data to save
     * @param int    $ttl   Time To Live, in seconds (default 60)
     *
     * @return bool Success or failure
     */
    public function save(string $key, $value, int $ttl = 60)
    {
        $key = static::validateKey($key, $this->prefix);

        if (! $this->config['raw']) {
            $value = [
                $value,
                time(),
                $ttl,
            ];
        }

        if ($this->memcached instanceof Memcached) {
            return $this->memcached->set($key, $value, $ttl);
        }

        if ($this->memcached instanceof Memcache) {
            return $this->memcached->set($key, $value, 0, $ttl);
        }

        // @phpstan-ignore-next-line
        return false;
    }

    //--------------------------------------------------------------------

    /**
     * Deletes a specific item from the cache store.
     *
     * @param string $key Cache item name
     *
     * @return bool Success or failure
     */
    public function delete(string $key)
    {
        $key = static::validateKey($key, $this->prefix);

        return $this->memcached->delete($key);
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
        throw new Exception('The deleteMatching method is not implemented for Memcached. You must select File, Redis or Predis handlers to use it.');
    }

    //--------------------------------------------------------------------

    /**
     * Performs atomic incrementation of a raw stored value.
     *
     * @param string $key    Cache ID
     * @param int    $offset Step/value to increase by
     *
     * @return false|int
     */
    public function increment(string $key, int $offset = 1)
    {
        if (! $this->config['raw']) {
            return false;
        }

        $key = static::validateKey($key, $this->prefix);

        // @phpstan-ignore-next-line
        return $this->memcached->increment($key, $offset, $offset, 60);
    }

    //--------------------------------------------------------------------

    /**
     * Performs atomic decrementation of a raw stored value.
     *
     * @param string $key    Cache ID
     * @param int    $offset Step/value to increase by
     *
     * @return false|int
     */
    public function decrement(string $key, int $offset = 1)
    {
        if (! $this->config['raw']) {
            return false;
        }

        $key = static::validateKey($key, $this->prefix);

        //FIXME: third parameter isn't other handler actions.
        // @phpstan-ignore-next-line
        return $this->memcached->decrement($key, $offset, $offset, 60);
    }

    //--------------------------------------------------------------------

    /**
     * Will delete all items in the entire cache.
     *
     * @return bool Success or failure
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
     * @return array|false
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
     * @return array|false|null
     *                          Returns null if the item does not exist, otherwise array<string, mixed>
     *                          with at least the 'expire' key for absolute epoch expiry (or null).
     *                          Some handlers may return false when an item does not exist, which is deprecated.
     */
    public function getMetaData(string $key)
    {
        $key    = static::validateKey($key, $this->prefix);
        $stored = $this->memcached->get($key);

        // if not an array, don't try to count for PHP7.2
        if (! is_array($stored) || count($stored) !== 3) {
            return false; // This will return null in a future release
        }

        [$data, $time, $limit] = $stored;

        return [
            'expire' => $limit > 0 ? $time + $limit : null,
            'mtime'  => $time,
            'data'   => $data,
        ];
    }

    //--------------------------------------------------------------------

    /**
     * Determines if the driver is supported on this system.
     *
     * @return bool
     */
    public function isSupported(): bool
    {
        return extension_loaded('memcached') || extension_loaded('memcache');
    }
}
