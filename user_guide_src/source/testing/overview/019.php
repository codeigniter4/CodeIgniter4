<?php

use CodeIgniter\Test\CIUnitTestCase;

final class SomeTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        CITestStreamFilter::$buffer = '';
        $this->stream_filter        = stream_filter_append(STDOUT, 'CITestStreamFilter');
    }

    protected function tearDown(): void
    {
        stream_filter_remove($this->stream_filter);
    }

    public function testSomeOutput(): void
    {
        CLI::write('first.');
        $expected = "first.\n";
        $this->assertSame($expected, CITestStreamFilter::$buffer);
    }
}
