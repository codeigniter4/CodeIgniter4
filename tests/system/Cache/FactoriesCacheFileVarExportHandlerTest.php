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
use Tests\Support\Cache\IsolatedCacheTrait;

/**
 * @internal
 */
#[Group('Others')]
final class FactoriesCacheFileVarExportHandlerTest extends AbstractFactoriesCacheHandlerTestCase
{
    use IsolatedCacheTrait;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::setUpBeforeClassIsolatedCache();

        helper('filesystem');
    }

    public static function tearDownAfterClass(): void
    {
        self::tearDownAfterClassIsolatedCache();

        parent::tearDownAfterClass();
    }

    protected function createFactoriesCache(): void
    {
        $this->handler = $this->getIsolatedCacheHandler();
        $this->cache   = new FactoriesCache($this->handler);
    }

    public function testUsesTheWritableCacheDirectoryByDefault(): void
    {
        $handler = new FileVarExportHandler();

        $this->assertSame(WRITEPATH . 'cache', $this->getPrivateProperty($handler, 'path'));
    }

    public function testSaveCreatesDirectoryAndFileWithCorrectPermissions(): void
    {
        $dir      = WRITEPATH . 'cache_test_dir_' . uniqid('', true);
        $oldUmask = umask(0000);

        try {
            $handler = new FileVarExportHandler($dir);

            $handler->save('test_key', ['data']);

            $this->assertDirectoryExists($dir);

            if (! is_windows()) {
                $dirPerms = fileperms($dir) & 0777;
                $this->assertSame(0755, $dirPerms);

                $filePerms = fileperms($dir . '/test_key') & 0777;
                $this->assertSame(0644, $filePerms);
            }
        } finally {
            umask($oldUmask);

            if (is_dir($dir)) {
                delete_files($dir);
                rmdir($dir);
            }
        }
    }
}
