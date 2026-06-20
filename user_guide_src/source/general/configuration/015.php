<?php

// Modules/MyModule/Config/Registrar.php - order filters relative to existing ones

namespace MyModule\Config;

use CodeIgniter\Config\Merge;

class Registrar
{
    public static function Filters(): array
    {
        return [
            'globals' => Merge::byKey([
                // Run "auth" immediately after "csrf" in the before-list.
                'before' => Merge::after('csrf', ['auth']),
                // Run "honeypot" first in the after-list.
                'after' => Merge::prepend(['honeypot']),
            ]),
        ];
    }
}

// Given a base of:
//   'before' => ['csrf', 'invalidchars'],
//   'after'  => ['toolbar'],
// the result is:
//   'before' => ['csrf', 'auth', 'invalidchars'],
//   'after'  => ['honeypot', 'toolbar'],
