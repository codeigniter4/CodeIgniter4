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
use Exception;

/**
 * Class DatetimeCast
 *
 * DB column: datetime <--> PHP: Time
 */
class DatetimeCast extends BaseCast
{
    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public static function fromDatabase($value, array $params = []): Time
    {
        if (! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        return Time::parse($value);
    }

    /**
     * {@inheritDoc}
     */
    public static function toDatabase($value, array $params = []): string
    {
        if (! $value instanceof Time) {
            self::invalidTypeValueError($value);
        }

        return (string) $value;
    }
}
