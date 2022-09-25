<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\Config;

/**
 * Class Registrar
 *
 * Provides a basic registrar class for testing BaseConfig registration functions.
 */
class TestRegistrar
{
    public static function RegistrarConfig()
    {
        return [
            'bar' => [
                'first',
                'second',
            ],
        ];
    }
}
