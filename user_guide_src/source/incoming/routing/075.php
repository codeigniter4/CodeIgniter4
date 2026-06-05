<?php

namespace Config;

use CodeIgniter\Config\Routing as BaseRouting;

class Routing extends BaseRouting
{
    public array $placeholderSamples = [
        'code' => 'ABC123',
        'uuid' => '550e8400-e29b-41d4-a716-446655440000',
    ];
}
