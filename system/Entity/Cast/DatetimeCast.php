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

namespace CodeIgniter\Entity\Cast;

use CodeIgniter\I18n\Time;
use DateTimeInterface;
use Exception;

class DatetimeCast extends BaseCast
{
    /**
     * {@inheritDoc}
     *
     * @return Time
     *
     * @throws Exception
     */
    public static function get($value, array $params = [])
    {
        if ($value instanceof Time) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return Time::createFromInstance($value);
        }

        if (is_numeric($value)) {
            return Time::createFromTimestamp((int) $value, date_default_timezone_get());
        }

        if (is_string($value)) {
            return Time::parse($value);
        }

        return $value;
    }
}
