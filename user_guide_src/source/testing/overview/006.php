<?php

namespace App\Models;

use CodeIgniter\Test\CIUnitTestCase;

final class OneOfMyModelsTest extends CIUnitTestCase
{
    protected $tearDownMethods = [
        'purgeRows',
    ];

    protected function purgeRows()
    {
        $this->model->purgeDeleted();
    }
}
