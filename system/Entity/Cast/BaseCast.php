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
 * Class BaseCast
 */
abstract class BaseCast implements CastInterface
{
    /**
     * Returns value when getting the Entity property.
     * This method is normally returns the value as it is.
     *
     * @param array|bool|float|int|object|string|null $value  Data
     * @param array                                   $params Additional param
     *
     * @return array|bool|float|int|object|string|null
     */
    public static function get($value, array $params = [])
    {
        return $value;
    }

    /**
     * Returns value for Entity property when setting the Entity property.
     *
     * @param array|bool|float|int|object|string|null $value  Data
     * @param array                                   $params Additional param
     *
     * @return array|bool|float|int|object|string|null
     */
    public static function set($value, array $params = [])
    {
        return $value;
    }

    /**
     * Takes the Entity property value, returns its value for database.
     *
     * @param array|bool|float|int|object|string|null $value  Data
     * @param array                                   $params Additional param
     *
     * @return bool|float|int|string|null
     */
    public static function toDatabase($value, array $params = [])
    {
        return $value;
    }

    /**
     * Takes value from database, returns its value for the Entity property.
     *
     * @param bool|float|int|string|null $value  Data
     * @param array                      $params Additional param
     *
     * @return array|bool|float|int|object|string|null
     */
    public static function fromDatabase($value, array $params = [])
    {
        return $value;
    }
}
