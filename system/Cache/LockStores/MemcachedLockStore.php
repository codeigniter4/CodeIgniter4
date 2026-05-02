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

use CodeIgniter\Cache\Handlers\MemcachedHandler;
use CodeIgniter\Cache\LockStoreInterface;
use Memcached;

class MemcachedLockStore implements LockStoreInterface
{
    private const RELEASE_TTL = 2;

    public function __construct(
        private readonly Memcached $memcached,
        private readonly string $prefix = '',
    ) {
    }

    public function acquireLock(string $key, string $owner, int $ttl): bool
    {
        $key = MemcachedHandler::validateKey($key, $this->prefix);

        return $this->memcached->add($key, $owner, $ttl);
    }

    public function releaseLock(string $key, string $owner): bool
    {
        $key = MemcachedHandler::validateKey($key, $this->prefix);

        [$value, $cas] = $this->getValueAndCas($key);

        if ($value !== $owner || $cas === null) {
            return false;
        }

        // Memcached has no atomic compare-and-delete command. CAS narrows the
        // release race by first shortening only the current owner's value.
        if (! $this->memcached->cas($cas, $key, $owner, self::RELEASE_TTL)) {
            return false;
        }

        return $this->memcached->delete($key);
    }

    public function forceReleaseLock(string $key): bool
    {
        $key = MemcachedHandler::validateKey($key, $this->prefix);

        if ($this->memcached->delete($key)) {
            return true;
        }

        return $this->memcached->getResultCode() === Memcached::RES_NOTFOUND;
    }

    public function refreshLock(string $key, string $owner, int $ttl): bool
    {
        $key = MemcachedHandler::validateKey($key, $this->prefix);

        [$value, $cas] = $this->getValueAndCas($key);

        if ($value !== $owner || $cas === null) {
            return false;
        }

        return $this->memcached->cas($cas, $key, $owner, $ttl);
    }

    public function getLockOwner(string $key): ?string
    {
        $key   = MemcachedHandler::validateKey($key, $this->prefix);
        $owner = $this->memcached->get($key);

        if ($this->memcached->getResultCode() !== Memcached::RES_SUCCESS) {
            return null;
        }

        return is_string($owner) ? $owner : null;
    }

    /**
     * @return array{0: mixed, 1: float|int|null}
     */
    private function getValueAndCas(string $key): array
    {
        $extended = $this->memcached->get($key, null, Memcached::GET_EXTENDED);

        if (! is_array($extended) || ! array_key_exists('value', $extended) || ! array_key_exists('cas', $extended)) {
            return [null, null];
        }

        return [$extended['value'], $extended['cas']];
    }
}
