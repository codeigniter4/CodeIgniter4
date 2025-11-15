<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Format extends BaseConfig
{
    public $supportedResponseFormats = [
        'application/json',
        'application/xml',
    ];

    // ...
}
