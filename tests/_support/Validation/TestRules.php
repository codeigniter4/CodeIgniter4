<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\Validation;

class TestRules
{
    public function customError(string $str, ?string &$error = null)
    {
        $error = 'My lovely error';

        return false;
    }

    public function check_object_rule(object $value, ?string $fields, array $data = [])
    {
        $find = false;

        foreach ($value as $key => $val) {
            if ($key === 'first') {
                $find = true;
            }
        }

        return $find;
    }

    public function array_count($value, $count): bool
    {
        return is_array($value) && count($value) === (int) $count;
    }
}
