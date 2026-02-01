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

use Closure;

interface CacheInterface
{
    /**
     * Takes care of any handler-specific setup that must be done.
     */
    public function initialize(): void;

    /**
     * Attempts to fetch an item from the cache store.
     *
     * @param string $key Cache item name
     */
    public function get(string $key): mixed;

    /**
     * Saves an item to the cache store.
     *
     * @param string $key   Cache item name
     * @param mixed  $value The data to save
     * @param int    $ttl   Time To Live, in seconds (default 60)
     *
     * @return bool Success or failure
     */
    public function save(string $key, mixed $value, int $ttl = 60): bool;

    /**
     * Attempts to get an item from the cache, or executes the callback
     * and stores the result on cache miss.
     *
     * @param string           $key      Cache item name
     * @param int              $ttl      Time To Live, in seconds
     * @param Closure(): mixed $callback Callback executed on cache miss
     */
    public function remember(string $key, int $ttl, Closure $callback): mixed;

    /**
     * Deletes a specific item from the cache store.
     *
     * @param string $key Cache item name
     *
     * @return bool Success or failure
     */
    public function delete(string $key): bool;

    /**
     * Deletes items from the cache store matching a given pattern.
     *
     * @param string $pattern Cache items glob-style pattern
     *
     * @return int Number of deleted items
     */
    public function deleteMatching(string $pattern): int;

    /**
     * Performs atomic incrementation of a raw stored value.
     *
     * @param string $key    Cache ID
     * @param int    $offset Step/value to increase by
     */
    public function increment(string $key, int $offset = 1): bool|int;

    /**
     * Performs atomic decrementation of a raw stored value.
     *
     * @param string $key    Cache ID
     * @param int    $offset Step/value to increase by
     */
    public function decrement(string $key, int $offset = 1): bool|int;

    /**
     * Will delete all items in the entire cache.
     *
     * @return bool Success or failure
     */
    public function clean(): bool;

    /**
     * Returns information on the entire cache.
     *
     * The information returned and the structure of the data
     * varies depending on the handler.
     *
     * @return array<array-key, mixed>|false|object|null
     */
    public function getCacheInfo(): array|false|object|null;

    /**
     * Returns detailed information about the specific item in the cache.
     *
     * @param string $key Cache item name.
     *
     * @return array<string, mixed>|null Returns null if the item does not exist, otherwise array<string, mixed>
     *                                   with at least the 'expire' key for absolute epoch expiry (or null).
     */
    public function getMetaData(string $key): ?array;

    /**
     * Determines if the driver is supported on this system.
     */
    public function isSupported(): bool;
}
