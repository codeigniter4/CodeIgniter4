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

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\I18n\Time;
use InvalidArgumentException;

/**
 * Class DatetimeCast
 *
 * (PHP) [Time --> string] --> (DB driver) --> (DB column) datetime
 *       [     <-- string] <-- (DB driver) <-- (DB column) datetime
 */
class DatetimeCast extends BaseCast
{
    public static function get(
        mixed $value,
        array $params = [],
        ?object $helper = null,
    ): Time {
        if (! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        if (! $helper instanceof BaseConnection) {
            $message = 'The parameter $helper must be BaseConnection.';

            throw new InvalidArgumentException($message);
        }

        /**
         * @see https://www.php.net/manual/en/datetimeimmutable.createfromformat.php#datetimeimmutable.createfromformat.parameters
         */
        $format = match ($params[0] ?? '') {
            ''      => $helper->dateFormat['datetime'],
            'ms'    => $helper->dateFormat['datetime-ms'],
            'us'    => $helper->dateFormat['datetime-us'],
            default => throw new InvalidArgumentException('Invalid parameter: ' . $params[0]),
        };

        return Time::createFromFormat($format, $value);
    }

    public static function set(
        mixed $value,
        array $params = [],
        ?object $helper = null,
    ): string {
        if (! $value instanceof Time) {
            self::invalidTypeValueError($value);
        }

        return (string) $value;
    }
}
