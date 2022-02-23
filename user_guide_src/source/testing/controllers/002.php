<?php

namespace CodeIgniter;

use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class TestControllerA extends CIUnitTestCase
{
    use ControllerTestTrait, DatabaseTestTrait;

    public function testShowCategories()
    {
        $result = $this->withURI('http://example.com/categories')
                       ->controller(\App\Controllers\ForumController::class)
                       ->execute('showCategories');

        $this->assertTrue($result->isOK());
    }
}
