<?php

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    // ...

    // MySQLi
    public array $default = [
        'DSN' => 'MySQLi://username:password@hostname:3306/database?charset=utf8mb4&DBCollat=utf8mb4_general_ci',
        // ...
    ];

    // ...
}
