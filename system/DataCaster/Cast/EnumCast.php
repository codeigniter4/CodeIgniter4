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

use BackedEnum;
use CodeIgniter\DataCaster\Exceptions\CastException;
use ReflectionEnum;
use UnitEnum;

/**
 * Class EnumCast
 *
 * Handles casting for PHP enums (both backed and unit enums)
 *
 * (PHP) [enum --> value/name] --> (DB driver) --> (DB column) int|string
 *       [     <-- value/name] <-- (DB driver) <-- (DB column) int|string
 */
class EnumCast extends BaseCast implements CastInterface
{
    public static function get(
        mixed $value,
        array $params = [],
        ?object $helper = null,
    ): BackedEnum|UnitEnum {
        if (! is_string($value) && ! is_int($value)) {
            self::invalidTypeValueError($value);
        }

        $enumClass = $params[0] ?? null;

        if ($enumClass === null) {
            throw CastException::forMissingEnumClass();
        }

        if (! enum_exists($enumClass)) {
            throw CastException::forNotEnum($enumClass);
        }

        $reflection = new ReflectionEnum($enumClass);

        // Unit enum
        if (! $reflection->isBacked()) {
            // Unit enum - match by name
            foreach ($enumClass::cases() as $case) {
                if ($case->name === $value) {
                    return $case;
                }
            }

            throw CastException::forInvalidEnumCaseName($enumClass, $value);
        }

        // Backed enum - validate and cast the value to proper type
        $backingType = $reflection->getBackingType();

        // Cast to proper type (int or string)
        if ($backingType->getName() === 'int') {
            $value = (int) $value;
        } elseif ($backingType->getName() === 'string') {
            $value = (string) $value;
        }

        $enum = $enumClass::tryFrom($value);

        if ($enum === null) {
            throw CastException::forInvalidEnumValue($enumClass, $value);
        }

        return $enum;
    }

    public static function set(
        mixed $value,
        array $params = [],
        ?object $helper = null,
    ): int|string {
        if (! is_object($value) || ! enum_exists($value::class)) {
            self::invalidTypeValueError($value);
        }

        // Get the expected enum class
        $enumClass = $params[0] ?? null;

        if ($enumClass === null) {
            throw CastException::forMissingEnumClass();
        }

        if (! enum_exists($enumClass)) {
            throw CastException::forNotEnum($enumClass);
        }

        // Validate that the enum is of the expected type
        if (! $value instanceof $enumClass) {
            throw CastException::forInvalidEnumType($enumClass, $value::class);
        }

        $reflection = new ReflectionEnum($value::class);

        // Backed enum - return the properly typed backing value
        if ($reflection->isBacked()) {
            /** @var BackedEnum $value */
            return $value->value;
        }

        // Unit enum - return the case name
        /** @var UnitEnum $value */
        return $value->name;
    }
}
