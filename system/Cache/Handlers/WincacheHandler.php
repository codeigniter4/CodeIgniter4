<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Cache\Handlers;

use Config\Cache;
use Exception;

/**
 * Cache handler for WinCache from Microsoft & IIS.
 *
 * @codeCoverageIgnore
 */
class WincacheHandler extends BaseHandler
{
    public function __construct(Cache $config)
    {
        $this->prefix = $config->prefix;
    }

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
        $key     = static::validateKey($key, $this->prefix);
        $success = false;

        $data = wincache_ucache_get($key, $success);

        // Success returned by reference from wincache_ucache_get()
        return $success ? $data : null;
    }

    /**
     * {@inheritDoc}
     */
    public function save(string $key, $value, int $ttl = 60)
    {
        $key = static::validateKey($key, $this->prefix);

        return wincache_ucache_set($key, $value, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key)
    {
        $key = static::validateKey($key, $this->prefix);

        return wincache_ucache_delete($key);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteMatching(string $pattern)
    {
        throw new Exception('The deleteMatching method is not implemented for Wincache. You must select File, Redis or Predis handlers to use it.');
    }

    /**
     * {@inheritDoc}
     */
    public function increment(string $key, int $offset = 1)
    {
        $key = static::validateKey($key, $this->prefix);

        return wincache_ucache_inc($key, $offset);
    }

    /**
     * {@inheritDoc}
     */
    public function decrement(string $key, int $offset = 1)
    {
        $key = static::validateKey($key, $this->prefix);

        return wincache_ucache_dec($key, $offset);
    }

    /**
     * {@inheritDoc}
     */
    public function clean()
    {
        return wincache_ucache_clear();
    }

    /**
     * {@inheritDoc}
     */
    public function getCacheInfo()
    {
        return wincache_ucache_info(true);
    }

    /**
     * {@inheritDoc}
     */
    public function getMetaData(string $key)
    {
        $key = static::validateKey($key, $this->prefix);

        if ($stored = wincache_ucache_info(false, $key)) {
            $age      = $stored['ucache_entries'][1]['age_seconds'];
            $ttl      = $stored['ucache_entries'][1]['ttl_seconds'];
            $hitcount = $stored['ucache_entries'][1]['hitcount'];

            return [
                'expire'   => $ttl > 0 ? time() + $ttl : null,
                'hitcount' => $hitcount,
                'age'      => $age,
                'ttl'      => $ttl,
            ];
        }

        return false; // @TODO This will return null in a future release
    }

    /**
     * {@inheritDoc}
     */
    public function isSupported(): bool
    {
        return extension_loaded('wincache') && ini_get('wincache.ucenabled');
    }
}
