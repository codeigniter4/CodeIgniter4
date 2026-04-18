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
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Services;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class ClearCacheTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function setUp(): void
    {
        parent::setUp();

        CLI::reset();
        $this->resetServices();

        // Make sure we are testing with the correct handler (override injections)
        Services::injectMock('cache', CacheFactory::getHandler(config('Cache')));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        CLI::reset();
        $this->resetServices();
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
            ->setConstructorArgs([config('Cache')])
            ->onlyMethods(['clean'])
            ->getMock();
        $cache->expects($this->once())->method('clean')->willReturn(false);

        Services::injectMock('cache', $cache);

        command('cache:clear');

        $this->assertSame(
            "\nError occurred while clearing the cache.\n",
            preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()),
        );
    }
}
