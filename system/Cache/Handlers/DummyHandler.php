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

namespace CodeIgniter\Cache\Handlers;

use Closure;

/**
 * Dummy cache handler
 *
 * @see \CodeIgniter\Cache\Handlers\DummyHandlerTest
 */
class DummyHandler extends BaseHandler
{
    public function initialize(): void
    {
    }

    public function get(string $key): mixed
    {
        return null;
    }

    public function remember(string $key, int $ttl, Closure $callback): mixed
    {
        return null;
    }

    public function save(string $key, mixed $value, int $ttl = 60): bool
    {
        return true;
    }

    public function delete(string $key): bool
    {
        return true;
    }

    public function deleteMatching(string $pattern): int
    {
        return 0;
    }

    public function increment(string $key, int $offset = 1): bool
    {
        return true;
    }

    public function decrement(string $key, int $offset = 1): bool
    {
        return true;
    }

    public function clean(): bool
    {
        return true;
    }

    public function getCacheInfo(): ?array
    {
        return null;
    }

    public function getMetaData(string $key): ?array
    {
        return null;
    }

    public function isSupported(): bool
    {
        return true;
    }
}
