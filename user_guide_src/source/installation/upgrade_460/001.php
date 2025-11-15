<?php

namespace CodeIgniter\Shield\Config;

use Config\App;

class Registrar
{
    public function __construct()
    {
        $config = new App(); // Bad. When this class is instantiated, Config\App will be instantiated.

        // Does something.
    }

    public static function Pager(): array
    {
        return [
            'templates' => [
                'module_pager' => 'MyModule\Views\Pager',
            ],
        ];
    }

    public static function hack(): void
    {
        $config = config('Cache');

        // Does something.
    }
}

Registrar::hack(); // Bad. When this class is loaded, Config\Cache will be instantiated.
