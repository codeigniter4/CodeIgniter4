<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    public $sessionDriver   = 'CodeIgniter\Session\Handlers\MemcachedHandler';
    public $sessionSavePath = 'localhost:11211';
    // ...
}
