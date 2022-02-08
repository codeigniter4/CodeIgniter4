<?php

namespace App\Database;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class MyTests extends CIUnitTestCase
{
    use DatabaseTestTrait;

    public function setUp()
    {
        parent::setUp();

        // Do something here....
    }

    public function tearDown()
    {
        parent::tearDown();

        // Do something here....
    }
}
