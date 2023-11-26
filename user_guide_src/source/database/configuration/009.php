<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{
    // ...

    public function __construct()
    {
        // ...

        $array = json_decode($this->default['encrypt'], true);
        if (is_array($array)) {
            $this->default['encrypt'] = $array;
        }
    }
}
