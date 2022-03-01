<?php

namespace Config;

// ...

class Filters extends BaseConfig
{
    // ...
    
    public $globals = [
        'before' => [
            // 'honeypot',
            'csrf',
        ],
    ];
    
    // ...
}
