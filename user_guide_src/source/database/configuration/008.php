<?php

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    public $development = [/* ... */];
    public $test        = [/* ... */];
    public $production  = [/* ... */];

    public function __construct()
    {
        $this->defaultGroup = ENVIRONMENT;
    }
}
