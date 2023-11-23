<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\DataConverter\Cast;

/**
 * Int Bool Cast
 *
 * (PHP) [bool --> int       ] --> (DB driver) --> (DB column) int(0/1)
 *       [     <-- int|string] <-- (DB driver) <-- (DB column) int(0/1)
 *
 * @extends BaseCast<bool, int, mixed>
 */
final class IntBoolCast extends BaseCast
{
    public static function fromDataSource(mixed $value, array $params = []): bool
    {
        if (! is_int($value) && ! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        return (bool) $value;
    }

    public static function toDataSource(mixed $value, array $params = []): int
    {
        if (! is_bool($value)) {
            self::invalidTypeValueError($value);
        }

        return (int) $value;
    }
}
