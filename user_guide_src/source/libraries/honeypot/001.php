<?php

class Filters extends BaseConfig
{
    public $globals = [
        'before' => [
            'honeypot',
            // 'csrf',
        ],
        'after' => [
            'toolbar',
            'honeypot',
        ],
    ];
}
