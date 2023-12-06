<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\Test;

class TestForReflectionHelper
{
    private string $private               = 'secret';
    private static string $static_private = 'xyz';

    public function getPrivate()
    {
        return $this->private;
    }

    public static function getStaticPrivate()
    {
        return self::$static_private;
    }

    private function privateMethod($param1, $param2)
    {
        return 'private ' . $param1 . $param2;
    }

    private static function privateStaticMethod($param1, $param2)
    {
        return 'private_static ' . $param1 . $param2;
    }
}
