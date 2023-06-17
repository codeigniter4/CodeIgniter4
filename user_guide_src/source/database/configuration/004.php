<?php

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    // ...

    // MySQLi
    public array $default = [
        'DSN' => 'MySQLi://username:password@hostname:3306/database?charset=utf8&DBCollat=utf8_general_ci',
        // ...
    ];

    // ...
}
