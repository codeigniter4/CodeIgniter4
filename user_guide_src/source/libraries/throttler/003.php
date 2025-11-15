<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public $aliases = [
        // ...
        'throttle' => \App\Filters\Throttle::class,
    ];

    // ...
}
