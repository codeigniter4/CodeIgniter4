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

use CodeIgniter\HTTP\URI;

/**
 * Class URICast
 *
 * (PHP) [URI --> string] --> (DB driver) --> (DB column) string
 *       [    <-- string] <-- (DB driver) <-- (DB column) string
 *
 * @extends BaseCast<URI, string, mixed>
 */
class URICast extends BaseCast
{
    public static function fromDatabase(mixed $value, array $params = []): URI
    {
        if (! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        return new URI($value);
    }

    public static function toDatabase(mixed $value, array $params = []): string
    {
        if (! $value instanceof URI) {
            self::invalidTypeValueError($value);
        }

        return (string) $value;
    }
}
