<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public array $aliases = [
        'api-prep' => [
            \App\Filters\Negotiate::class,
            \App\Filters\ApiAuth::class,
        ],
    ];

    // ...
}
