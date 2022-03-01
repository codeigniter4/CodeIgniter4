<?php

namespace Config;

// ...

class Filters extends BaseConfig
{
    public $aliases = [
        'csrf' => \CodeIgniter\Filters\CSRF::class,
    ];
    
    // ...
}
