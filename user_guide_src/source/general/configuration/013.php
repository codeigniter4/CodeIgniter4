<?php

// Modules/MyModule/Config/Registrar.php - opt into a deep merge with Merge::byKey()

namespace MyModule\Config;

use CodeIgniter\Config\Merge;

class Registrar
{
    public static function Example(): array
    {
        return [
            'arrayNested' => Merge::byKey([
                'key2' => ['val4' => 'subVal4'],
            ]),
        ];
    }
}

// Result - the sibling keys are preserved:
//
// 'arrayNested' => [
//     'key1' => 'val1',
//     'key2' => ['val2' => 'subVal2', 'val3' => 'subVal3', 'val4' => 'subVal4'],
// ]
