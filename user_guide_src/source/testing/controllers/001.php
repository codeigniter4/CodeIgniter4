<?php

namespace CodeIgniter;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

class TestControllerA extends CIUnitTestCase
{
    use ControllerTestTrait;
    use DatabaseTestTrait;
}
