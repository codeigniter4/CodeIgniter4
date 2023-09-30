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
 * Class BooleanCast
 */
class BooleanCast extends BaseCast
{
    /**
     * {@inheritDoc}
     */
    public static function set($value, array $params = []): bool
    {
        return (bool) $value;
    }

    /**
     * {@inheritDoc}
     */
    public static function fromDatabase($value, array $params = []): bool
    {
        if (! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        return (bool) $value;
    }
}
