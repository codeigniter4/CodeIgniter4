<?php namespace CodeIgniter\Commands;

use CodeIgniter\Cache\CacheFactory;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use Config\Services;

class ClearCacheTest extends CIUnitTestCase
{
	protected $streamFilter;
	protected $result;

	protected function setUp(): void
	{
		parent::setUp();

		CITestStreamFilter::$buffer = '';
		$this->streamFilter         = stream_filter_append(STDOUT, 'CITestStreamFilter');
		$this->streamFilter         = stream_filter_append(STDERR, 'CITestStreamFilter');

		// Make sure we are testing with the correct handler (override injections)
		Services::injectMock('cache', CacheFactory::getHandler(config('Cache')));
	}

	public function tearDown(): void
	{
		if (! $this->result)
		{
			return;
		}

		stream_filter_remove($this->streamFilter);
	}

	public function testClearCacheInvalidHandler()
	{
		command('cache:clear junk');
		$result = CITestStreamFilter::$buffer;

		$this->assertStringContainsString('junk is not a valid cache handler.', $result);
	}

	public function testClearCacheWorks()
	{
		cache()->save('foo', 'bar');

		$this->assertEquals('bar', cache('foo'));

		command('cache:clear');
		$result = CITestStreamFilter::$buffer;

		$this->assertNull(cache('foo'));

		$this->assertStringContainsString('Done', $result);
	}
}
