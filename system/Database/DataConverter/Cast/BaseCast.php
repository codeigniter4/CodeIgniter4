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

use TypeError;

/**
 * Class BaseCast
 */
abstract class BaseCast implements CastInterface
{
    /**
     * {@inheritDoc}
     */
    public static function fromDatabase($value, array $params = [])
    {
        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public static function toDatabase($value, array $params = [])
    {
        return $value;
    }

    /**
     * Throws TypeError
     */
    protected static function invalidTypeValueError(mixed $value): never
    {
        throw new TypeError('Invalid type value: ' . var_export($value, true));
    }
}
