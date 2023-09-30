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

/**
 * Class CSVCast
 */
class CSVCast extends BaseCast
{
    /**
     * {@inheritDoc}
     */
    public static function fromDatabase($value, array $params = []): array
    {
        if (! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        return explode(',', $value);
    }

    /**
     * {@inheritDoc}
     */
    public static function toDatabase($value, array $params = []): string
    {
        if (! is_array($value)) {
            self::invalidTypeValueError($value);
        }

        return implode(',', $value);
    }
}
