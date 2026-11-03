<?php

use CodeIgniter\Config\Factories;
use CodeIgniter\Filters\FilterInterface;

Factories::setOptions('filters', [
    'instanceOf' => FilterInterface::class,
    'prefersApp' => false,
]);
