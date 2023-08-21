<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    // ...

    public array $methods = [
        'post' => ['InvalidChars', 'csrf'],
        'get'  => ['csrf'],
    ];

    // ...
}
