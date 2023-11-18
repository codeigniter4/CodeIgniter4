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

use CodeIgniter\Database\DataConverter\Exceptions\CastException;
use JsonException;
use stdClass;

/**
 * Class JsonCast
 *
 * DB column: string <--> PHP: array|stdClass
 */
class JsonCast extends BaseCast
{
    /**
     * {@inheritDoc}
     */
    public static function fromDatabase($value, array $params = [])
    {
        if (! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        $associative = in_array('array', $params, true);

        $output = ($associative ? [] : new stdClass());

        if (
            (strlen($value) > 1 && in_array($value[0], ['[', '{', '"'], true))
            || is_numeric($value)
        ) {
            try {
                $output = json_decode($value, $associative, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw CastException::forInvalidJsonFormat($e->getCode());
            }
        }

        return $output;
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed $value
     */
    public static function toDatabase($value, array $params = []): string
    {
        try {
            $output = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw CastException::forInvalidJsonFormat($e->getCode());
        }

        return $output;
    }
}
