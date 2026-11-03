<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    // ...

    public $filters = [
        'group:admin,superadmin'  => ['before' => ['admin/*']],
        'permission:users.manage' => ['before' => ['admin/users/*']],
    ];

    // ...
}
