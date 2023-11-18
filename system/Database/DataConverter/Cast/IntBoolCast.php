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

/**
 * Int Bool Cast
 *
 * DB column: int(0/1) <--> PHP: bool
 */
final class IntBoolCast extends BaseCast
{
    /**
     * {@inheritDoc}
     */
    public static function fromDatabase($value, array $params = []): bool
    {
        if (! is_int($value) && ! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        return (bool) $value;
    }

    /**
     * {@inheritDoc}
     */
    public static function toDatabase($value, array $params = []): int
    {
        if (! is_bool($value)) {
            self::invalidTypeValueError($value);
        }

        return (int) $value;
    }
}
