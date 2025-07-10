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

use CodeIgniter\Cache\FactoriesCache\FileVarExportHandler;
use CodeIgniter\Config\Factories;

final class FactoriesCache
{
    private readonly CacheInterface|FileVarExportHandler $cache;

    public function __construct(CacheInterface|FileVarExportHandler|null $cache = null)
    {
        $this->cache = $cache ?? new FileVarExportHandler();
    }

    public function save(string $component): void
    {
        if (! Factories::isUpdated($component)) {
            return;
        }

        $data = Factories::getComponentInstances($component);

        $this->cache->save($this->getCacheKey($component), $data, 3600 * 24);
    }

    private function getCacheKey(string $component): string
    {
        return 'FactoriesCache_' . $component;
    }

    public function load(string $component): bool
    {
        $key = $this->getCacheKey($component);

        $data = $this->cache->get($key);

        if (! is_array($data) || $data === []) {
            return false;
        }

        Factories::setComponentInstances($component, $data);

        return true;
    }

    public function delete(string $component): void
    {
        $this->cache->delete($this->getCacheKey($component));
    }
}
