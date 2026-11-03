<?php

// app/Config/Example.php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Example extends BaseConfig
{
    public array $arrayNested = [
        'key1' => 'val1',
        'key2' => ['val2' => 'subVal2', 'val3' => 'subVal3'],
    ];
}

// Modules/MyModule/Config/Registrar.php - plain array (shallow merge)

namespace MyModule\Config;

class Registrar
{
    public static function Example(): array
    {
        return ['arrayNested' => ['key2' => ['val4' => 'subVal4']]];
    }
}

// Result - the nested array under "key2" is replaced wholesale, so
// "val2" and "val3" are silently dropped:
//
// 'arrayNested' => [
//     'key1' => 'val1',
//     'key2' => ['val4' => 'subVal4'],
// ]
