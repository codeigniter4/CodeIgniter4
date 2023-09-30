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
 *
 * [App Code] --- set() --> [Entity] --- toDatabase() ---> [Database]
 * [App Code] <-- get() --- [Entity] <-- fromDatabase() -- [Database]
 */
interface CastInterface
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
    public static function get($value, array $params = []);

    /**
     * Returns value for the Entity property when setting the Entity property.
     *
     * @param array|bool|float|int|object|string|null $value  Data
     * @param array                                   $params Additional param
     *
     * @return array|bool|float|int|object|string|null
     */
    public static function set($value, array $params = []);

    /**
     * Takes the Entity property value, returns its value for database.
     *
     * @param array|bool|float|int|object|string|null $value  Data
     * @param array                                   $params Additional param
     *
     * @return bool|float|int|string|null
     */
    public static function toDatabase($value, array $params = []);

    /**
     * Takes value from database, returns its value for the Entity property.
     *
     * @param bool|float|int|string|null $value  Data
     * @param array                      $params Additional param
     *
     * @return array|bool|float|int|object|string|null
     */
    public static function fromDatabase($value, array $params = []);
}
