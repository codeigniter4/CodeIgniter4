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

namespace CodeIgniter\Commands\Utilities;

use Closure;
use CodeIgniter\Autoloader\FileLocator;
use CodeIgniter\Autoloader\FileLocatorCached;
use CodeIgniter\Cache\FactoriesCache\FileVarExportHandler;
use CodeIgniter\CLI\Output;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ReflectionHelper;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Services;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Cache\IsolatedCacheTrait;

/**
 * @internal
 */
#[Group('Others')]
final class OptimizeTest extends CIUnitTestCase
{
    use IsolatedCacheTrait;
    use ReflectionHelper;
    use StreamFilterTrait;

    private string $file = WRITEPATH . 'cache/OptimizeTest_config';
    private FileVarExportHandler $handler;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::setUpBeforeClassIsolatedCache();
    }

    public static function tearDownAfterClass(): void
    {
        self::tearDownAfterClassIsolatedCache();

        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = $this->getIsolatedCacheHandler();
        $this->handler->delete('FileLocatorCache');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (is_file($this->file)) {
            unlink($this->file);
        }

        $this->handler->delete('FileLocatorCache');
        Services::resetSingle('locator');
    }

    /**
     * @return Closure(Output, string): void
     */
    private function getRemoveFile(): Closure
    {
        return self::getPrivateMethodInvoker(new Optimize(service('commands')), 'removeFile');
    }

    /**
     * @return Closure(Output): void
     */
    private function getClearCache(): Closure
    {
        return self::getPrivateMethodInvoker(new Optimize(service('commands')), 'clearCache');
    }

    public function testRemoveFileDeletesTheFile(): void
    {
        file_put_contents($this->file, '<?php');

        ($this->getRemoveFile())(new Output(), $this->file);

        $this->assertFileDoesNotExist($this->file);
        $this->assertStringContainsString('Removed', $this->getStreamFilterBuffer());
    }

    public function testRemoveFileDoesNothingWhenFileIsAbsent(): void
    {
        $this->assertFileDoesNotExist($this->file);

        ($this->getRemoveFile())(new Output(), $this->file);

        $this->assertSame('', $this->getStreamFilterBuffer());
    }

    public function testClearCacheDiscardsSharedLocatorCache(): void
    {
        $locator = new FileLocatorCached(new FileLocator(service('autoloader')), $this->handler);
        $locator->search('Config/App');
        Services::injectMock('locator', $locator);

        ($this->getClearCache())(new Output());

        $locator->search('Config/Cache');
        $locator->__destruct();

        $cached = $this->handler->get('FileLocatorCache');

        $this->assertArrayNotHasKey('Config/App', $cached['search']);
        $this->assertArrayHasKey('Config/Cache', $cached['search']);
        $this->assertStringContainsString('Removed FileLocatorCache.', $this->getStreamFilterBuffer());

        // Prevent the destructor from writing the cache file
        // when the locator is destroyed.
        $locator->deleteCache();
    }

    public function testClearCacheDeletesTheFileWithoutSharedLocatorCache(): void
    {
        $writePath = rtrim(sys_get_temp_dir(), '\\/') . DIRECTORY_SEPARATOR . 'ci_test_optimize_' . uniqid('', true) . DIRECTORY_SEPARATOR;
        mkdir($writePath . 'cache', 0755, true);

        $escapedWritePath = var_export($writePath, true);
        $escapedBootstrap = var_export(SYSTEMPATH . 'Test/bootstrap.php', true);

        try {
            $code = <<<PHP
                define('WRITEPATH', {$escapedWritePath});
                require {$escapedBootstrap};

                use CodeIgniter\\Cache\\FactoriesCache\\FileVarExportHandler;
                use CodeIgniter\\CLI\\Output;
                use CodeIgniter\\Commands\\Utilities\\Optimize;
                use CodeIgniter\\Test\\ReflectionHelper;

                \$handler = new FileVarExportHandler();
                \$handler->save('FileLocatorCache', ['search' => []]);

                \$clearCache = ReflectionHelper::getPrivateMethodInvoker(
                    new Optimize(service('commands')),
                    'clearCache'
                );
                \$clearCache(new Output());

                if (\$handler->get('FileLocatorCache') !== false) {
                    exit(1);
                }
                PHP;

            $cmd = sprintf('%s -r %s 2>&1', escapeshellarg(PHP_BINARY), escapeshellarg($code));
            exec($cmd, $output, $status);

            $this->assertSame(0, $status, implode(PHP_EOL, $output));
            $this->assertStringContainsString('Removed FileLocatorCache.', implode(PHP_EOL, $output));
        } finally {
            helper('filesystem');
            delete_files($writePath, true);
            @rmdir($writePath);
        }
    }
}
