<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Cache\Handlers;

use CodeIgniter\Exceptions\CriticalError;
use CodeIgniter\I18n\Time;
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

    public function __construct(Cache $config)
    {
        $this->prefix = $config->prefix;

        $this->config = array_merge($this->config, $config->redis);
    }

    /**
     * Closes the connection to Redis if present.
     */
    public function __destruct()
    {
        if (isset($this->redis)) {
            $this->redis->close();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        $config = $this->config;

        $this->redis = new Redis();

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
            throw new CriticalError('Cache: RedisException occurred with message (' . $e->getMessage() . ').');
        }
    }

    /**
     * {@inheritDoc}
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

    /**
     * {@inheritDoc}
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
            $this->redis->expireAt($key, Time::now()->getTimestamp() + $ttl);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key)
    {
        $key = static::validateKey($key, $this->prefix);

        return $this->redis->del($key) === 1;
    }

    /**
     * {@inheritDoc}
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

    /**
     * {@inheritDoc}
     */
    public function increment(string $key, int $offset = 1)
    {
        $key = static::validateKey($key, $this->prefix);

        return $this->redis->hIncrBy($key, '__ci_value', $offset);
    }

    /**
     * {@inheritDoc}
     */
    public function decrement(string $key, int $offset = 1)
    {
        return $this->increment($key, -$offset);
    }

    /**
     * {@inheritDoc}
     */
    public function clean()
    {
        return $this->redis->flushDB();
    }

    /**
     * {@inheritDoc}
     */
    public function getCacheInfo()
    {
        return $this->redis->info();
    }

    /**
     * {@inheritDoc}
     */
    public function getMetaData(string $key)
    {
        $key   = static::validateKey($key, $this->prefix);
        $value = $this->get($key);

        if ($value !== null) {
            $time = Time::now()->getTimestamp();
            $ttl  = $this->redis->ttl($key);

            return [
                'expire' => $ttl > 0 ? $time + $ttl : null,
                'mtime'  => $time,
                'data'   => $value,
            ];
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function isSupported(): bool
    {
        return extension_loaded('redis');
    }
}
