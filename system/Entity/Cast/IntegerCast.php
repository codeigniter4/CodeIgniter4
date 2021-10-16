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
    public static function get($value, array $params = []): int
    {
        return (int) $value;
    }
}
