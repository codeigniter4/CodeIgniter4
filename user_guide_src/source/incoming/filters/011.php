<?php

class Filters extends BaseConfig
{
    public $aliases = [
        // ...
        'secureheaders' => \App\Filters\SecureHeaders::class,
    ];
}
