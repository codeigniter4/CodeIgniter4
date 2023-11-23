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
 * Class ArrayCast
 *
 * PHP: array <--> DB column: string
 *
 * @extends BaseCast<mixed[], string, string>
 */
class ArrayCast extends BaseCast implements CastInterface
{
    public static function fromDatabase(mixed $value, array $params = []): array
    {
        if (! is_string($value)) {
            self::invalidTypeValueError($value, self::class);
        }

        if ((strpos($value, 'a:') === 0 || strpos($value, 's:') === 0)) {
            $value = unserialize($value, ['allowed_classes' => false]);
        }

        return (array) $value;
    }

    public static function toDatabase(mixed $value, array $params = []): string
    {
        return serialize($value);
    }
}
