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
use CodeIgniter\Cache\LockStoreInterface;
use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Lock\Exceptions\LockException;

class LockManager
{
    private const KEY_PREFIX = 'lock_';

    public function __construct(private readonly CacheInterface $cache)
    {
    }

    public function create(string $name, int $ttl = 300, ?string $owner = null): LockInterface
    {
        if ($name === '') {
            throw new InvalidArgumentException('Lock name cannot be empty.');
        }

        $store = $this->store();
        $key   = $this->key($name);

        return new Lock($store, $key, $ttl, $owner ?? bin2hex(random_bytes(16)));
    }

    public function restore(string $name, string $owner, int $ttl = 300): LockInterface
    {
        return $this->create($name, $ttl, $owner);
    }

    private function store(): LockStoreInterface
    {
        if (! $this->cache instanceof LockStoreInterface) {
            throw LockException::forUnsupportedStore($this->cache::class);
        }

        return $this->cache;
    }

    private function key(string $name): string
    {
        return self::KEY_PREFIX . hash('sha256', $name);
    }
}
