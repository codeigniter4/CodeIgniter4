<?php

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;

final class SomeTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function setUp(): void
    {
        $this->registerStreamFilterClass()
            ->appendStreamOutputFilter()
            ->appendStreamErrorFilter();
    }

    protected function tearDown(): void
    {
        $this->removeStreamOutputFilter()
            ->removeStreamErrorFilter();
    }

    public function testSomeOutput(): void
    {
        $this->resetStreamFilterBuffer();

        CLI::write('first.');

        $expected = "first.\n";
        $this->assertSame($expected, $this->getStreamFilterBuffer());
    }
}
