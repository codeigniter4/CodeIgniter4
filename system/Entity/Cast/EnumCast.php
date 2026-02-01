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

use BackedEnum;
use CodeIgniter\Entity\Exceptions\CastException;
use ReflectionEnum;
use UnitEnum;

class EnumCast extends BaseCast
{
    public static function get($value, array $params = []): BackedEnum|UnitEnum
    {
        $enumClass = $params[0] ?? null;

        if ($enumClass === null) {
            throw CastException::forMissingEnumClass();
        }

        if (! enum_exists($enumClass)) {
            throw CastException::forNotEnum($enumClass);
        }

        $reflection = new ReflectionEnum($enumClass);

        // Backed enum - validate and cast the value to proper type
        if ($reflection->isBacked()) {
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

        // Unit enum - match by name
        foreach ($enumClass::cases() as $case) {
            if ($case->name === $value) {
                return $case;
            }
        }

        throw CastException::forInvalidEnumCaseName($enumClass, $value);
    }

    public static function set($value, array $params = []): int|string
    {
        // Get the expected enum class
        $enumClass = $params[0] ?? null;

        if ($enumClass === null) {
            throw CastException::forMissingEnumClass();
        }

        if (! enum_exists($enumClass)) {
            throw CastException::forNotEnum($enumClass);
        }

        // If it's already an enum object, validate and extract its value
        if (is_object($value) && enum_exists($value::class)) {
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

        $reflection = new ReflectionEnum($enumClass);

        // Validate backed enum values
        if ($reflection->isBacked()) {
            $backingType = $reflection->getBackingType();

            // Cast to proper type (int or string)
            if ($backingType->getName() === 'int') {
                $value = (int) $value;
            } elseif ($backingType->getName() === 'string') {
                $value = (string) $value;
            }

            if ($enumClass::tryFrom($value) === null) {
                throw CastException::forInvalidEnumValue($enumClass, $value);
            }

            return $value;
        }

        // Validate unit enum case names - must be a string
        $value = (string) $value;

        foreach ($enumClass::cases() as $case) {
            if ($case->name === $value) {
                return $value;
            }
        }

        throw CastException::forInvalidEnumCaseName($enumClass, $value);
    }
}
