<?php

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\Filters\CITestStreamFilter;

public function testSomeOutput(): void
{
    CITestStreamFilter::registration();
    CITestStreamFilter::addOutputFilter();

    CLI::write('first.');

    CITestStreamFilter::removeOutputFilter();
}

