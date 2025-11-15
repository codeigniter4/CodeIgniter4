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
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class FactoriesCacheFileVarExportHandlerTest extends AbstractFactoriesCacheHandlerTestCase
{
    protected function createFactoriesCache(): void
    {
        $this->handler = new FileVarExportHandler();
        $this->cache   = new FactoriesCache($this->handler);
    }
}
