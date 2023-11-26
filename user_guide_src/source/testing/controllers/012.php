<?php

namespace CodeIgniter;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FilterTestTrait;

final class FilterTestCase extends CIUnitTestCase
{
    use FilterTestTrait;

    protected function testFilterFailsOnAdminRoute()
    {
        $this->filtersConfig->globals['before'] = ['admin-only-filter'];

        $this->assertHasFilters('unfiltered/route', 'before');
    }

    // ...
}
