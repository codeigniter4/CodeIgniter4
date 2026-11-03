<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Cache extends BaseConfig
{
    // ...

    public $redis = [
        'host'       => '127.0.0.1',
        'password'   => null,
        'port'       => 6379,
        'async'      => false, // specific to Predis and ignored by the native Redis extension
        'persistent' => false,
        'timeout'    => 0,
        'database'   => 0,
    ];

    // ...
}
