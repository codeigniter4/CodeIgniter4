<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test\Mock;

use Closure;
use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Cache\Handlers\BaseHandler;

class MockCache extends BaseHandler implements CacheInterface
{
    /**
     * Mock cache storage.
     *
     * @var array
     */
    protected $cache = [];

    /**
     * Expiration times.
     *
     * @var ?int[]
     */
    protected $expirations = [];

    /**
     * Takes care of any handler-specific setup that must be done.
     */
    public function initialize()
    {
    }

    /**
     * Attempts to fetch an item from the cache store.
     *
     * @param string $key Cache item name
     *
     * @return mixed
     */
    public function get(string $key)
    {
        $key = static::validateKey($key, $this->prefix);

        return $this->cache[$key] ?? null;
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     *
     * @param string  $key      Cache item name
     * @param int     $ttl      Time to live
     * @param Closure $callback Callback return value
     *
     * @return mixed
     */
    public function remember(string $key, int $ttl, Closure $callback)
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $this->save($key, $value = $callback(), $ttl);

        return $value;
    }

    /**
     * Saves an item to the cache store.
     *
     * The $raw parameter is only utilized by Mamcache in order to
     * allow usage of increment() and decrement().
     *
     * @param string $key   Cache item name
     * @param mixed  $value the data to save
     * @param int    $ttl   Time To Live, in seconds (default 60)
     * @param bool   $raw   Whether to store the raw value.
     *
     * @return bool
     */
    public function save(string $key, $value, int $ttl = 60, bool $raw = false)
    {
        $key = static::validateKey($key, $this->prefix);

        $this->cache[$key]       = $value;
        $this->expirations[$key] = $ttl > 0 ? time() + $ttl : null;

        return true;
    }

    /**
     * Deletes a specific item from the cache store.
     *
     * @param string $key Cache item name
     *
     * @return bool
     */
    public function delete(string $key)
    {
        $key = static::validateKey($key, $this->prefix);

        if (! isset($this->cache[$key])) {
            return false;
        }

        unset($this->cache[$key], $this->expirations[$key]);

        return true;
    }

    /**
     * Deletes items from the cache store matching a given pattern.
     *
     * @param string $pattern Cache items glob-style pattern
     *
     * @return int
     */
    public function deleteMatching(string $pattern)
    {
        $count = 0;

        foreach (array_keys($this->cache) as $key) {
            if (fnmatch($pattern, $key)) {
                $count++;
                unset($this->cache[$key], $this->expirations[$key]);
            }
        }

        return $count;
    }

    /**
     * Performs atomic incrementation of a raw stored value.
     *
     * @param string $key    Cache ID
     * @param int    $offset Step/value to increase by
     *
     * @return bool
     */
    public function increment(string $key, int $offset = 1)
    {
        $key  = static::validateKey($key, $this->prefix);
        $data = $this->cache[$key] ?: null;

        if (empty($data)) {
            $data = 0;
        } elseif (! is_int($data)) {
            return false;
        }

        return $this->save($key, $data + $offset);
    }

    /**
     * Performs atomic decrementation of a raw stored value.
     *
     * @param string $key    Cache ID
     * @param int    $offset Step/value to increase by
     *
     * @return bool
     */
    public function decrement(string $key, int $offset = 1)
    {
        $key = static::validateKey($key, $this->prefix);

        $data = $this->cache[$key] ?: null;

        if (empty($data)) {
            $data = 0;
        } elseif (! is_int($data)) {
            return false;
        }

        return $this->save($key, $data - $offset);
    }

    /**
     * Will delete all items in the entire cache.
     *
     * @return bool
     */
    public function clean()
    {
        $this->cache       = [];
        $this->expirations = [];

        return true;
    }

    /**
     * Returns information on the entire cache.
     *
     * The information returned and the structure of the data
     * varies depending on the handler.
     *
     * @return string[] Keys currently present in the store
     */
    public function getCacheInfo()
    {
        return array_keys($this->cache);
    }

    /**
     * Returns detailed information about the specific item in the cache.
     *
     * @param string $key Cache item name.
     *
     * @return array|null
     *                    Returns null if the item does not exist, otherwise array<string, mixed>
     *                    with at least the 'expire' key for absolute epoch expiry (or null).
     */
    public function getMetaData(string $key)
    {
        // Misses return null
        if (! array_key_exists($key, $this->expirations)) {
            return null;
        }

        // Count expired items as a miss
        if (is_int($this->expirations[$key]) && $this->expirations[$key] > time()) {
            return null;
        }

        return [
            'expire' => $this->expirations[$key],
        ];
    }

    /**
     * Determines if the driver is supported on this system.
     */
    public function isSupported(): bool
    {
        return true;
    }
}
