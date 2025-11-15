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
 * Class BooleanCast
 *
 * (PHP) [bool --> bool      ] --> (DB driver) --> (DB column) bool|int(0/1)
 *       [     <-- string|int] <-- (DB driver) <-- (DB column) bool|int(0/1)
 */
class BooleanCast extends BaseCast
{
    public static function get(
        mixed $value,
        array $params = [],
        ?object $helper = null,
    ): bool {
        // For PostgreSQL
        if ($value === 't') {
            return true;
        }
        if ($value === 'f') {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
