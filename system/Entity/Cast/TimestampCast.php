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

use CodeIgniter\Entity\Exceptions\CastException;

class TimestampCast extends BaseCast
{
    public static function get($value, array $params = [])
    {
        $value = strtotime($value);

        if ($value === false) {
            throw CastException::forInvalidTimestamp();
        }

        return $value;
    }
}
