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

        $this->handler->delete('FileLocatorCache');
        Services::resetSingle('locator');
    }

    /**
     * @return Closure(): void
     */
    private function getClearCache(): Closure
    {
        return self::getPrivateMethodInvoker(new Optimize(service('logger'), service('commands')), 'clearCache');
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
