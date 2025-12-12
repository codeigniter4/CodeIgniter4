<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Session\Handlers\MemcachedHandler;

class Session extends BaseConfig
{
    // ...
    public string $driver = MemcachedHandler::class;

    // ...
    public string $savePath = 'localhost:11211';

    // ...
}
