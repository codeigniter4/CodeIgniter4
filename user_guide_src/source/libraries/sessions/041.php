<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Session\Handlers\RedisHandler;

class Session extends BaseConfig
{
    // ...
    public string $driver = RedisHandler::class;

    // ...
    public string $savePath = 'tcp://localhost:6379';

    // ...
}
