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

namespace CodeIgniter\Cache\LockStores;

use CodeIgniter\Cache\Handlers\RedisHandler;
use CodeIgniter\Cache\LockStoreInterface;
use Redis;

class RedisLockStore implements LockStoreInterface
{
    public function __construct(
        private readonly Redis $redis,
        private readonly string $prefix = '',
    ) {
    }

    public function acquireLock(string $key, string $owner, int $ttl): bool
    {
        $key = RedisHandler::validateKey($key, $this->prefix);

        return (bool) $this->redis->set($key, $owner, ['nx', 'ex' => $ttl]);
    }

    public function releaseLock(string $key, string $owner): bool
    {
        $key = RedisHandler::validateKey($key, $this->prefix);

        $script = <<<'LUA'
            if redis.call("get", KEYS[1]) == ARGV[1] then
                return redis.call("del", KEYS[1])
            end

            return 0
            LUA;

        return (int) $this->redis->eval($script, [$key, $owner], 1) === 1;
    }

    public function forceReleaseLock(string $key): bool
    {
        $key     = RedisHandler::validateKey($key, $this->prefix);
        $deleted = $this->redis->del($key);

        return is_int($deleted) && $deleted >= 0;
    }

    public function refreshLock(string $key, string $owner, int $ttl): bool
    {
        $key = RedisHandler::validateKey($key, $this->prefix);

        $script = <<<'LUA'
            if redis.call("get", KEYS[1]) == ARGV[1] then
                return redis.call("expire", KEYS[1], ARGV[2])
            end

            return 0
            LUA;

        return (int) $this->redis->eval($script, [$key, $owner, $ttl], 1) === 1;
    }

    public function getLockOwner(string $key): ?string
    {
        $key   = RedisHandler::validateKey($key, $this->prefix);
        $owner = $this->redis->get($key);

        return is_string($owner) ? $owner : null;
    }
}
