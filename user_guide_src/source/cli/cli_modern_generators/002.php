<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Generators extends BaseConfig
{
    public array $views = [
        // ..
        'make:widget' => 'App\Commands\Generators\Views\widget.tpl.php',
    ];
}
