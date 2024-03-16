<?php

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    // ...

    // MySQLi over a socket
    public array $default = [
        // ...
        'hostname' => '/cloudsql/toolbox-tests:europe-north1:toolbox-db',
        // ...
        'DBDriver' => 'MySQLi',
        // ...
    ];

    // ...
}
