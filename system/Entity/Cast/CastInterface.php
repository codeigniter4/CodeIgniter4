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
 * The methods work at (1)(4) only.
 *   [App Code] --- (1) --> [Entity] --- (2) --> [Database]
 *   [App Code] <-- (4) --- [Entity] <-- (3) --- [Database]
 */
interface CastInterface
{
    /**
     * Takes a raw value from Entity, returns its value for PHP.
     *
     * @param mixed              $value  Data
     * @param array<int, string> $params Additional param
     *
     * @return mixed
     */
    public static function get($value, array $params = []);

    /**
     * Takes a PHP value, returns its raw value for Entity.
     *
     * @param mixed              $value  Data
     * @param array<int, string> $params Additional param
     *
     * @return mixed
     */
    public static function set($value, array $params = []);
}
