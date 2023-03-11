<?php

namespace Config;

use CodeIgniter\Config\AutoloadConfig;

class Autoload extends AutoloadConfig
{
    // ...
    public $classmap = [
        'Markdown' => APPPATH . 'ThirdParty/markdown.php',
    ];

    // ...
}
