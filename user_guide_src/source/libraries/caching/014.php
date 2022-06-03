<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Cache extends BaseConfig
{
    // ...

    public $redis = [
        'host'     => '127.0.0.1',
        'password' => null,
        'port'     => 6379,
        'timeout'  => 0,
        'database' => 0,
    ];

    // ...
}
