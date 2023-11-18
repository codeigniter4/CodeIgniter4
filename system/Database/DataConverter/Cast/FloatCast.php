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
 * Class FloatCast
 *
 * DB column: float <--> PHP: float
 */
class FloatCast extends BaseCast
{
    /**
     * {@inheritDoc}
     */
    public static function fromDatabase($value, array $params = []): float
    {
        return (float) $value;
    }
}
