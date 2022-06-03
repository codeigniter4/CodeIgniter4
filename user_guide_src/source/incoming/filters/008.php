<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public $methods = [
        'post' => ['foo', 'bar'],
        'get'  => ['baz'],
    ];

    // ...
}
