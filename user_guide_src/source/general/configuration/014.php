<?php

// Modules/MyModule/Config/Registrar.php - nested directives inside Merge::byKey()

namespace MyModule\Config;

use CodeIgniter\Config\Merge;

class Registrar
{
    public static function Filters(): array
    {
        return [
            'globals' => Merge::byKey([
                'before' => Merge::append(['blogFilter']), // add to the existing list
                'after'  => Merge::replace([]),            // hard reset, plain [] would keep merging
            ]),
        ];
    }

    // Scalar replace also works at the property root:
    public static function Cache(): array
    {
        return ['handler' => Merge::replace('redis')];
    }
}
