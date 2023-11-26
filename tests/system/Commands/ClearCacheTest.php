<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Commands;

use CodeIgniter\Cache\CacheFactory;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Services;

/**
 * @internal
 *
 * @group Others
 */
final class ClearCacheTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function setUp(): void
    {
        parent::setUp();

        // Make sure we are testing with the correct handler (override injections)
        Services::injectMock('cache', CacheFactory::getHandler(config('Cache')));
    }

    public function testClearCacheInvalidHandler(): void
    {
        command('cache:clear junk');

        $this->assertStringContainsString('junk is not a valid cache handler.', $this->getStreamFilterBuffer());
    }

    public function testClearCacheWorks(): void
    {
        cache()->save('foo', 'bar');
        $this->assertSame('bar', cache('foo'));

        command('cache:clear');

        $this->assertNull(cache('foo'));
        $this->assertStringContainsString('Cache cleared.', $this->getStreamFilterBuffer());
    }
}
