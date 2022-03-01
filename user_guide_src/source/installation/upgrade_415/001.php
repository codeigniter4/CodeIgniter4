<?php

namespace Config;

// ...

class Filters extends BaseConfig
{
    // ...
    
    public $methods = [
        'get'  => ['csrf'],
        'post' => ['csrf'],
    ];
    
    // ...
}
