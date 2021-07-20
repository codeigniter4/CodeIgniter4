<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\View;

/**
 * Class SampleClass
 *
 * This class is only used to provide a reference point
 * during tests to make sure that things work as expected.
 */
class SampleClass
{
    public function index()
    {
        return 'Hello World';
    }

    public function hello()
    {
        return 'Hello';
    }

    public function echobox($params)
    {
        if (is_array($params)) {
            $params = implode(',', $params);
        }

        return $params;
    }

    public static function staticEcho($params)
    {
        if (is_array($params)) {
            $params = implode(',', $params);
        }

        return $params;
    }

    public function work($p1, $p2, $p4)
    {
        return 'Right on';
    }
}
