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

namespace Tests\Support\Cache;

use CodeIgniter\Cache\FactoriesCache\FileVarExportHandler;

/**
 * Gives the test class its own directory for the files created
 * by the FileVarExportHandler.
 *
 * The handler stores its files in the shared "writable/cache" directory by
 * default. Since the random test execution runs the components in parallel,
 * one component may delete the cache file that another component is
 * asserting on. A dedicated directory per test class avoids that.
 */
trait IsolatedCacheTrait
{
    /**
     * The directory used to store the cache files of the test class.
     */
    private static string $isolatedCachePath;

    public static function setUpBeforeClassIsolatedCache(): void
    {
        helper('filesystem');

        self::$isolatedCachePath = WRITEPATH . 'cache_test_' . str_replace('\\', '_', static::class);

        // Remove the files left over by a previous run.
        if (is_dir(self::$isolatedCachePath)) {
            delete_files(self::$isolatedCachePath, true);
            @rmdir(self::$isolatedCachePath);
        }
    }

    public static function tearDownAfterClassIsolatedCache(): void
    {
        if (! isset(self::$isolatedCachePath) || ! is_dir(self::$isolatedCachePath)) {
            return;
        }

        delete_files(self::$isolatedCachePath, true);
        @rmdir(self::$isolatedCachePath);
    }

    /**
     * Returns a handler that stores its files in the isolated directory.
     */
    protected function getIsolatedCacheHandler(): FileVarExportHandler
    {
        return new FileVarExportHandler(self::$isolatedCachePath);
    }

    /**
     * Returns the path of the isolated cache directory.
     */
    protected static function getIsolatedCachePath(): string
    {
        return self::$isolatedCachePath;
    }
}
