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

namespace CodeIgniter\Cache;

interface LockStoreInterface
{
    /**
     * Attempts to acquire a lock for the given owner and TTL.
     */
    public function acquireLock(string $key, string $owner, int $ttl): bool;

    /**
     * Releases the lock only when it is currently held by the given owner.
     */
    public function releaseLock(string $key, string $owner): bool;

    /**
     * Releases the lock without checking ownership.
     */
    public function forceReleaseLock(string $key): bool;

    /**
     * Extends the lock TTL only when it is currently held by the given owner.
     */
    public function refreshLock(string $key, string $owner, int $ttl): bool;

    /**
     * Returns the current owner token, or null when the lock is not held.
     */
    public function getLockOwner(string $key): ?string;
}
