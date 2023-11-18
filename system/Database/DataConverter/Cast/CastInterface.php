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
 * Interface CastInterface
 */
interface CastInterface
{
    /**
     * Takes value from database, returns its value for PHP.
     *
     * @param bool|float|int|string|null $value  Data
     * @param array                      $params Additional param
     *
     * @return array|bool|float|int|object|string|null
     */
    public static function fromDatabase($value, array $params = []);

    /**
     * Takes the PHP value, returns its value for database.
     *
     * @param array|bool|float|int|object|string|null $value  Data
     * @param array                                   $params Additional param
     *
     * @return bool|float|int|string|null
     */
    public static function toDatabase($value, array $params = []);
}
