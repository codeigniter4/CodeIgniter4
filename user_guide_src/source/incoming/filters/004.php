<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public array $aliases = [
        'apiPrep' => [
            \App\Filters\Negotiate::class,
            \App\Filters\ApiAuth::class,
        ],
    ];

    // ...
}
