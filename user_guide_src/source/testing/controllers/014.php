<?php

namespace CodeIgniter;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FilterTestTrait;

final class FilterTestCase extends CIUnitTestCase
{
    use FilterTestTrait;

    protected function testUnauthorizedAccessRedirects()
    {
        $caller = $this->getFilterCaller('permission', 'before');
        $result = $caller('MayEditWidgets');

        $this->assertInstanceOf('CodeIgniter\HTTP\RedirectResponse', $result);
    }
}
