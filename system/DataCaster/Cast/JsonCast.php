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
use JsonException;
use stdClass;

/**
 * Class JsonCast
 *
 * (PHP) [array|stdClass --> string] --> (DB driver) --> (DB column) string
 *       [               <-- string] <-- (DB driver) <-- (DB column) string
 */
class JsonCast extends BaseCast
{
    public static function get(
        mixed $value,
        array $params = [],
        ?object $helper = null,
    ): array|stdClass {
        if (! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        $associative = in_array('array', $params, true);

        $output = ($associative ? [] : new stdClass());

        try {
            $output = json_decode($value, $associative, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw CastException::forInvalidJsonFormat($e->getCode());
        }

        return $output;
    }

    public static function set(
        mixed $value,
        array $params = [],
        ?object $helper = null,
    ): string {
        try {
            $output = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw CastException::forInvalidJsonFormat($e->getCode());
        }

        return $output;
    }
}
