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
use Redis;
use RedisException;

/**
 * Redis cache handler
 */
class RedisHandler extends BaseHandler
{
    /**
     * Default config
     *
     * @var array
     */
    protected $config = [
        'host'     => '127.0.0.1',
        'password' => null,
        'port'     => 6379,
        'timeout'  => 0,
        'database' => 0,
    ];

    /**
     * Redis connection
     *
     * @var Redis
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

        if (! empty($config)) {
            $this->config = array_merge($this->config, $config->redis);
        }
    }

    /**
     * Class destructor
     *
     * Closes the connection to Redis if present.
     */
    public function __destruct()
    {
        if (isset($this->redis)) {
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

        $this->redis = new Redis();

        // Try to connect to Redis, if an issue occurs throw a CriticalError exception,
        // so that the CacheFactory can attempt to initiate the next cache handler.
        try {
            // Note:: If Redis is your primary cache choice, and it is "offline", every page load will end up been delayed by the timeout duration.
            // I feel like some sort of temporary flag should be set, to indicate that we think Redis is "offline", allowing us to bypass the timeout for a set period of time.

            if (! $this->redis->connect($config['host'], ($config['host'][0] === '/' ? 0 : $config['port']), $config['timeout'])) {
                // Note:: I'm unsure if log_message() is necessary, however I'm not 100% comfortable removing it.
                log_message('error', 'Cache: Redis connection failed. Check your configuration.');

                throw new CriticalError('Cache: Redis connection failed. Check your configuration.');
            }

            if (isset($config['password']) && ! $this->redis->auth($config['password'])) {
                log_message('error', 'Cache: Redis authentication failed.');

                throw new CriticalError('Cache: Redis authentication failed.');
            }

            if (isset($config['database']) && ! $this->redis->select($config['database'])) {
                log_message('error', 'Cache: Redis select database failed.');

                throw new CriticalError('Cache: Redis select database failed.');
            }
        } catch (RedisException $e) {
            // $this->redis->connect() can sometimes throw a RedisException.
            // We need to convert the exception into a CriticalError exception and throw it.
            throw new CriticalError('Cache: RedisException occurred with message (' . $e->getMessage() . ').');
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
        $key  = static::validateKey($key, $this->prefix);
        $data = $this->redis->hMGet($key, ['__ci_type', '__ci_value']);

        if (! isset($data['__ci_type'], $data['__ci_value']) || $data['__ci_value'] === false) {
            return null;
        }

        switch ($data['__ci_type']) {
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
     * @param string $key   Cache item name
     * @param mixed  $value The data to save
     * @param int    $ttl   Time To Live, in seconds (default 60)
     *
     * @return bool Success or failure
     */
    public function save(string $key, $value, int $ttl = 60)
    {
        $key = static::validateKey($key, $this->prefix);

        switch ($dataType = gettype($value)) {
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

        if (! $this->redis->hMSet($key, ['__ci_type' => $dataType, '__ci_value' => $value])) {
            return false;
        }

        if ($ttl) {
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
     * @return bool Success or failure
     */
    public function delete(string $key)
    {
        $key = static::validateKey($key, $this->prefix);

        return $this->redis->del($key) === 1;
    }

    //--------------------------------------------------------------------

    /**
     * Deletes items from the cache store matching a given pattern.
     *
     * @param string $pattern Cache items glob-style pattern
     *
     * @return int The number of deleted items
     */
    public function deleteMatching(string $pattern)
    {
        $matchedKeys = [];
        $iterator    = null;

        do {
            // Scan for some keys
            $keys = $this->redis->scan($iterator, $pattern);

            // Redis may return empty results, so protect against that
            if ($keys !== false) {
                foreach ($keys as $key) {
                    $matchedKeys[] = $key;
                }
            }
        } while ($iterator > 0);

        return $this->redis->del($matchedKeys);
    }

    //--------------------------------------------------------------------

    /**
     * Performs atomic incrementation of a raw stored value.
     *
     * @param string $key    Cache ID
     * @param int    $offset Step/value to increase by
     *
     * @return int
     */
    public function increment(string $key, int $offset = 1)
    {
        $key = static::validateKey($key, $this->prefix);

        return $this->redis->hIncrBy($key, 'data', $offset);
    }

    //--------------------------------------------------------------------

    /**
     * Performs atomic decrementation of a raw stored value.
     *
     * @param string $key    Cache ID
     * @param int    $offset Step/value to increase by
     *
     * @return int
     */
    public function decrement(string $key, int $offset = 1)
    {
        $key = static::validateKey($key, $this->prefix);

        return $this->redis->hIncrBy($key, 'data', -$offset);
    }

    //--------------------------------------------------------------------

    /**
     * Will delete all items in the entire cache.
     *
     * @return bool Success or failure
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
     * @return array|null
     *                    Returns null if the item does not exist, otherwise array<string, mixed>
     *                    with at least the 'expire' key for absolute epoch expiry (or null).
     */
    public function getMetaData(string $key)
    {
        $key   = static::validateKey($key, $this->prefix);
        $value = $this->get($key);

        if ($value !== null) {
            $time = time();
            $ttl  = $this->redis->ttl($key);

            return [
                'expire' => $ttl > 0 ? time() + $ttl : null,
                'mtime'  => $time,
                'data'   => $value,
            ];
        }

        return null;
    }

    //--------------------------------------------------------------------

    /**
     * Determines if the driver is supported on this system.
     *
     * @return bool
     */
    public function isSupported(): bool
    {
        return extension_loaded('redis');
    }
}
