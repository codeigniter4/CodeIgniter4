<?php

namespace CodeIgniter;

use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class TestControllerA extends CIUnitTestCase
{
    use ControllerTestTrait, DatabaseTestTrait;
}
