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

/**
 * Cache interface
 */
interface CacheInterface
{
    /**
     * Takes care of any handler-specific setup that must be done.
     *
     * @return void
     */
    public function initialize();

    /**
     * Attempts to fetch an item from the cache store.
     *
     * @param string $key Cache item name
     *
     * @return array|bool|float|int|object|string|null
     */
    public function get(string $key);

    /**
     * Saves an item to the cache store.
     *
     * @param string                                  $key   Cache item name
     * @param array|bool|float|int|object|string|null $value The data to save
     * @param int                                     $ttl   Time To Live, in seconds (default 60)
     *
     * @return bool Success or failure
     */
    public function save(string $key, $value, int $ttl = 60);

    /**
     * Deletes a specific item from the cache store.
     *
     * @param string $key Cache item name
     *
     * @return bool Success or failure
     */
    public function delete(string $key);

    /**
     * Performs atomic incrementation of a raw stored value.
     *
     * @param string $key    Cache ID
     * @param int    $offset Step/value to increase by
     *
     * @return bool|int
     */
    public function increment(string $key, int $offset = 1);

    /**
     * Performs atomic decrementation of a raw stored value.
     *
     * @param string $key    Cache ID
     * @param int    $offset Step/value to increase by
     *
     * @return bool|int
     */
    public function decrement(string $key, int $offset = 1);

    /**
     * Will delete all items in the entire cache.
     *
     * @return bool Success or failure
     */
    public function clean();

    /**
     * Returns information on the entire cache.
     *
     * The information returned and the structure of the data
     * varies depending on the handler.
     *
     * @return array|false|object|null
     */
    public function getCacheInfo();

    /**
     * Returns detailed information about the specific item in the cache.
     *
     * @param string $key Cache item name.
     *
     * @return array|false|null
     *                          Returns null if the item does not exist, otherwise array<string, mixed>
     *                          with at least the 'expire' key for absolute epoch expiry (or null).
     *                          Some handlers may return false when an item does not exist, which is deprecated.
     */
    public function getMetaData(string $key);

    /**
     * Determines if the driver is supported on this system.
     */
    public function isSupported(): bool;
}
