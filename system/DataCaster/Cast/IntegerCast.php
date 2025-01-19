<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\DataCaster\Cast;

/**
 * Class IntegerCast
 *
 * (PHP) [int --> int       ] --> (DB driver) --> (DB column) int
 *       [    <-- int|string] <-- (DB driver) <-- (DB column) int
 */
class IntegerCast extends BaseCast
{
    public static function get(
        mixed $value,
        array $params = [],
        ?object $helper = null,
    ): int {
        if (! is_string($value) && ! is_int($value)) {
            self::invalidTypeValueError($value);
        }

        return (int) $value;
    }
}
