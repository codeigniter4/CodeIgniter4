<?php

namespace CodeIgniter\Cache;

use CodeIgniter\Cache\Handlers\BaseHandler;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockCache;

class CacheMockTest extends CIUnitTestCase
{
	public function testMockReturnsMockCacheClass()
	{
		// Baseline test
		$this->assertInstanceOf(BaseHandler::class, service('cache'));

		$mock = mock(CacheFactory::class);

		// Should return MockCache class
		$this->assertInstanceOf(MockCache::class, $mock);

		// Should inject MockCache
		$this->assertInstanceOf(MockCache::class, service('cache'));
	}

	public function testMockCaching()
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
