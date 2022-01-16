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
use Predis\Connection\StreamConnection;
use Predis\Response\Error;
use Predis\Response\Status;
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
            $data = $this->tryUnserialize($data);
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

        $rtnVal = $ttl > 0 ? $this->redis->setex($key, $ttl, $value) : $this->redis->set($key, $value);

        if ($rtnVal instanceof Status) {
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
        $rtnVal      = 0;

        if ($this->config['isCluster']) {
            // @phpstan-ignore-next-line
            foreach ($this->redis->getConnection() as $c) {
                $matchedKeys = [];
                // Predis doesn't natively support 'Keyspace'/SCAN, so we have to do it ourselves.
                $i = 0;

                do {
                    $cmd = $this->redis->createCommand('SCAN', [(string) $i, 'MATCH', $pattern]);
                    if ($result = $c->executeCommand($cmd)) {
                        $i           = (int) $result[0];
                        $matchedKeys = array_merge($matchedKeys, $result[1]);
                    } else {
                        $i = 0;
                    }
                } while ($i !== 0);

                if ($matchedKeys) {
                    // Predis can't run del() for multiple keys in cluster mode, since they can map to different nodes
                    // or slots. We also can't do a MULTI since it expects everything to hash to the same slot. So we
                    // will run atomic deletes to get around those errors. Not optimal at all, but our hands are tied.
                    foreach ($matchedKeys as $k) {
                        $cmd    = $this->redis->createCommand('DEL', [$k]);
                        $result = $c->executeCommand($cmd);
                        if ($result instanceof Error) {
                            throw new Exception("Predis cluster: Could not delete '{$k}': {$result->getMessage()}");
                        }
                        $rtnVal++;
                    }
                }
            }
        } else {
            foreach (new Keyspace($this->redis, $pattern) as $key) {
                $matchedKeys[] = $key;
            }
            $rtnVal = $this->redis->del($matchedKeys);
        }

        return $rtnVal;
    }

    /**
     * {@inheritDoc}
     */
    public function increment(string $key, int $offset = 1)
    {
        $key = static::validateKey($key);

        return $this->redis->incrby($key, $offset);
    }

    /**
     * {@inheritDoc}
     */
    public function decrement(string $key, int $offset = 1)
    {
        $key = static::validateKey($key);

        return $this->redis->incrby($key, -$offset);
    }

    /**
     * {@inheritDoc}
     */
    public function clean()
    {
        $rtnVal = true;
        if ($this->config['isCluster']) {
            $cmd = $this->redis->createCommand('flushall');
            // @phpstan-ignore-next-line
            foreach ($this->redis->getConnection() as $c) {
                $rtnVal = $rtnVal && $c->executeCommand($cmd)->getPayload() === 'OK';
            }
        } else {
            $rtnVal = $this->redis->flushdb()->getPayload() === 'OK';
        }

        return $rtnVal;
    }

    /**
     * {@inheritDoc}
     */
    public function getCacheInfo()
    {
        if ($this->config['isCluster']) {
            // predis blocks the 'info()' function with a 'NotSupportedException', so let's build out the data manually
            $rtnVal = [];
            // Create a raw command to execute
            $cmd = $this->redis->createCommand('info');
            // @phpstan-ignore-next-line
            foreach ($this->redis->getConnection() as $c) {
                /** @var StreamConnection $c */
                $info = $c->executeCommand($cmd);
                // Bust up the result by newline
                $info = explode("\n", $info);
                $ptr  = &$rtnVal;

                foreach ($info as $i) {
                    if (empty($i = trim($i))) {
                        continue;
                    }
                    // Grab section header
                    if (preg_match('/^#\s(\w+)/', $i, $matches)) {
                        $ptr = &$rtnVal[$matches[1]];

                        continue;
                    }
                    // Put key/value pairs in each section.
                    [$key, $value] = explode(':', $i);
                    $ptr[$key][]   = $value;
                }
            }
            $sums = ['keys' => 0, 'expires' => 0, 'avg_ttl' => 0];
            $db   = "db{$this->config['database']}";
            // @phpstan-ignore-next-line
            if (isset($rtnVal['Keyspace'][$db])) {
                $nodeCnt = count($rtnVal['Keyspace'][$db]);
                // Summarize all of the keyspace stats into a single line for backwards compatibility with tests.
                // If other stats are needed we could do that here as well.
                foreach ($rtnVal['Keyspace'][$db] as $ks) {
                    foreach (explode(',', $ks) as $stat) {
                        [$key, $value] = explode('=', $stat);
                        $sums[$key] += $value;
                    }
                }
                $rtnVal['Keyspace']['db0']            = $sums;
                $avgttl                               = $rtnVal['Keyspace']['db0']['avg_ttl'];
                $rtnVal['Keyspace']['db0']['avg_ttl'] = (int) ($avgttl / $nodeCnt);
                // Tests expect string values, so let's handle that.
                foreach ($rtnVal['Keyspace']['db0'] as &$value) {
                    $value = (string) $value;
                }
            }
        } else {
            $rtnVal = $this->redis->info();
        }

        return $rtnVal;
    }

    /**
     * Attempt to unserialize data. Sometimes data can't be unserialized successfully (e.g., data set by incrby), in
     * those cases, just return the raw data.
     *
     * @param string $data
     *
     * @return mixed
     */
    private function tryUnserialize($data)
    {
        try {
            $data = unserialize($data);
        } catch (\Throwable $t) {
            // nothing to do about this, $data was unmodified
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetaData(string $key)
    {
        $key    = static::validateKey($key);
        $rtnVal = null;

        if (null !== ($data = $this->redis->get($key))) {
            $data = $this->tryUnserialize($data);
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
