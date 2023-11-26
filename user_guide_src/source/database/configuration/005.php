<?php

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    // ...

    public array $default = [
        // ...
        'failover' => [
            [
                'hostname' => 'localhost1',
                'username' => '',
                'password' => '',
                'database' => '',
                'DBDriver' => 'MySQLi',
                'DBPrefix' => '',
                'pConnect' => true,
                'DBDebug'  => true,
                'charset'  => 'utf8',
                'DBCollat' => 'utf8_general_ci',
                'swapPre'  => '',
                'encrypt'  => false,
                'compress' => false,
                'strictOn' => false,
            ],
            [
                'hostname' => 'localhost2',
                'username' => '',
                'password' => '',
                'database' => '',
                'DBDriver' => 'MySQLi',
                'DBPrefix' => '',
                'pConnect' => true,
                'DBDebug'  => true,
                'charset'  => 'utf8',
                'DBCollat' => 'utf8_general_ci',
                'swapPre'  => '',
                'encrypt'  => false,
                'compress' => false,
                'strictOn' => false,
            ],
        ],
        // ...
    ];

    // ...
}
