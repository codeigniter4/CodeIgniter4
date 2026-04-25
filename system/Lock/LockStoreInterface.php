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

interface LockStoreInterface
{
    public function acquireLock(string $key, string $owner, int $ttl): bool;

    public function releaseLock(string $key, string $owner): bool;

    public function forceReleaseLock(string $key): bool;

    public function refreshLock(string $key, string $owner, int $ttl): bool;

    public function getLockOwner(string $key): ?string;
}
