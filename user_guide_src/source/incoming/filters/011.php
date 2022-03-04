<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public $aliases = [
        // ...
        'secureheaders' => \App\Filters\SecureHeaders::class,
    ];

    // ...
}
