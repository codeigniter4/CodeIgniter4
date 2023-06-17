<?php

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    // ...

    public array $test = [
        'DSN'      => '',
        'hostname' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'database_name',
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'pConnect' => true,
        'DBDebug'  => true,
        'charset'  => 'utf8',
        'DBCollat' => 'utf8_general_ci',
        'swapPre'  => '',
        'compress' => false,
        'encrypt'  => false,
        'strictOn' => false,
        'failover' => [],
    ];

    // ...
}
