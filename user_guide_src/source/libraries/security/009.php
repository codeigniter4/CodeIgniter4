<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public $methods = [
        'get'  => ['csrf'],
        'post' => ['csrf'],
    ];

    // ...
}
