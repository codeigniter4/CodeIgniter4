<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Generators extends BaseConfig
{
    public array $views = [
        // ..
        'make:awesome-command' => 'App\Commands\Generators\Views\awesomecommand.tpl.php',
    ];
}
