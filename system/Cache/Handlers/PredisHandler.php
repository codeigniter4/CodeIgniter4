<?php

declare(strict_types=1);

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
use Exception;
use Predis\Client;
use Predis\Collection\Iterator\Keyspace;
use Predis\Response\Status;

/**
 * Predis cache handler
 *
 * @see \CodeIgniter\Cache\Handlers\PredisHandlerTest
 */
class PredisHandler extends BaseHandler
{
    /**
     * Default config
     *
     * @var array{
     *   scheme: string,
     *   host: string,
     *   password: string|null,
     *   port: int,
     *   async: bool,
     *   persistent: bool,
     *   timeout: int,
     *   sentinels: list<string>,
     *   service: string,
     * }
     */
    protected $config = [
        'scheme'     => 'tcp',
        'host'       => '127.0.0.1',
        'password'   => null,
        'port'       => 6379,
        'async'      => false,
        'persistent' => false,
        'timeout'    => 0,
        'sentinels'  => [],
        'service'    => '',
    ];

    /**
     * Predis connection
     *
     * @var Client
     */
    protected $redis;

    /**
     * Note: Use `CacheFactory::getHandler()` to instantiate.
     */
    public function __construct(Cache $config)
    {
        $this->prefix = $config->prefix;

        $this->config = array_merge($this->config, $config->redis);
    }

    /**
     * Factory method for creating Predis Client instances.
     * Override in tests to mock Client.
     */
    protected function createPredisClient(array $connection, array $options = []): Client
    {
        return new Client($connection, $options);
    }

    public function initialize(): void
    {
        try {
            $config = $this->config;

            if ($config['sentinels'] !== [] && $config['service'] !== '') {
                $this->initializeSentinel($config);

                return;
            }

            unset($config['sentinels'], $config['service']);

            $this->redis = $this->createPredisClient($config, ['prefix' => $this->prefix]);
            $this->redis->time();
        } catch (Exception $e) {
            throw new CriticalError('Cache: Predis connection refused (' . $e->getMessage() . ').', $e->getCode(), $e);
        }
    }

    /**
     * Initializes a connection via Redis Sentinel.
     *
     * Predis v3.x does not have built-in sentinel support,
     * so we manually discover the master via the Sentinel protocol.
     *
     * @param array{
     *   sentinels: list<string>,
     *   service: string,
     *   timeout: int,
     *   password?: string|null,
     *   database?: int,
     *   prefix?: string,
     * } $config
     */
    protected function initializeSentinel(array $config): void
    {
        $sentinels = $config['sentinels'];
        $service   = $config['service'];
        $timeout   = $config['timeout'] ?? 0;

        $masterHost = null;
        $masterPort = null;

        foreach ($sentinels as $sentinel) {
            $parts = parse_url($sentinel);
            $sentinelHost = $parts['host'] ?? '127.0.0.1';
            $sentinelPort = $parts['port'] ?? 26379;
            $sentinelScheme = $parts['scheme'] ?? 'tcp';

            try {
                $sentinelClient = $this->createPredisClient([
                    'scheme'  => $sentinelScheme,
                    'host'    => $sentinelHost,
                    'port'    => $sentinelPort,
                    'timeout' => $timeout,
                ]);

                if (isset($config['password']) && $config['password'] !== null) {
                    $sentinelClient->auth($config['password']);
                }

                $addr = $sentinelClient->rawCommand('SENTINEL', 'get-master-addr-by-name', $service);

                if (is_array($addr) && count($addr) >= 2) {
                    $masterHost = $addr[0];
                    $masterPort = (int) $addr[1];
                }

                $sentinelClient->disconnect();
                unset($sentinelClient);

                if ($masterHost !== null) {
                    break;
                }
            } catch (Exception) {
                continue;
            }
        }

        if ($masterHost === null) {
            throw new CriticalError('Cache: Redis Sentinel could not find a master for service "' . $service . '".');
        }

        $config['scheme'] = 'tcp';
        $config['host']   = $masterHost;
        $config['port']   = $masterPort;
        unset($config['sentinels'], $config['service'], $config['async'], $config['persistent']);

        $this->redis = $this->createPredisClient($config, ['prefix' => $this->prefix]);
        $this->redis->time();
    }

    public function get(string $key): mixed
    {
        $key = static::validateKey($key);

        $data = array_combine(
            ['__ci_type', '__ci_value'],
            $this->redis->hmget($key, ['__ci_type', '__ci_value']),
        );

        if (! isset($data['__ci_type'], $data['__ci_value']) || $data['__ci_value'] === false) {
            return null;
        }

        return match ($data['__ci_type']) {
            'array', 'object'                                => unserialize($data['__ci_value']),
            'boolean', 'integer', 'double', 'string', 'NULL' => settype($data['__ci_value'], $data['__ci_type']) ? $data['__ci_value'] : null,
            default                                          => null,
        };
    }

    public function save(string $key, mixed $value, int $ttl = 60): bool
    {
        $key = static::validateKey($key);

        switch ($dataType = gettype($value)) {
            case 'array':
            case 'object':
                $value = serialize($value);
                break;

            case 'boolean':
            case 'integer':
            case 'double':
            case 'string':
            case 'NULL':
                break;

            case 'resource':
            default:
                return false;
        }

        if (! $this->redis->hmset($key, ['__ci_type' => $dataType, '__ci_value' => $value]) instanceof Status) {
            return false;
        }

        if ($ttl !== 0) {
            $this->redis->expireat($key, Time::now()->getTimestamp() + $ttl);
        }

        return true;
    }

    public function delete(string $key): bool
    {
        $key = static::validateKey($key);

        return $this->redis->del($key) === 1;
    }

    public function deleteMatching(string $pattern): int
    {
        $matchedKeys = [];

        foreach (new Keyspace($this->redis, $pattern) as $key) {
            $matchedKeys[] = $key;
        }

        if ($matchedKeys === []) {
            return 0;
        }

        return $this->redis->del($matchedKeys);
    }

    public function increment(string $key, int $offset = 1): int
    {
        $key = static::validateKey($key);

        return $this->redis->hincrby($key, 'data', $offset);
    }

    public function decrement(string $key, int $offset = 1): int
    {
        $key = static::validateKey($key);

        return $this->redis->hincrby($key, 'data', -$offset);
    }

    public function clean(): bool
    {
        return $this->redis->flushdb()->getPayload() === 'OK';
    }

    public function getCacheInfo(): array
    {
        return $this->redis->info();
    }

    public function getMetaData(string $key): ?array
    {
        $key = static::validateKey($key);

        $data = array_combine(['__ci_value'], $this->redis->hmget($key, ['__ci_value']));

        if (isset($data['__ci_value']) && $data['__ci_value'] !== false) {
            $time = Time::now()->getTimestamp();
            $ttl  = $this->redis->ttl($key);

            return [
                'expire' => $ttl > 0 ? $time + $ttl : null,
                'mtime'  => $time,
                'data'   => $data['__ci_value'],
            ];
        }

        return null;
    }

    public function isSupported(): bool
    {
        return class_exists(Client::class);
    }

    public function ping(): bool
    {
        try {
            $result = $this->redis->ping();

            if (is_object($result)) {
                return $result->getPayload() === 'PONG';
            }

            return $result === 'PONG';
        } catch (Exception) {
            return false;
        }
    }

    public function reconnect(): bool
    {
        try {
            $this->redis->disconnect();
        } catch (Exception) {
            // Connection already dead, that's fine
        }

        try {
            $this->initialize();

            return true;
        } catch (CriticalError $e) {
            log_message('error', 'Cache: Predis reconnection failed: ' . $e->getMessage());

            return false;
        }
    }
}
