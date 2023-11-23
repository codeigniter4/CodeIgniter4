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

use TypeError;

/**
 * @template TPhpValue PHP data type
 * @template TToDb     Data type to pass to database driver
 * @template TDbColumn Data type from database driver
 *
 * @implements CastInterface<TPhpValue, TToDb, TDbColumn>
 */
abstract class BaseCast implements CastInterface
{
    public static function fromDatabase(mixed $value, array $params = []): mixed
    {
        return $value;
    }

    public static function toDatabase(mixed $value, array $params = []): mixed
    {
        return $value;
    }

    /**
     * @throws TypeError
     */
    protected static function invalidTypeValueError(mixed $value, string $class): never
    {
        $message = '[' . $class . '] Invalid value type: ' . get_debug_type($value);
        if (is_scalar($value)) {
            $message .= ', and its value: ' . $value;
        }

        throw new TypeError($message);
    }
}
