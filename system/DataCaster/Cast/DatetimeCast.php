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
    public static function get(mixed $value, array $params = []): Time
    {
        if (! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        /**
         * @see https://www.php.net/manual/en/datetimeimmutable.createfromformat.php#datetimeimmutable.createfromformat.parameters
         */
        $format = $params[0] ?? 'Y-m-d H:i:s';

        return Time::createFromFormat($format, $value);
    }

    public static function set(mixed $value, array $params = []): string
    {
        if (! $value instanceof Time) {
            self::invalidTypeValueError($value);
        }

        return (string) $value;
    }
}
