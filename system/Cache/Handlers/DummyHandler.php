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
    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key)
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function remember(string $key, int $ttl, Closure $callback)
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function save(string $key, $value, int $ttl = 60)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @return int
     */
    public function deleteMatching(string $pattern)
    {
        return 0;
    }

    /**
     * {@inheritDoc}
     */
    public function increment(string $key, int $offset = 1)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function decrement(string $key, int $offset = 1)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function clean()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getCacheInfo()
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetaData(string $key)
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function isSupported(): bool
    {
        return true;
    }
}
