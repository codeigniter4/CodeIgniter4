<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Cache;

use CodeIgniter\Cache\Handlers\BaseHandler;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockCache;

/**
 * @internal
 *
 * @group Others
 */
final class CacheMockTest extends CIUnitTestCase
{
    public function testMockReturnsMockCacheClass(): void
    {
        $this->assertInstanceOf(BaseHandler::class, service('cache'));

        $mock = mock(CacheFactory::class);
        $this->assertInstanceOf(MockCache::class, $mock);
        $this->assertInstanceOf(MockCache::class, service('cache'));
    }

    public function testMockCaching(): void
    {
        $mock = mock(CacheFactory::class);

        // Ensure it stores the value normally
        $mock->save('foo', 'bar');
        $mock->assertHas('foo');
        $mock->assertHasValue('foo', 'bar');

        // Try it again with bypass on
        $mock->bypass();
        $mock->save('foo', 'bar');
        $mock->assertMissing('foo');
    }
}
