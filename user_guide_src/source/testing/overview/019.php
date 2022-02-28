<?php

final class Sometest extends CIUnitTestCase
{
    protected function setUp()
    {
        CITestStreamFilter::$buffer = '';
        $this->stream_filter        = stream_filter_append(STDOUT, 'CITestStreamFilter');
    }

    protected function tearDown()
    {
        stream_filter_remove($this->stream_filter);
    }

    public function testSomeOutput()
    {
        CLI::write('first.');
        $expected = "first.\n";
        $this->assertSame($expected, CITestStreamFilter::$buffer);
    }
}
