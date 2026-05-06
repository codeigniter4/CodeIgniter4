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

namespace CodeIgniter\Validation;

use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\I18n\Time;
use CodeIgniter\Input\InputData;
use DateTimeZone;
use Exception;
use ReflectionEnum;
use UnitEnum;

/**
 * @see \CodeIgniter\Validation\ValidatedInputTest
 */
class ValidatedInput extends InputData
{
    /**
     * Returns a validated field as a Time instance.
     *
     * Supports dot-array syntax for nested validated data.
     */
    public function date(
        string $key,
        ?string $format = null,
        DateTimeZone|string|null $timezone = null,
    ): ?Time {
        $value = $this->get($key);

        if ($value === null) {
            return null;
        }

        if (! is_string($value) || $value === '') {
            $this->invalidValue($key, 'date', null);
        }

        try {
            if ($format === null) {
                return Time::parse($value, $timezone);
            }

            return Time::createFromFormat($format, $value, $timezone);
        } catch (Exception) {
            $this->invalidValue($key, 'date', null);
        }
    }

    /**
     * Returns a validated field as an enum instance.
     *
     * Supports dot-array syntax for nested validated data.
     *
     * @template TEnum of UnitEnum
     *
     * @param class-string<TEnum> $enumClass
     * @param TEnum|null          $default
     *
     * @return TEnum|null
     */
    public function enum(string $key, string $enumClass, ?UnitEnum $default = null): ?UnitEnum
    {
        if (! enum_exists($enumClass)) {
            throw new InvalidArgumentException('The "' . $enumClass . '" class is not a valid enum.');
        }

        if ($default instanceof UnitEnum && ! $default instanceof $enumClass) {
            $this->invalidValue($key, $enumClass, $default);
        }

        $value = $this->get($key, $default);

        if ($value === null) {
            return null;
        }

        if ($value instanceof UnitEnum) {
            if ($value instanceof $enumClass) {
                return $value;
            }

            $this->invalidValue($key, $enumClass, $default);
        }

        $reflection = new ReflectionEnum($enumClass);

        if ($reflection->isBacked()) {
            return $this->backedEnum($key, $enumClass, $reflection, $value);
        }

        if (is_string($value)) {
            foreach ($enumClass::cases() as $case) {
                if ($case->name === $value) {
                    return $case;
                }
            }
        }

        $this->invalidValue($key, $enumClass, $default);
    }

    private function backedEnum(string $key, string $enumClass, ReflectionEnum $reflection, mixed $value): UnitEnum
    {
        $backingType = $reflection->getBackingType()?->getName();

        if ($backingType === 'int') {
            if (is_string($value)) {
                $value = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
            }

            if (! is_int($value)) {
                $this->invalidValue($key, $enumClass, null);
            }
        } elseif (! is_int($value) && ! is_string($value)) {
            $this->invalidValue($key, $enumClass, null);
        }

        if ($backingType === 'string') {
            $value = (string) $value;
        }

        $enum = $enumClass::tryFrom($value);

        if ($enum === null) {
            $this->invalidValue($key, $enumClass, null);
        }

        return $enum;
    }

    protected function invalidValue(string $key, string $type, mixed $default): never
    {
        throw new InvalidArgumentException(
            sprintf('The validated "%s" value cannot be read as %s.', $key, $type),
        );
    }
}
