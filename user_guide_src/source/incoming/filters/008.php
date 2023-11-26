<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    // ...

    public array $methods = [
        'post' => ['invalidchars', 'csrf'],
        'get'  => ['csrf'],
    ];

    // ...
}
