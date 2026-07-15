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
use DateTimeInterface;
use Exception;

/**
 * Class TimestampCast
 *
 * (PHP) [Time --> int       ] --> (DB driver) --> (DB column) int
 *       [     <-- int|string] <-- (DB driver) <-- (DB column) int
 */
class TimestampCast extends BaseCast
{
    public static function get(
        mixed $value,
        array $params = [],
        ?object $helper = null,
    ): Time {
        if (! is_int($value) && ! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        return Time::createFromTimestamp((int) $value, date_default_timezone_get());
    }

    public static function set(
        mixed $value,
        array $params = [],
        ?object $helper = null,
    ): int {
        if (is_string($value)) {
            try {
                $value = Time::parse($value);
            } catch (Exception) {
                self::invalidTypeValueError($value);
            }
        }

        if (! $value instanceof DateTimeInterface) {
            self::invalidTypeValueError($value);
        }

        return $value->getTimestamp();
    }
}
