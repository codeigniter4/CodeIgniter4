<?php

class Filters extends BaseConfig
{
    public $globals = [
        'before' => [
            'csrf' => ['except' => 'api/*'],
        ],
        'after' => [],
    ];
}
