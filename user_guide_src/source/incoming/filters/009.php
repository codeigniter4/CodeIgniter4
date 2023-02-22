<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    // ...

    public array $filters = [
        'foo' => ['before' => ['admin/*'], 'after' => ['users/*']],
        'bar' => ['before' => ['api/*', 'admin/*']],
    ];

    // ...
}
