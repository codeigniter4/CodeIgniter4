<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Session\Handlers\FileHandler;

class Session extends BaseConfig
{
    // ...

    // localhost will be given higher priority (5) here,
    // compared to 192.0.2.1 with a weight of 1.
    public string $savePath = 'localhost:11211:5,192.0.2.1:11211:1';

    // ...
}
