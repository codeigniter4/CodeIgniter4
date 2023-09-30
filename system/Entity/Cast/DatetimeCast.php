<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Entity\Cast;

use CodeIgniter\I18n\Time;
use DateTime;
use Exception;

/**
 * Class DatetimeCast
 */
class DatetimeCast extends BaseCast
{
    /**
     * {@inheritDoc}
     *
     * @return Time
     *
     * @throws Exception
     */
    public static function set($value, array $params = [])
    {
        if ($value instanceof Time) {
            return $value;
        }

        if ($value instanceof DateTime) {
            return Time::createFromInstance($value);
        }

        if (is_numeric($value)) {
            return Time::createFromTimestamp($value);
        }

        if (is_string($value)) {
            return Time::parse($value);
        }

        self::invalidTypeValueError($value);
    }

    /**
     * {@inheritDoc}
     *
     * @return Time
     *
     * @throws Exception
     */
    public static function fromDatabase($value, array $params = [])
    {
        if (is_string($value)) {
            return Time::parse($value);
        }

        self::invalidTypeValueError($value);
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
