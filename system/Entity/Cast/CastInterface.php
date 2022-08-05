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
 * Interface CastInterface
 */
interface CastInterface
{
    /**
     * Get
     *
     * @param array|bool|float|int|object|string|null $value  Data
     * @param array                                   $params Additional param
     *
     * @return array|bool|float|int|object|string|null
     */
    public static function get($value, array $params = []);

    /**
     * Set
     *
     * @param array|bool|float|int|object|string|null $value  Data
     * @param array                                   $params Additional param
     *
     * @return array|bool|float|int|object|string|null
     */
    public static function set($value, array $params = []);
}
