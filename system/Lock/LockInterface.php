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

interface LockInterface
{
    /**
     * Attempts to acquire the lock immediately.
     */
    public function acquire(): bool;

    /**
     * Attempts to acquire the lock, waiting up to the given number of seconds.
     */
    public function block(int $seconds): bool;

    /**
     * Runs the callback while the lock is held.
     *
     * @param Closure(): mixed $callback
     */
    public function run(Closure $callback, int $waitSeconds = 0): mixed;

    /**
     * Releases the lock only if this instance still owns it.
     */
    public function release(): bool;

    /**
     * Releases the lock without checking ownership.
     */
    public function forceRelease(): bool;

    /**
     * Extends the lock TTL only if this instance still owns it.
     */
    public function refresh(?int $ttl = null): bool;

    /**
     * Checks whether this instance still owns the lock.
     */
    public function isAcquired(): bool;

    /**
     * Returns this instance's owner token.
     */
    public function owner(): string;
}
