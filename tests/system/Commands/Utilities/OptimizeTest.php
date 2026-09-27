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
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ReflectionHelper;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Services;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class OptimizeTest extends CIUnitTestCase
{
    use ReflectionHelper;
    use StreamFilterTrait;

    private string $file = WRITEPATH . 'cache/OptimizeTest_config';
    private FileVarExportHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new FileVarExportHandler();
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
     * @return Closure(string): void
     */
    private function getRemoveFile(): Closure
    {
        return self::getPrivateMethodInvoker(new Optimize(service('commands')), 'removeFile');
    }

    /**
     * @return Closure(): void
     */
    private function getClearCache(): Closure
    {
        return self::getPrivateMethodInvoker(new Optimize(service('commands')), 'clearCache');
    }

    public function testRemoveFileDeletesTheFile(): void
    {
        file_put_contents($this->file, '<?php');

        ($this->getRemoveFile())($this->file);

        $this->assertFileDoesNotExist($this->file);
        $this->assertStringContainsString('Removed', $this->getStreamFilterBuffer());
    }

    public function testRemoveFileDoesNothingWhenFileIsAbsent(): void
    {
        $this->assertFileDoesNotExist($this->file);

        ($this->getRemoveFile())($this->file);

        $this->assertSame('', $this->getStreamFilterBuffer());
    }

    public function testClearCacheDiscardsSharedLocatorCache(): void
    {
        $locator = new FileLocatorCached(new FileLocator(service('autoloader')), $this->handler);
        $locator->search('Config/App');
        Services::injectMock('locator', $locator);

        ($this->getClearCache())();

        $locator->search('Config/Cache');
        $locator->__destruct();

        $cached = $this->handler->get('FileLocatorCache');

        $this->assertArrayNotHasKey('Config/App', $cached['search']);
        $this->assertArrayHasKey('Config/Cache', $cached['search']);
        $this->assertStringContainsString('Removed FileLocatorCache.', $this->getStreamFilterBuffer());
    }

    public function testClearCacheDeletesTheFileWithoutSharedLocatorCache(): void
    {
        $this->handler->save('FileLocatorCache', ['search' => []]);

        ($this->getClearCache())();

        $this->assertFalse($this->handler->get('FileLocatorCache'));
        $this->assertStringContainsString('Removed FileLocatorCache.', $this->getStreamFilterBuffer());
    }
}
