<?php

namespace Config;

use CodeIgniter\Config\AutoloadConfig;

class Autoload extends AutoloadConfig
{
    public $psr4 = [
        APP_NAMESPACE => APPPATH, // For custom namespace
        'Config'      => APPPATH . 'Config',
        'Acme\Blog'   => ROOTPATH . 'acme/Blog',
    ];

    // ...
}
