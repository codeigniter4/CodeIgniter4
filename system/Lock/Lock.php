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

namespace CodeIgniter\Lock;

use Closure;
use CodeIgniter\Cache\LockStoreInterface;
use CodeIgniter\Exceptions\InvalidArgumentException;

final readonly class Lock implements LockInterface
{
    private const BLOCK_RETRY_MICROSECONDS = 100_000;

    public function __construct(
        private LockStoreInterface $store,
        private string $key,
        private int $ttl,
        private string $owner,
    ) {
        if ($ttl < 1) {
            throw new InvalidArgumentException('Lock TTL must be a positive integer.');
        }

        if ($owner === '') {
            throw new InvalidArgumentException('Lock owner cannot be empty.');
        }
    }

    public function acquire(): bool
    {
        return $this->store->acquireLock($this->key, $this->owner, $this->ttl);
    }

    public function block(int $seconds): bool
    {
        if ($seconds < 1) {
            return $this->acquire();
        }

        $expiresAt = microtime(true) + $seconds;

        do {
            if ($this->acquire()) {
                return true;
            }

            usleep(self::BLOCK_RETRY_MICROSECONDS);
        } while (microtime(true) < $expiresAt);

        return false;
    }

    /**
     * @param Closure(): mixed $callback
     */
    public function run(Closure $callback, int $waitSeconds = 0): mixed
    {
        $acquired = $waitSeconds > 0 ? $this->block($waitSeconds) : $this->acquire();

        if (! $acquired) {
            return false;
        }

        try {
            return $callback();
        } finally {
            $this->release();
        }
    }

    public function release(): bool
    {
        return $this->store->releaseLock($this->key, $this->owner);
    }

    public function forceRelease(): bool
    {
        return $this->store->forceReleaseLock($this->key);
    }

    public function refresh(?int $ttl = null): bool
    {
        $ttl ??= $this->ttl;

        if ($ttl < 1) {
            throw new InvalidArgumentException('Lock TTL must be a positive integer.');
        }

        return $this->store->refreshLock($this->key, $this->owner, $ttl);
    }

    public function isAcquired(): bool
    {
        return $this->store->getLockOwner($this->key) === $this->owner;
    }

    public function owner(): string
    {
        return $this->owner;
    }
}
