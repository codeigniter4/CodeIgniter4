<?php

namespace CodeIgniter\Commands;

use CodeIgniter\Cache\CacheFactory;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use Config\Services;

/**
 * @internal
 */
final class ClearCacheTest extends CIUnitTestCase
{
    protected $streamFilter;

    protected function setUp(): void
    {
        parent::setUp();

        CITestStreamFilter::$buffer = '';
        $this->streamFilter         = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter         = stream_filter_append(STDERR, 'CITestStreamFilter');

        // Make sure we are testing with the correct handler (override injections)
        Services::injectMock('cache', CacheFactory::getHandler(config('Cache')));
    }

    protected function tearDown(): void
    {
        stream_filter_remove($this->streamFilter);
    }

    public function testClearCacheInvalidHandler()
    {
        command('cache:clear junk');

        $this->assertStringContainsString('junk is not a valid cache handler.', CITestStreamFilter::$buffer);
    }

    public function testClearCacheWorks()
    {
        cache()->save('foo', 'bar');
        $this->assertSame('bar', cache('foo'));

        command('cache:clear');

        $this->assertNull(cache('foo'));
        $this->assertStringContainsString('Cache cleared.', CITestStreamFilter::$buffer);
    }
}
