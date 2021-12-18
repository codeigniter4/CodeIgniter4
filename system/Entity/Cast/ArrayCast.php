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
 * Class ArrayCast
 */
class ArrayCast extends BaseCast
{
    /**
     * {@inheritDoc}
     */
    public static function get($value, array $params = []): array
    {
        if (is_string($value) && (strpos($value, 'a:') === 0 || strpos($value, 's:') === 0)) {
            $value = unserialize($value);
        }

        return (array) $value;
    }

    /**
     * {@inheritDoc}
     */
    public static function set($value, array $params = []): string
    {
        return serialize($value);
    }
}
