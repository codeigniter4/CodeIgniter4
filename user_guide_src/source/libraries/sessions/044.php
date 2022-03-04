<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    // localhost will be given higher priority (5) here,
    // compared to 192.0.2.1 with a weight of 1.
    public $sessionSavePath = 'localhost:11211:5,192.0.2.1:11211:1';

    // ...
}
