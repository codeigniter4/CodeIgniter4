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
final class InfoCacheTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    private Cache $config;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->resetFactories();
        $this->resetServices();

        parent::tearDown();
    }

    protected function getBuffer(): string
    {
        return $this->getStreamFilterBuffer();
    }

    public function testInfoCacheErrorsOnInvalidHandler(): void
    {
        $this->config->handler = 'redis';
        cache()->save('foo', 'bar');
        command('cache:info');

        $this->assertStringContainsString('This command only supports the file cache handler.', $this->getBuffer());
    }

    public function testInfoCacheCanSeeFoo(): void
    {
        cache()->save('foo', 'bar');
        command('cache:info');

        $this->assertStringContainsString('foo', $this->getBuffer());
    }

    public function testInfoCacheCanSeeTable(): void
    {
        command('cache:info');

        $this->assertStringContainsString('Name', $this->getBuffer());
        $this->assertStringContainsString('Server Path', $this->getBuffer());
        $this->assertStringContainsString('Size', $this->getBuffer());
        $this->assertStringContainsString('Date', $this->getBuffer());
    }

    public function testInfoCacheCannotSeeFoo(): void
    {
        cache()->delete('foo');
        command('cache:info');

        $this->assertStringNotContainsString('foo', $this->getBuffer());
    }
}
