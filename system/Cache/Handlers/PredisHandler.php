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
use Predis\Client;
use Predis\Collection\Iterator\Keyspace;
use Throwable;

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
     * Predis connection
     *
     * @var Client|null
     */
    protected $redis;

    public function __construct(Cache $config)
    {
        $this->prefix = $config->prefix;

        $this->config = array_merge($this->config, $config->redis);
    }

    /**
     * Initiate connection to individual redis server
     */
    private function connectToRedisServer(array $config)
    {
        $this->redis = new Client($config, ['prefix' => $this->prefix]);
        $this->redis->time();
    }

    /**
     * Initiate connection to redis cluster
     *
     * @throws Exception
     */
    private function connectToRedisCluster(array $config)
    {
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
        $timeout    = $config['timeout'] ?? 0;
        $parameters = [
            'read_write_timeout' => $timeout,
            'timeout'            => $timeout,
            'persistent'         => (bool) ($config['persistent']),
            'username'           => $config['username'],
            'password'           => $config['password'],
            'prefix'             => $this->prefix,
        ];
        // For cluster mode, the first argument is the list of servers to connect to.
        // Use server-side clustering like phpredis RedisCluster does.
        $this->redis = new Client($hosts, ['cluster' => 'redis', 'parameters' => $parameters]);
        // ping(), time(), etc. are not supported for predis cluster mode, so try to grab a key to check connectivity.
        $this->redis->get('testkey');
    }

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        $config = $this->config;

        try {
            // User must specify whether they are connecting to a cluster or not.
            if ($config['isCluster']) {
                $this->connectToRedisCluster($config);
            } else {
                $this->connectToRedisServer($config);
            }

            // NOTE: Using php's serializer automatically, like we do for phpredis, requires installation of phpiredis,
            // which in turn requires installation of another package. Rather than incurring the bloat of all of these
            // packages, we'll use serialize/userialize for our get/set functions.
        } catch (Throwable $t) {
            $mode    = $config['isCluster'] ? 'server' : 'cluster';
            $message = "Cache: Predis {$mode} connection refused ('{$t->getMessage()}').";
            log_message('error', $message);

            throw new CriticalError($message, 0, $t);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key)
    {
        $key = static::validateKey($key);

        if (! (null === ($data = $this->redis->get($key)))) {
            $data = unserialize($data);
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function save(string $key, $value, int $ttl = 60)
    {
        $key = static::validateKey($key);

        $value = serialize($value);

        if ($ttl > 0) {
            $rtnVal = $this->redis->setex($key, $ttl, $value);
        } else {
            $rtnVal = $this->redis->set($key, $value);
        }
        if ($rtnVal instanceof \Predis\Response\Status) {
            $rtnVal = $rtnVal->getPayload() === 'OK';
        }

        return $rtnVal;
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

        foreach (new Keyspace($this->redis, $pattern) as $key) {
            $matchedKeys[] = $key;
        }

        return $this->redis->del($matchedKeys);
    }

    /**
     * {@inheritDoc}
     */
    public function increment(string $key, int $offset = 1)
    {
        $key = static::validateKey($key);

        return $this->redis->hincrby($key, 'data', $offset);
    }

    /**
     * {@inheritDoc}
     */
    public function decrement(string $key, int $offset = 1)
    {
        $key = static::validateKey($key);

        return $this->redis->hincrby($key, 'data', -$offset);
    }

    /**
     * {@inheritDoc}
     */
    public function clean()
    {
        return $this->redis->flushdb()->getPayload() === 'OK';
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
        $key    = static::validateKey($key);
        $rtnVal = null;

        if (null !== ($data = $this->redis->get($key))) {
            $data = unserialize($data);
            $time = time();
            $ttl  = $this->redis->ttl($key);

            $rtnVal = [
                'expire' => $ttl > 0 ? time() + $ttl : null,
                'mtime'  => $time,
                'data'   => $data,
            ];
        }

        return $rtnVal;
    }

    /**
     * {@inheritDoc}
     */
    public function isSupported(): bool
    {
        return class_exists('Predis\Client');
    }
}
