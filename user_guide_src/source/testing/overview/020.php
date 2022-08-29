<?php

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;

final class SomeTest extends CIUnitTestCase
{
    public function testSomeOutput(): void
    {
        CITestStreamFilter::registration();
        CITestStreamFilter::addOutputFilter();

        CLI::write('first.');

        CITestStreamFilter::removeOutputFilter();
    }
}
