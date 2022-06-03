<?php

namespace App;

use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class TestFoo extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->myClassMethod();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->anotherClassMethod();
    }
}
