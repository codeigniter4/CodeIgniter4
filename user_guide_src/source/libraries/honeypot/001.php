<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    // ...

    public $globals = [
        'before' => [
            'honeypot',
            // 'csrf',
            // 'invalidchars',
        ],
        'after' => [
            'toolbar',
            'honeypot',
            // 'secureheaders',
        ],
    ];

    // ...
}
