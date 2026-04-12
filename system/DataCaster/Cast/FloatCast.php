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

use CodeIgniter\DataCaster\Exceptions\CastException;

/**
 * Class FloatCast
 *
 * (PHP) [float --> float       ] --> (DB driver) --> (DB column) float
 *       [      <-- float|string] <-- (DB driver) <-- (DB column) float
 */
class FloatCast extends BaseCast
{
    public static function get(
        mixed $value,
        array $params = [],
        ?object $helper = null,
    ): float {
        if (! is_float($value) && ! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        $precision = isset($params[0]) ? (int) $params[0] : null;

        if ($precision === null) {
            return (float) $value;
        }

        $mode = match (strtolower($params[1] ?? 'up')) {
            'up'    => PHP_ROUND_HALF_UP,
            'down'  => PHP_ROUND_HALF_DOWN,
            'even'  => PHP_ROUND_HALF_EVEN,
            'odd'   => PHP_ROUND_HALF_ODD,
            default => throw CastException::forInvalidFloatRoundingMode($params[1]),
        };

        return round((float) $value, $precision, $mode);
    }
}
