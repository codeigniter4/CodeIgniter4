<?php

namespace Config;

use CodeIgniter\Config\Factory as BaseFactory;
use CodeIgniter\Filters\FilterInterface;

class Factories extends BaseFactory
{
    public $filters = [
        'instanceOf' => FilterInterface::class,
    ];
    // ...
}
