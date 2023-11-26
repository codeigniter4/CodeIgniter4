<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Session\Handlers\FileHandler;

class Session extends BaseConfig
{
    // ...
    public string $driver = 'CodeIgniter\Session\Handlers\MemcachedHandler';

    // ...
    public string $savePath = 'localhost:11211';

    // ...
}
