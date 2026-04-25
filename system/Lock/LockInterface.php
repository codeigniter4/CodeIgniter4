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
    public function acquire(): bool;

    public function block(int $seconds): bool;

    /**
     * @param Closure(): mixed $callback
     */
    public function run(Closure $callback, int $waitSeconds = 0): mixed;

    public function release(): bool;

    public function forceRelease(): bool;

    public function refresh(?int $ttl = null): bool;

    public function isAcquired(): bool;

    public function owner(): string;
}
