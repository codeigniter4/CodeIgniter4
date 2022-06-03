<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public $aliases = [
        'csrf' => \CodeIgniter\Filters\CSRF::class,
    ];

    // ...
}
