<?php

namespace CodeIgniter\Commands;

use CodeIgniter\Cache\CacheFactory;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use Config\Services;

/**
 * @internal
 */
final class InfoCacheTest extends CIUnitTestCase
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

        // restore default cache handler
        config('Cache')->handler = 'file';
    }

    protected function getBuffer()
    {
        return CITestStreamFilter::$buffer;
    }

    public function testInfoCacheErrorsOnInvalidHandler()
    {
        config('Cache')->handler = 'redis';
        cache()->save('foo', 'bar');
        command('cache:info');

        $this->assertStringContainsString('This command only supports the file cache handler.', $this->getBuffer());
    }

    public function testInfoCacheCanSeeFoo()
    {
        cache()->save('foo', 'bar');
        command('cache:info');

        $this->assertStringContainsString('foo', $this->getBuffer());
    }

    public function testInfoCacheCanSeeTable()
    {
        command('cache:info');

        $this->assertStringContainsString('Name', $this->getBuffer());
        $this->assertStringContainsString('Server Path', $this->getBuffer());
        $this->assertStringContainsString('Size', $this->getBuffer());
        $this->assertStringContainsString('Date', $this->getBuffer());
    }

    public function testInfoCacheCannotSeeFoo()
    {
        cache()->delete('foo');
        command('cache:info');

        $this->assertStringNotContainsString('foo', $this->getBuffer());
    }
}
