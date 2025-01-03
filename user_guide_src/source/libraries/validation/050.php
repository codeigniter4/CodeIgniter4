<?php

namespace Config;

use App\Validation\UserRules;
use CodeIgniter\Config\BaseConfig;

class Validation extends BaseConfig
{
    public array $ruleSets = [
        // ...
        UserRules::class,
    ];

    // ...
}
