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

use CodeIgniter\Cache\Handlers\PredisHandler;
use CodeIgniter\Cache\LockStoreInterface;
use Predis\Client;
use Predis\Response\Status;

class PredisLockStore implements LockStoreInterface
{
    public function __construct(private readonly Client $redis)
    {
    }

    public function acquireLock(string $key, string $owner, int $ttl): bool
    {
        $key    = PredisHandler::validateKey($key);
        $result = $this->redis->set($key, $owner, 'EX', $ttl, 'NX');

        return $result instanceof Status && $result->getPayload() === 'OK';
    }

    public function releaseLock(string $key, string $owner): bool
    {
        $key = PredisHandler::validateKey($key);

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
        $key     = PredisHandler::validateKey($key);
        $deleted = $this->redis->del($key);

        return is_int($deleted) && $deleted >= 0;
    }

    public function refreshLock(string $key, string $owner, int $ttl): bool
    {
        $key = PredisHandler::validateKey($key);

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
        $key   = PredisHandler::validateKey($key);
        $owner = $this->redis->get($key);

        return is_string($owner) ? $owner : null;
    }
}
