<?php

namespace App\Database;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class MyTests extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh  = true;
    protected $seed     = 'TestSeeder';
    protected $basePath = 'path/to/database/files';
}