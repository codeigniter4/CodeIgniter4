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

namespace CodeIgniter\DataCaster\Cast;

interface CastInterface
{
    /**
     * Takes a value from DataSource, returns its value for PHP.
     *
     * @param mixed        $value  Data from database driver
     * @param list<string> $params Additional param
     * @param object|null  $helper Helper object. E.g., database connection
     *
     * @return mixed PHP native value
     */
    public static function get(
        mixed $value,
        array $params = [],
        ?object $helper = null
    ): mixed;

    /**
     * Takes a PHP value, returns its value for DataSource.
     *
     * @param mixed        $value  PHP native value
     * @param list<string> $params Additional param
     * @param object|null  $helper Helper object. E.g., database connection
     *
     * @return mixed Data to pass to database driver
     */
    public static function set(
        mixed $value,
        array $params = [],
        ?object $helper = null
    ): mixed;
}
