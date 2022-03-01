<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    public $sessionDriver   = 'CodeIgniter\Session\Handlers\DatabaseHandler';
    public $sessionSavePath = 'ci_sessions';
    // ...
}
