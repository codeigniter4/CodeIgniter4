<?php

namespace CodeIgniter\Shield\Config;

use CodeIgniter\Shield\Filters\SessionAuth;
use CodeIgniter\Shield\Filters\TokenAuth;

class Registrar
{
    /**
     * Registers the Shield filters.
     */
    public static function Filters(): array
    {
        return [
            'aliases' => [
                'session' => SessionAuth::class,
                'tokens'  => TokenAuth::class,
            ],
        ];
    }
}
