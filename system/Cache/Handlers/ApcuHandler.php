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

use APCUIterator;
use Closure;
use CodeIgniter\I18n\Time;
use Config\Cache;

/**
 * APCu cache handler
 *
 * @see \CodeIgniter\Cache\Handlers\ApcuHandlerTest
 */
class ApcuHandler extends BaseHandler
{
    /**
     * Note: Use `CacheFactory::getHandler()` to instantiate.
     */
    public function __construct(Cache $config)
    {
        $this->prefix = $config->prefix;
    }

    public function initialize(): void
    {
    }

    public function get(string $key): mixed
    {
        $key     = static::validateKey($key, $this->prefix);
        $success = false;

        $data = apcu_fetch($key, $success);

        // Success returned by reference from apcu_fetch()
        return $success ? $data : null;
    }

    public function save(string $key, $value, int $ttl = 60): bool
    {
        $key = static::validateKey($key, $this->prefix);

        return apcu_store($key, $value, $ttl);
    }

    public function remember(string $key, int $ttl, Closure $callback): mixed
    {
        $key = static::validateKey($key, $this->prefix);

        return apcu_entry($key, $callback, $ttl);
    }

    public function delete(string $key): bool
    {
        $key = static::validateKey($key, $this->prefix);

        return apcu_delete($key);
    }

    public function deleteMatching(string $pattern): int
    {
        $matchedKeys = array_filter(
            array_keys(iterator_to_array(new APCUIterator(null, APC_ITER_KEY))),
            static fn ($key): bool => fnmatch($pattern, $key),
        );

        if ($matchedKeys !== []) {
            return count($matchedKeys) - count(apcu_delete($matchedKeys));
        }

        return 0;
    }

    public function increment(string $key, int $offset = 1): false|int
    {
        $key = static::validateKey($key, $this->prefix);

        return apcu_inc($key, $offset);
    }

    public function decrement(string $key, int $offset = 1): false|int
    {
        $key = static::validateKey($key, $this->prefix);

        return apcu_dec($key, $offset);
    }

    public function clean(): bool
    {
        return apcu_clear_cache();
    }

    public function getCacheInfo(): array|false
    {
        return apcu_cache_info(true);
    }

    public function getMetaData(string $key): ?array
    {
        $key      = static::validateKey($key, $this->prefix);
        $metadata = apcu_key_info($key);

        if ($metadata !== null) {
            return [
                'expire' => $metadata['ttl'] > 0 ? Time::now()->getTimestamp() + $metadata['ttl'] : null,
                'mtime'  => $metadata['mtime'],
                'data'   => apcu_fetch($key),
            ];
        }

        return null;
    }

    public function isSupported(): bool
    {
        return extension_loaded('apcu') && apcu_enabled();
    }
}
