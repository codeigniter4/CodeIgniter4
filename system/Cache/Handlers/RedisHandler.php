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
use Exception;
use Redis;
use RedisCluster;
use Throwable;

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
        'host'       => '127.0.0.1',
        'username'   => null,
        'password'   => null,
        'port'       => 6379,
        'timeout'    => 0,
        'database'   => 0,
        'isCluster'  => false,
        'persistent' => false,
    ];

    /**
     * Redis connection
     *
     * @var Redis|RedisCluster|null
     */
    protected $redis;

    public function __construct(Cache $config)
    {
        $this->prefix = $config->prefix;

        if (! empty($config)) {
            $this->config = array_merge($this->config, $config->redis);
        }
        if (null === $this->config['timeout']) {
            // As of php8.1, redis::connect doesn't allow a null timeout value.
            $this->config['timeout'] = 0;
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
     *
     * @throws Exception
     */
    private function connectToRedisServer(array $config)
    {
        $this->redis = new Redis();

        // Note:: If Redis is your primary cache choice, and it is "offline", every page load will end up been delayed by the timeout duration.
        // I feel like some sort of temporary flag should be set, to indicate that we think Redis is "offline", allowing us to bypass the timeout for a set period of time.

        if ($config['persistent']) {
            if (! $this->redis->pconnect($config['host'], ($config['host'][0] === '/' ? 0 : $config['port']), $config['timeout'])) {
                throw new Exception('Connection failed. Check your configuration.');
            }
        } elseif (! $this->redis->connect($config['host'], ($config['host'][0] === '/' ? 0 : $config['port']), $config['timeout'])) {
            throw new Exception('Connection failed. Check your configuration.');
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
            throw new Exception('Select database failed.');
        }
    }

    /**
     * Initiate connection to redis cluster
     *
     * @throws Exception
     */
    private function connectToRedisCluster(array $config)
    {
        // NOTE: You can connect to redis cluster via a singular endpoint, the redis extension will automatically run
        // 'CLUSTER NODES' to discover what other nodes are available. We're preserving the ability to list an array
        // of comma-separated servers here in case someone still wants to configure it that way.
        if (empty($hosts = str_getcsv($config['host']))) {
            throw new Exception("Must specify one or more comma-separated hosts to work with in 'host' configuration.");
        }
        $port = $config['port'] ?? 6379;
        if ($port > 0) {
            // User defined a port so let's make sure it's setup for all of the cluster hosts.
            foreach ($hosts as &$host) {
                if (! preg_match('/:\d+$/', $host)) {
                    // User didn't append :port to their cluster server name so let's do that for them.
                    $host .= ":{$config['port']}";
                }
            }
        }
        $timeout = (int) ($config['timeout'] ?? 0);

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
        $this->redis = new RedisCluster(null, $hosts, $timeout, $timeout, (bool) ($config['persistent']), $auth);
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
        $isCluster = (bool) ($config['isCluster']);

        try {
            // TODO: add TLS compatibility for both of these.
            if ($isCluster) {
                $this->connectToRedisCluster($config);
            } else {
                $this->connectToRedisServer($config);
            }
        } catch (Throwable $t) {
            // Either connection function can throw Exception, RedisException, or RedisClusterException. Catch any
            // possible error and log it.
            $redisMode = $isCluster ? 'Redis' : 'RedisCluster';
            $message   = "Cache: {$redisMode}: {$t->getMessage()}";
            log_message('error', $message);

            throw new CriticalError($message, 0, $t);
        }

        // Use the php serializer to handle everything we set/get from redis.
        $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);

        if (! empty($this->prefix)) {
            // Let the driver handle the prefix.
            $this->redis->setOption(Redis::OPT_PREFIX, $this->prefix);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key)
    {
        $key  = static::validateKey($key);
        $data = $this->redis->get($key);

        return $data === false ? null : $data;
    }

    /**
     * {@inheritDoc}
     */
    public function save(string $key, $value, int $ttl = 60)
    {
        $key = static::validateKey($key);

        return $ttl > 0 ? $this->redis->setEx($key, $ttl, $value) : $this->redis->set($key, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key)
    {
        $key = static::validateKey($key);

        return $this->redis->del($key) === 1;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteMatching(string $pattern)
    {
        $matchedKeys = [];

        if ($this->config['isCluster']) {
            // scan() is a directed-node command in redis cluster so we need to scan all nodes.
            foreach ($this->redis->_masters() as $m) {
                $iterator = null;

                do {
                    // @phpstan-ignore-next-line
                    $keys = $this->redis->scan($iterator, $m, $pattern);

                    // Redis may return empty results, so protect against that
                    if ($keys !== false) {
                        foreach ($keys as $key) {
                            $matchedKeys[] = $key;
                        }
                    }
                } while ($iterator > 0);
            }
        } else {
            $iterator = null;

            do {
                $keys = $this->redis->scan($iterator, $pattern);

                // Redis may return empty results, so protect against that
                if ($keys !== false) {
                    foreach ($keys as $key) {
                        $matchedKeys[] = $key;
                    }
                }
            } while ($iterator > 0);
        }

        return $this->redis->del($matchedKeys);
    }

    /**
     * {@inheritDoc}
     */
    public function increment(string $key, int $offset = 1)
    {
        $key = static::validateKey($key);

        return $this->redis->incrBy($key, $offset);
    }

    /**
     * {@inheritDoc}
     */
    public function decrement(string $key, int $offset = 1)
    {
        $key = static::validateKey($key);

        return $this->redis->incrBy($key, -$offset);
    }

    /**
     * {@inheritDoc}
     */
    public function clean()
    {
        if ($this->config['isCluster']) {
            foreach ($this->redis->_masters() as $m) {
                $this->redis->flushAll($m);
            }
        } else {
            $this->redis->flushAll();
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getCacheInfo()
    {
        $rtnVal = [];
        if ($this->config['isCluster']) {
            $nodeCount = 0;

            foreach ($this->redis->_masters() as $m) {
                $nodeCount++;

                foreach ($this->redis->info($m) as $key => $value) {
                    $rtnVal[$key][] = $value;
                }
            }
            // Summarize the keyspace counts for backwards compatibility with tests.
            // if we have other stats we need to combine, that can be done here as well.
            $db   = "db{$this->config['database']}";
            $sums = ['keys' => 0, 'expires' => 0, 'avg_ttl' => 0];

            foreach ($rtnVal[$db] as $ks) {
                foreach (explode(',', $ks) as $stat) {
                    [$key, $value] = explode('=', $stat);
                    $sums[$key] += $value;
                }
            }
            $sums['avg_ttl'] = (int) ($sums['avg_ttl'] / $nodeCount);
            $finalData       = [];

            foreach ($sums as $k => $v) {
                $finalData[] = "{$k}={$v}";
            }
            $rtnVal[$db] = implode(',', $finalData);
        } else {
            $rtnVal = $this->redis->info();
        }

        return $rtnVal;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetaData(string $key)
    {
        $key    = static::validateKey($key);
        $rtnVal = null;
        if (null !== ($value = $this->get($key))) {
            $time   = time();
            $ttl    = $this->redis->ttl($key);
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
