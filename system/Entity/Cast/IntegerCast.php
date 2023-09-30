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
 * Class IntegerCast
 */
class IntegerCast extends BaseCast
{
    /**
     * {@inheritDoc}
     */
    public static function set($value, array $params = []): int
    {
        return (int) $value;
    }

    /**
     * {@inheritDoc}
     */
    public static function fromDatabase($value, array $params = []): int
    {
        if (! is_string($value) && ! is_int($value)) {
            self::invalidTypeValueError($value);
        }

        return (int) $value;
    }
}
