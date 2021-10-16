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
     * @throws Exception
     */
    public static function get($value, array $params = [])
    {
        if ($value instanceof Time) {
            return $value;
        }

        if ($value instanceof DateTime) {
            return Time::instance($value);
        }

        if (is_numeric($value)) {
            return Time::createFromTimestamp($value);
        }

        if (is_string($value)) {
            return Time::parse($value);
        }

        return $value;
    }
}
