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

use CodeIgniter\Cache\LockStoreInterface;
use CodeIgniter\Cache\LockStoreProviderInterface;
use CodeIgniter\Cache\LockStores\PredisLockStore;
use CodeIgniter\Exceptions\CriticalError;
use CodeIgniter\I18n\Time;
use Config\Cache;
use Exception;
use Predis\Client;
use Predis\Collection\Iterator\Keyspace;
use Predis\Command\RawCommand;
use Predis\Response\Status;
use RuntimeException;

/**
 * Predis cache handler
 *
 * @see \CodeIgniter\Cache\Handlers\PredisHandlerTest
 */
class PredisHandler extends BaseHandler implements LockStoreProviderInterface
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
     *   sentinel?: array{
     *     service?: string,
     *     nodes?: list<array{scheme?: string, host: string, port?: int}>
     *   }
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
        'sentinel'   => [],
    ];

    /**
     * Predis connection
     *
     * @var Client
     */
    protected $redis;

    private ?LockStoreInterface $lockStore = null;

    /**
     * Note: Use `CacheFactory::getHandler()` to instantiate.
     */
    public function __construct(Cache $config)
    {
        $this->prefix = $config->prefix;

        $this->config = array_merge($this->config, $config->redis);
    }

    public function initialize(): void
    {
        try {
            // When a Sentinel cluster is configured, discover the current master
            // address first and connect to it directly (Predis has no built-in
            // Sentinel handling on this client). Otherwise connect to the single
            // configured host.
            if (($this->config['sentinel']['nodes'] ?? []) !== []) {
                [$host, $port] = $this->discoverMasterFromSentinel();

                $config         = $this->config;
                $config['host'] = $host;
                $config['port'] = $port;

                $this->redis = new Client($config, ['prefix' => $this->prefix]);
            } else {
                $this->redis = new Client($this->config, ['prefix' => $this->prefix]);
            }

            $this->lockStore = null;
            $this->redis->time();
        } catch (RuntimeException $e) {
            throw new CriticalError('Cache: ' . $e->getMessage(), $e->getCode(), $e);
        } catch (Exception $e) {
            throw new CriticalError('Cache: Predis connection refused (' . $e->getMessage() . ').', $e->getCode(), $e);
        }
    }

    /**
     * Queries the configured Sentinel nodes for the current master address.
     *
     * Each node is tried in order; the first one that answers wins.
     *
     * @return array{0: string, 1: int} The master host and port.
     *
     * @throws RuntimeException When no Sentinel node can discover the master.
     */
    private function discoverMasterFromSentinel(): array
    {
        $service = $this->config['sentinel']['service'];

        foreach ($this->config['sentinel']['nodes'] as $node) {
            try {
                $sentinel = new Client([
                    'scheme'  => $node['scheme'] ?? 'tcp',
                    'host'    => $node['host'],
                    'port'    => $node['port'] ?? 26379,
                    'timeout' => (float) ($this->config['sentinel']['timeout'] ?? 0),
                ]);

                $result = $sentinel->executeCommand(
                    RawCommand::create('SENTINEL', 'get-master-addr-by-name', $service),
                );
                $sentinel->disconnect();

                if (is_array($result) && isset($result[0], $result[1]) && is_string($result[0])) {
                    return [(string) $result[0], (int) $result[1]];
                }
            } catch (Exception) {
                // Node unreachable or command failed; try the next one.
            }
        }

        throw new RuntimeException(sprintf('Redis Sentinel unable to discover master "%s".', $service));
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

    public function lockStore(): LockStoreInterface
    {
        // Predis applies the configured prefix at the client level.
        return $this->lockStore ??= new PredisLockStore($this->redis);
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
