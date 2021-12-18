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
 * Class FloatCast
 */
class FloatCast extends BaseCast
{
    /**
     * {@inheritDoc}
     */
    public static function get($value, array $params = []): float
    {
        return (float) $value;
    }
}
