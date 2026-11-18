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
use Override;

/**
 * Dummy cache handler
 */
class DummyHandler extends BaseHandler
{
    #[Override]
    public function initialize(): void
    {
    }

    #[Override]
    public function get(string $key): mixed
    {
        return null;
    }

    #[Override]
    public function remember(string $key, callable|int $ttl, Closure $callback): mixed
    {
        return null;
    }

    #[Override]
    public function save(string $key, mixed $value, int $ttl = 60): bool
    {
        return true;
    }

    #[Override]
    public function delete(string $key): bool
    {
        return true;
    }

    #[Override]
    public function deleteMatching(string $pattern): int
    {
        return 0;
    }

    #[Override]
    public function increment(string $key, int $offset = 1): bool
    {
        return true;
    }

    #[Override]
    public function decrement(string $key, int $offset = 1): bool
    {
        return true;
    }

    #[Override]
    public function clean(): bool
    {
        return true;
    }

    #[Override]
    public function getCacheInfo(): ?array
    {
        return null;
    }

    #[Override]
    public function getMetaData(string $key): ?array
    {
        return null;
    }

    #[Override]
    public function isSupported(): bool
    {
        return true;
    }
}
