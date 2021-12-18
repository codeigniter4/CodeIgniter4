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
 * Class BadRegistrar
 *
 * Doesn't provides a basic registrar class for testing BaseConfig registration functions,
 * because it doesn't return an associative array
 */
class BadRegistrar
{
    public static function RegistrarConfig()
    {
        return 'I am not worthy';
    }
}
