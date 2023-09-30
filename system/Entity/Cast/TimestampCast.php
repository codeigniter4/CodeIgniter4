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

use CodeIgniter\Entity\Exceptions\CastException;

/**
 * Class TimestampCast
 */
class TimestampCast extends BaseCast
{
    /**
     * {@inheritDoc}
     *
     * @return int
     */
    public static function set($value, array $params = [])
    {
        if (! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        $value = strtotime($value);

        if ($value === false) {
            throw CastException::forInvalidTimestamp();
        }

        return $value;
    }
}
