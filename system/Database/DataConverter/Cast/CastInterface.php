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
 * @template TPhpValue PHP data type
 * @template TToDb     Data type to pass to database driver
 * @template TDbColumn Data type from database driver
 */
interface CastInterface
{
    /**
     * Takes value from DataSource, returns its value for PHP.
     *
     * @param TDbColumn    $value  Data from database driver
     * @param list<string> $params Additional param
     *
     * @return TPhpValue
     */
    public static function fromDataSource(mixed $value, array $params = []): mixed;

    /**
     * Takes the PHP value, returns its value for DataSource.
     *
     * @param TPhpValue    $value  PHP data
     * @param list<string> $params Additional param
     *
     * @return TToDb Data to pass to database driver
     */
    public static function toDataSource(mixed $value, array $params = []): mixed;
}
