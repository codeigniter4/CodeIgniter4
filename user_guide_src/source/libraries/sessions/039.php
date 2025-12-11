<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Session\Handlers\DatabaseHandler;

class Session extends BaseConfig
{
    // ...
    public string $driver = DatabaseHandler::class;

    // ...
    public string $savePath = 'ci_sessions';

    // ...
}
