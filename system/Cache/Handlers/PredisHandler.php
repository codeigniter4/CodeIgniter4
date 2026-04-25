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
use CodeIgniter\Lock\LockStoreInterface;
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
class PredisHandler extends BaseHandler implements LockStoreInterface
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
     *   timeout: int
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

    public function initialize(): void
    {
        try {
            $this->redis = new Client($this->config, ['prefix' => $this->prefix]);
            $this->redis->time();
        } catch (Exception $e) {
            throw new CriticalError('Cache: Predis connection refused (' . $e->getMessage() . ').', $e->getCode(), $e);
        }
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

    public function acquireLock(string $key, string $owner, int $ttl): bool
    {
        $key    = static::validateKey($key);
        $result = $this->redis->set($key, $owner, 'EX', $ttl, 'NX');

        return $result instanceof Status && $result->getPayload() === 'OK';
    }

    public function releaseLock(string $key, string $owner): bool
    {
        $key = static::validateKey($key);

        $script = <<<'LUA'
            if redis.call("get", KEYS[1]) == ARGV[1] then
                return redis.call("del", KEYS[1])
            end

            return 0
            LUA;

        return $this->redis->eval($script, 1, $key, $owner) === 1;
    }

    public function forceReleaseLock(string $key): bool
    {
        $key     = static::validateKey($key);
        $deleted = $this->redis->del($key);

        return is_int($deleted) && $deleted >= 0;
    }

    public function refreshLock(string $key, string $owner, int $ttl): bool
    {
        $key = static::validateKey($key);

        $script = <<<'LUA'
            if redis.call("get", KEYS[1]) == ARGV[1] then
                return redis.call("expire", KEYS[1], ARGV[2])
            end

            return 0
            LUA;

        return $this->redis->eval($script, 1, $key, $owner, (string) $ttl) === 1;
    }

    public function getLockOwner(string $key): ?string
    {
        $key   = static::validateKey($key);
        $owner = $this->redis->get($key);

        return is_string($owner) ? $owner : null;
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
