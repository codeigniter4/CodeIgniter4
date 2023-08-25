<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Cache;

use Config\Cache as CacheConfig;

/**
 * @internal
 *
 * @group Others
 */
final class FactoriesCacheFileHandlerTest extends FactoriesCacheFileVarExportHandlerTest
{
    /**
     * @var @var FileVarExportHandler|CacheInterface
     */
    protected $handler;

    protected function createFactoriesCache(): void
    {
        $this->handler = CacheFactory::getHandler(new CacheConfig(), 'file');
        $this->cache   = new FactoriesCache($this->handler);
    }
}
