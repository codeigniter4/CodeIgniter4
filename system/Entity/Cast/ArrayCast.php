<?php

declare(strict_types=1);

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
        if (is_string($value) && (str_starts_with($value, 'a:') || str_starts_with($value, 's:'))) {
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
