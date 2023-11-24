<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\DataConverter\Cast;

use CodeIgniter\DataConverter\Exceptions\CastException;
use JsonException;
use stdClass;

/**
 * Class JsonCast
 *
 * (PHP) [array|stdClass --> string] --> (DB driver) --> (DB column) string
 *       [               <-- string] <-- (DB driver) <-- (DB column) string
 *
 * @extends BaseCast<array|stdClass, string, mixed>
 */
class JsonCast extends BaseCast
{
    public static function fromDataSource(mixed $value, array $params = []): array|stdClass
    {
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

    public static function toDataSource(mixed $value, array $params = []): string
    {
        try {
            $output = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw CastException::forInvalidJsonFormat($e->getCode());
        }

        return $output;
    }
}
