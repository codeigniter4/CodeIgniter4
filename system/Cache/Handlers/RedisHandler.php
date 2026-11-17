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
use Redis;
use RedisException;

/**
 * Redis cache handler
 */
class RedisHandler extends BaseHandler
{
    /**
     * Lua script to execute HMSET and EXPIREAT in a single round trip.
     *
     * Both commands run through pcall and their error reply is returned as-is,
     * so a failing HMSET never runs EXPIREAT and the original error message and
     * type are preserved. phpredis exposes the message through
     * Redis::getLastError(); save() rethrows a permission error as
     * RedisException, as the sequential implementation did, and keeps reporting
     * any other error (e.g. WRONGTYPE) as false.
     *
     * If HMSET succeeds and EXPIREAT is rejected, the hash is stored without a
     * TTL, exactly as with the previous sequential implementation (Redis does
     * not roll back script effects).
     */
    private const SAVE_SCRIPT = <<<'LUA'
        local res = redis.pcall('HMSET', KEYS[1], '__ci_type', ARGV[2], '__ci_value', ARGV[3])
        if type(res) == 'table' and res.err then
            return res
        end
        local exp = redis.pcall('EXPIREAT', KEYS[1], ARGV[1])
        if type(exp) == 'table' and exp.err then
            return exp
        end
        return 1
        LUA;

    /**
     * Default config
     *
     * @var array{
     *   host: string,
     *   password: string|null,
     *   port: int,
     *   timeout: int,
     *   persistent: bool,
     *   database: int,
     * }
     */
    protected $config = [
        'host'       => '127.0.0.1',
        'password'   => null,
        'port'       => 6379,
        'timeout'    => 0,
        'persistent' => false,
        'database'   => 0,
    ];

    /**
     * Redis connection
     *
     * @var Redis|null
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
        $config = $this->config;

        $this->redis = new Redis();

        try {
            $funcConnection = isset($config['persistent']) && $config['persistent'] ? 'pconnect' : 'connect';

            // Note:: If Redis is your primary cache choice, and it is "offline", every page load will end up been delayed by the timeout duration.
            // I feel like some sort of temporary flag should be set, to indicate that we think Redis is "offline", allowing us to bypass the timeout for a set period of time.
            if (! $this->redis->{$funcConnection}($config['host'], ($config['host'][0] === '/' ? 0 : $config['port']), $config['timeout'])) {
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
            throw new CriticalError('Cache: RedisException occurred with message (' . $e->getMessage() . ').', $e->getCode(), $e);
        }
    }

    public function get(string $key): mixed
    {
        $key  = static::validateKey($key, $this->prefix);
        $data = $this->redis->hMget($key, ['__ci_type', '__ci_value']);

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
        $key = static::validateKey($key, $this->prefix);

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

        $fields = ['__ci_type' => $dataType, '__ci_value' => $value];

        if ($ttl === 0) {
            return (bool) $this->redis->hMset($key, $fields);
        }

        $expiresAt = Time::now()->getTimestamp() + $ttl;

        $shouldFallback = false;

        $this->redis->clearLastError();

        try {
            $result = $this->redis->eval(
                self::SAVE_SCRIPT,
                [$key, (string) $expiresAt, $dataType, (string) $value],
                1,
            );

            if ($result !== false) {
                return (bool) $result;
            }

            // phpredis reports server errors as false and keeps the message in getLastError().
            $error = $this->redis->getLastError();

            if (is_string($error)) {
                if (self::isEvalUnavailable($error)) {
                    $shouldFallback = true;
                } elseif (self::isDataCommandPermissionError($error)) {
                    // HMSET or EXPIREAT was denied by an ACL: the script itself ran,
                    // so keep throwing exactly like the sequential implementation did.
                    throw new RedisException($error);
                }
            }
        } catch (RedisException $e) {
            if (! self::isEvalUnavailable($e->getMessage())) {
                throw $e;
            }

            $shouldFallback = true;
        }

        if (! $shouldFallback) {
            return false;
        }

        // EVAL is unavailable (renamed command or ACL denial): run both commands sequentially.
        if (! $this->redis->hMset($key, $fields)) {
            return false;
        }

        $this->redis->expireAt($key, $expiresAt);

        return true;
    }

    /**
     * Checks whether the message only means that the EVAL command itself is not
     * available to this client, either because it was renamed or because ACL denies it.
     *
     * Keep the patterns in sync with PredisHandler::isEvalUnavailable().
     */
    private static function isEvalUnavailable(string $message): bool
    {
        return preg_match("/unknown command [`']?eval\\b/i", $message) === 1
            || preg_match("/no permissions to run the [`']eval[`'] command/i", $message) === 1;
    }

    /**
     * Checks whether the message means that a data command executed inside the
     * save script was denied by an ACL.
     *
     * EVAL availability was already checked, so the denied command can only be
     * HMSET or EXPIREAT. Redis reports it either as a command level denial or as
     * an ACL failure while running the script.
     *
     * Predis throws for every error reply, so this mapping only exists for the
     * phpredis false plus getLastError() path.
     */
    private static function isDataCommandPermissionError(string $message): bool
    {
        return preg_match("/no permissions to run the [`']?[a-z]+[`'] command/i", $message) === 1
            || preg_match('/ACL failure in script/i', $message) === 1;
    }

    public function delete(string $key): bool
    {
        $key = static::validateKey($key, $this->prefix);

        return $this->redis->del($key) === 1;
    }

    public function deleteMatching(string $pattern): int
    {
        /** @var list<string> $matchedKeys */
        $matchedKeys = [];
        $pattern     = static::validateKey($pattern, $this->prefix);
        $iterator    = null;

        do {
            /** @var false|list<string> $keys */
            $keys = $this->redis->scan($iterator, $pattern);

            if (is_array($keys)) {
                $matchedKeys = [...$matchedKeys, ...$keys];
            }
        } while ($iterator > 0);

        return (int) $this->redis->del($matchedKeys);
    }

    public function increment(string $key, int $offset = 1): int
    {
        $key = static::validateKey($key, $this->prefix);

        return $this->redis->hIncrBy($key, '__ci_value', $offset);
    }

    public function decrement(string $key, int $offset = 1): int
    {
        return $this->increment($key, -$offset);
    }

    public function clean(): bool
    {
        return $this->redis->flushDB();
    }

    public function getCacheInfo(): array
    {
        return $this->redis->info();
    }

    public function getMetaData(string $key): ?array
    {
        $value = $this->get($key);

        if ($value !== null) {
            $time = Time::now()->getTimestamp();
            $ttl  = $this->redis->ttl(static::validateKey($key, $this->prefix));
            assert(is_int($ttl));

            return [
                'expire' => $ttl > 0 ? $time + $ttl : null,
                'mtime'  => $time,
                'data'   => $value,
            ];
        }

        return null;
    }

    public function isSupported(): bool
    {
        return extension_loaded('redis');
    }

    public function ping(): bool
    {
        if (! isset($this->redis)) {
            return false;
        }

        try {
            $result = $this->redis->ping();

            return in_array($result, [true, '+PONG'], true);
        } catch (RedisException) {
            return false;
        }
    }

    public function reconnect(): bool
    {
        if (isset($this->redis)) {
            try {
                $this->redis->close();
            } catch (RedisException) {
                // Connection already dead, that's fine
            }
        }

        try {
            $this->initialize();

            return true;
        } catch (CriticalError $e) {
            log_message('error', 'Cache: Redis reconnection failed: ' . $e->getMessage());

            return false;
        }
    }
}
