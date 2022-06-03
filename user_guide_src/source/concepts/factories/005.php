<?php

namespace Config;

use CodeIgniter\Config\Factory as BaseFactory;
use CodeIgniter\Filters\FilterInterface;

class Factory extends BaseFactory
{
    public $filters = [
        'instanceOf' => FilterInterface::class,
    ];
}
