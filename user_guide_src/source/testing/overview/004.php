<?php

namespace App\Models;

use CodeIgniter\Test\CIUnitTestCase;

final class OneOfMyModelsTest extends CIUnitTestCase
{
    protected $setUpMethods = [
        'mockEmail',
        'mockSession',
    ];

    protected $tearDownMethods = [];
}
