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
use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Services;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class InfoCacheTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function setUp(): void
    {
        parent::setUp();

        CLI::resetLastWrite();
        $this->resetServices();
        Services::injectMock('cache', CacheFactory::getHandler(config('Cache')));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        CLI::resetLastWrite();
        $this->resetServices();
        $this->resetFactories();
    }

    private function getUndecoratedBuffer(): string
    {
        return preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()) ?? '';
    }

    public function testInfoCacheErrorsOnInvalidHandler(): void
    {
        config('Cache')->handler = 'redis';

        command('cache:info');

        $this->assertSame(
            <<<'EOT'

                This command only supports the file cache handler.

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }

    public function testInfoCacheCanSeeFoo(): void
    {
        cache()->save('foo', 'bar');
        command('cache:info');

        $this->assertStringContainsString('foo', $this->getStreamFilterBuffer());
    }

    public function testInfoCacheCanSeeTheads(): void
    {
        command('cache:info');

        $this->assertMatchesRegularExpression(
            '/\|\sName[[:space:]]+\|\sServer Path[[:space:]]+\|\sSize[[:space:]]+\|\sDate[[:space:]]+\|/',
            $this->getUndecoratedBuffer(),
        );
    }

    public function testInfoCacheCannotSeeFoo(): void
    {
        cache()->save('foo', 'bar');
        command('cache:info');
        $this->assertStringContainsString('foo', $this->getStreamFilterBuffer());

        $this->resetStreamFilterBuffer();

        cache()->delete('foo');
        command('cache:info');
        $this->assertStringNotContainsString('foo', $this->getUndecoratedBuffer());
    }
}
