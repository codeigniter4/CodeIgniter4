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
use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Exceptions\InvalidArgumentException;
use Config\Cache;

/**
 * Base class for cache handling
 *
 * @see \CodeIgniter\Cache\Handlers\BaseHandlerTest
 */
abstract class BaseHandler implements CacheInterface
{
    /**
     * Reserved characters that cannot be used in a key or tag. May be overridden by the config.
     * From https://github.com/symfony/cache-contracts/blob/c0446463729b89dd4fa62e9aeecc80287323615d/ItemInterface.php#L43
     *
     * @deprecated in favor of the Cache config
     */
    public const RESERVED_CHARACTERS = '{}()/\@:';

    /**
     * Maximum key length.
     */
    public const MAX_KEY_LENGTH = PHP_INT_MAX;

    /**
     * Prefix to apply to cache keys.
     * May not be used by all handlers.
     *
     * @var string
     */
    protected $prefix;

    /**
     * Validates a cache key according to PSR-6.
     * Keys that exceed MAX_KEY_LENGTH are hashed.
     * From https://github.com/symfony/cache/blob/7b024c6726af21fd4984ac8d1eae2b9f3d90de88/CacheItem.php#L158
     *
     * @param mixed  $key    The key to validate
     * @param string $prefix Optional prefix to include in length calculations
     *
     * @throws InvalidArgumentException When $key is not valid
     */
    public static function validateKey($key, $prefix = ''): string
    {
        if (! is_string($key)) {
            throw new InvalidArgumentException('Cache key must be a string');
        }
        if ($key === '') {
            throw new InvalidArgumentException('Cache key cannot be empty.');
        }

        $reserved = config(Cache::class)->reservedCharacters;

        if ($reserved !== '' && strpbrk($key, $reserved) !== false) {
            throw new InvalidArgumentException('Cache key contains reserved characters ' . $reserved);
        }

        // If the key with prefix exceeds the length then return the hashed version
        return strlen($prefix . $key) > static::MAX_KEY_LENGTH ? $prefix . md5($key) : $prefix . $key;
    }

    public function remember(string $key, int $ttl, Closure $callback): mixed
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $this->save($key, $value = $callback(), $ttl);

        return $value;
    }

    /**
     * Check if connection is alive.
     *
     * Default implementation for handlers that don't require connection management.
     * Handlers with persistent connections (Redis, Predis, Memcached) should override this.
     */
    public function ping(): bool
    {
        return true;
    }

    /**
     * Reconnect to the cache server.
     *
     * Default implementation for handlers that don't require connection management.
     * Handlers with persistent connections (Redis, Predis, Memcached) should override this.
     */
    public function reconnect(): bool
    {
        return true;
    }
}
