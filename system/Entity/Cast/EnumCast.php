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

/**
 * @extends BaseCast<BackedEnum|UnitEnum, int|string>
 */
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

        if (is_a($enumClass, BackedEnum::class, true)) {
            $backingType = $reflection->getBackingType();

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

        foreach ($enumClass::cases() as $case) {
            if ($case->name === $value) {
                return $case;
            }
        }

        throw CastException::forInvalidEnumCaseName($enumClass, $value);
    }

    /**
     * @param mixed $value
     */
    public static function set($value, array $params = []): int|string
    {
        $enumClass = $params[0] ?? null;

        if ($enumClass === null) {
            throw CastException::forMissingEnumClass();
        }

        if (! enum_exists($enumClass)) {
            throw CastException::forNotEnum($enumClass);
        }

        if (is_object($value) && enum_exists($value::class)) {
            if (! $value instanceof $enumClass) {
                throw CastException::forInvalidEnumType($enumClass, $value::class);
            }

            if ($value instanceof BackedEnum) {
                return $value->value;
            }

            return $value->name;
        }

        $reflection = new ReflectionEnum($enumClass);

        if (is_a($enumClass, BackedEnum::class, true)) {
            $backingType = $reflection->getBackingType();

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

        $value = (string) $value;

        foreach ($enumClass::cases() as $case) {
            if ($case->name === $value) {
                return $value;
            }
        }

        throw CastException::forInvalidEnumCaseName($enumClass, $value);
    }
}
