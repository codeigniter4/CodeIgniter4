<?php

namespace Config;

// ...

class Cache extends BaseConfig
{
    // ...
    public $memcached = [
        'host'   => '127.0.0.1',
        'port'   => 11211,
        'weight' => 1,
        'raw'    => false,
    ];
    
    // ...
}
