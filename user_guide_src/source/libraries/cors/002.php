<?php

namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;

// ...

class Filters extends BaseFilters
{
    // ...
    public array $filters = [
        // ...
        'cors' => [
            'before' => ['api/*'],
            'after'  => ['api/*'],
        ],
    ];
}
