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
use Config\Cache;
use Redis;
use RedisCluster;

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
        'username' => null,
        'password' => null,
        'port'     => 6379,
        'timeout'  => 0,
        'database' => 0,
        'isCluster' => false,
        'persistent' => false,
    ];

    /**
     * Redis connection
     *
     * @var Redis|RedisCluster
     */
    protected $redis;

    public function __construct(Cache $config)
    {
        $this->prefix = $config->prefix;

        if (! empty($config)) {
            $this->config = array_merge($this->config, $config->redis);
        }
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
     * Initiate connection to redis server
     * @param array $config
     * @throws \Exception
     */
    private function _connectToRedisServer(array $config)
    {
        $this->redis = new Redis();

        // Note:: If Redis is your primary cache choice, and it is "offline", every page load will end up been delayed by the timeout duration.
        // I feel like some sort of temporary flag should be set, to indicate that we think Redis is "offline", allowing us to bypass the timeout for a set period of time.

        if($config['persistent']) {
            if (! $this->redis->pconnect($config['host'], ($config['host'][0] === '/' ? 0 : $config['port']), $config['timeout'])) {
                throw new \Exception('Connection failed. Check your configuration.');
            }
        } else {
            if (! $this->redis->connect($config['host'], ($config['host'][0] === '/' ? 0 : $config['port']), $config['timeout'])) {
                throw new \Exception('Connection failed. Check your configuration.');
            }
        }

        if (isset($config['password'])) {
            if (isset($config['username'])) {
                // Redis 6+ accepts username/password (see Redis ACL)
                $auth = [
                    'user' => $config['username'],
                    'pass' => $config['password'],
                ];
            } else {
                $auth = $config['password'];
            }
            // auth() throws an exception if authentication was unsuccessful, so checking for a bad return doesn't help
            $this->redis->auth($auth);
        }

        // Don't select a database if it's set to false
        if ($config['database'] !== false && ! $this->redis->select($config['database'])) {
            throw new \Exception('Select database failed.');
        }
    }

    /**
     * Initiate connection to redis cluster
     * @param array $config
     * @throws \Exception
     */
    private function _connectToRedisCluster(array $config)
    {
        // NOTE: You can connect to redis cluster via a singular endpoint, the redis extension will automatically run
        // 'CLUSTER NODES' to discover what other nodes are available. We're preserving the ability to list an array
        // of comma-separated servers here in case someone still wants to configure it that way.
        if (empty($hosts = str_getcsv($config['host']))) {
            throw new \Exception("Must specify one or more comma-separated hosts to work with in 'host' configuration.");
        }
        $port = $config['port'] ?? 6379;
        if($port > 0) {
            // User defined a port so let's make sure it's setup for all of the cluster hosts.
            foreach($hosts as &$host) {
                if(!preg_match('/:\d+$/',$host)) {
                    // User didn't append :port to their cluster server name so let's do that for them.
                    $host .= ":{$config['port']}";
                }
            }
        }
        $timeout = intval($config['timeout'] ?? 0);

        $auth = null;
        if (isset($config['password'])) {
            if (isset($config['username'])) {
                // Redis 6+ accepts username/password (see Redis ACL)
                $auth = [
                    'user' => $config['username'],
                    'pass' => $config['password'],
                ];
            } else {
                $auth = $config['password'];
            }
        }

        /*
         * This instantiates the cluster connection
         * Note: RedisCluster supports a first argument of $name which is a 'seed name', which serves as a pointer
         * to a named cluster with an array of 'seeds'/hosts, which is defined in php.ini. Predis doesn't support this.
         * My intent is to only offer configuration for RedisCluster/Predis for features that are shared for both.
         * Since $name isn't a shared feature, I'm not setting it up here, although it wouldn't be hard to have a new
         * 'clusterName' parameter in $config and be able to plug that in here.
        */
        $this->redis = new RedisCluster(null, $hosts, $timeout, $timeout, boolval($config['persistent']), $auth);
        // Prefer failover to an available replica.
        // @phpstan-ignore-next-line
        $this->redis->setOption(RedisCluster::OPT_SLAVE_FAILOVER, RedisCluster::FAILOVER_DISTRIBUTE_SLAVES);
    }

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        $config = $this->config;

        // User must specify whether they are connecting to a cluster or not.
        $isCluster = boolval($config['isCluster']);

        try {
            // TODO: add TLS compatibility for both of these.
            if($isCluster) {
                $this->_connectToRedisCluster($config);
            } else {
                $this->_connectToRedisServer($config);
            }
        } catch (\Throwable $e) {
            // Either connection function can throw \Exception, \RedisException, or \RedisClusterException. Catch any
            // possible error and log it.
            $redisMode = $isCluster ? 'Redis' : 'RedisCluster';
            $message = "Cache: {$redisMode}: ".$e->getMessage();
            log_message('error', $message);
            throw new CriticalError($message);
        }

        // Use the php serializer to handle everything we set/get from redis.
        // @phpstan-ignore-next-line
        $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key)
    {
        $key  = static::validateKey($key, $this->prefix);
        $data = $this->redis->get($key);
        return $data === false ? null : $data;
    }

    /**
     * {@inheritDoc}
     */
    public function save(string $key, $value, int $ttl = 60)
    {
        $key = static::validateKey($key, $this->prefix);

        return $this->redis->setEx($key, $ttl, $value);
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

        return $this->redis->hIncrBy($key, 'data', $offset);
    }

    /**
     * {@inheritDoc}
     */
    public function decrement(string $key, int $offset = 1)
    {
        $key = static::validateKey($key, $this->prefix);

        return $this->redis->hIncrBy($key, 'data', -$offset);
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
        $key  = static::validateKey($key, $this->prefix);
        $rtnVal = null;
        if(($value = $this->get($key)) !== false) {
            $time = time();
            $ttl  = $this->redis->ttl($key);
            $rtnVal = [
                'expire' => $ttl > 0 ? time() + $ttl : null,
                'mtime'  => $time,
                'data'   => $value,
            ];
        }

        return $rtnVal;
    }

    /**
     * {@inheritDoc}
     */
    public function isSupported(): bool
    {
        return extension_loaded('redis');
    }
}
