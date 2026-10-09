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

/**
 * @template TPhpNativeValue
 * @template TDataSourceValue
 */
interface CastInterface
{
    /**
     * Takes a value from a data source, returns its value for PHP.
     *
     * @param TDataSourceValue   $value  Data from database driver
     * @param array<int, string> $params Additional param
     * @param object|null        $helper Helper object. E.g., database connection
     *
     * @return TPhpNativeValue
     */
    public static function get(mixed $value, array $params = [], ?object $helper = null): mixed;

    /**
     * Takes a PHP value, returns its value for a data source.
     *
     * @param TPhpNativeValue    $value  PHP native value
     * @param array<int, string> $params Additional param
     * @param object|null        $helper Helper object. E.g., database connection
     *
     * @return TDataSourceValue
     */
    public static function set(mixed $value, array $params = [], ?object $helper = null): mixed;
}
