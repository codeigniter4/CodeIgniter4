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

namespace CodeIgniter\Commands\Cache;

use CodeIgniter\Cache\CacheFactory;
use CodeIgniter\Cache\Handlers\FileHandler;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Cache;
use Config\Services;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class ClearCacheTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    private Cache $config;

    protected function setUp(): void
    {
        parent::setUp();

        CLI::reset();
        $this->resetServices();

        $this->config                    = new Cache();
        $this->config->file['storePath'] = rtrim($this->config->file['storePath'], DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . 'FileHandlerCommands';

        if (! is_dir($this->config->file['storePath'])) {
            mkdir($this->config->file['storePath'], 0777, true);
        }

        Factories::injectMock('config', Cache::class, $this->config);

        // Make sure we are testing with the correct handler (override injections)
        $handler = CacheFactory::getHandler($this->config);
        $handler->clean();
        Services::injectMock('cache', $handler);
    }

    protected function tearDown(): void
    {
        $this->config->handler = 'file';

        if (is_dir($this->config->file['storePath'])) {
            CacheFactory::getHandler($this->config)->clean();
            rmdir($this->config->file['storePath']);
        }

        CLI::reset();
        $this->resetFactories();
        $this->resetServices();

        parent::tearDown();
    }

    public function testClearCacheInvalidHandler(): void
    {
        command('cache:clear junk');

        $this->assertSame(
            "\nCache driver \"junk\" is not a valid cache handler.\n",
            preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()),
        );
    }

    public function testClearCacheWorks(): void
    {
        cache()->save('foo', 'bar');
        $this->assertSame('bar', cache('foo'));

        command('cache:clear');

        $this->assertNull(cache('foo'));
        $this->assertStringContainsString('Cache cleared.', $this->getStreamFilterBuffer());
    }

    public function testClearCacheFails(): void
    {
        $cache = $this->getMockBuilder(FileHandler::class)
            ->setConstructorArgs([$this->config])
            ->onlyMethods(['clean'])
            ->getMock();
        $cache->expects($this->once())->method('clean')->willReturn(false);

        Services::injectMock('cache', $cache);

        command('cache:clear');
        Services::resetSingle('cache');

        $this->assertSame(
            "\nError while clearing the cache.\n",
            preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()),
        );
    }
}
