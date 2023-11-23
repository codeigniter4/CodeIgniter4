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

use CodeIgniter\I18n\Time;

/**
 * Class DatetimeCast
 *
 * (PHP) [Time --> string] --> (DB driver) --> (DB column) datetime
 *       [     <-- string] <-- (DB driver) <-- (DB column) datetime
 *
 * @extends BaseCast<Time, string, mixed>
 */
class DatetimeCast extends BaseCast
{
    public static function fromDataSource(mixed $value, array $params = []): Time
    {
        if (! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        return Time::parse($value);
    }

    public static function toDataSource(mixed $value, array $params = []): string
    {
        if (! $value instanceof Time) {
            self::invalidTypeValueError($value);
        }

        return (string) $value;
    }
}
