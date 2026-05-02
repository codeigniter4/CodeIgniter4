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

use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Cache\Exceptions\CacheException;
use CodeIgniter\Cache\LockStoreInterface;
use CodeIgniter\Cache\LockStoreProviderInterface;
use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Lock\Exceptions\LockException;

final readonly class LockManager
{
    private const KEY_PREFIX = 'lock_';

    private LockStoreInterface $store;

    /**
     * @param CacheInterface&LockStoreProviderInterface $cache Cache handler that supports lock stores.
     *
     * @throws LockException When the cache handler does not support locks.
     */
    public function __construct(CacheInterface $cache)
    {
        if (! $cache instanceof LockStoreProviderInterface) {
            throw LockException::forUnsupportedStore($cache::class);
        }

        try {
            $this->store = $cache->lockStore();
        } catch (CacheException) {
            throw LockException::forUnsupportedStore($cache::class);
        }
    }

    public function create(string $name, int $ttl = 300, ?string $owner = null): LockInterface
    {
        if ($name === '') {
            throw new InvalidArgumentException('Lock name cannot be empty.');
        }

        return new Lock($this->store, $this->key($name), $ttl, $owner ?? bin2hex(random_bytes(16)));
    }

    public function restore(string $name, string $owner, int $ttl = 300): LockInterface
    {
        return $this->create($name, $ttl, $owner);
    }

    private function key(string $name): string
    {
        return self::KEY_PREFIX . hash('xxh128', $name);
    }
}
