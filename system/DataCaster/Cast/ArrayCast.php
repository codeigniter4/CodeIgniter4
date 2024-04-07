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
 * Class ArrayCast
 *
 * (PHP) [array --> string] --> (DB driver) --> (DB column) string
 *       [      <-- string] <-- (DB driver) <-- (DB column) string
 */
class ArrayCast extends BaseCast implements CastInterface
{
    public static function get(
        mixed $value,
        array $params = [],
        ?object $helper = null
    ): array {
        if (! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        if ((str_starts_with($value, 'a:') || str_starts_with($value, 's:'))) {
            $value = unserialize($value, ['allowed_classes' => false]);
        }

        return (array) $value;
    }

    public static function set(
        mixed $value,
        array $params = [],
        ?object $helper = null
    ): string {
        return serialize($value);
    }
}
