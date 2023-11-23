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
 * Class IntegerCast
 *
 * PHP: int <--> DB column: int
 *
 * @extends BaseCast<int, int, int|string>
 */
class IntegerCast extends BaseCast
{
    /**
     * {@inheritDoc}
     */
    public static function fromDatabase(mixed $value, array $params = []): int
    {
        if (! is_string($value) && ! is_int($value)) {
            self::invalidTypeValueError($value);
        }

        return (int) $value;
    }
}
