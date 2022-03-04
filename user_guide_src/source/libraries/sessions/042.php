<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    public $sessionDiver    = 'CodeIgniter\Session\Handlers\RedisHandler';
    public $sessionSavePath = 'tcp://localhost:6379';
    // ...
}
