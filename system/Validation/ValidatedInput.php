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
            throw $this->invalidType($key, 'date');
        }

        try {
            if ($format === null) {
                return Time::parse($value, $timezone);
            }

            return Time::createFromFormat($format, $value, $timezone);
        } catch (Exception) {
            throw $this->invalidType($key, 'date');
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
            throw $this->invalidType($key, $enumClass);
        }

        $value = $this->get($key, $default);

        if ($value === null) {
            return null;
        }

        if ($value instanceof UnitEnum) {
            if ($value instanceof $enumClass) {
                return $value;
            }

            throw $this->invalidType($key, $enumClass);
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

        throw $this->invalidType($key, $enumClass);
    }

    private function backedEnum(string $key, string $enumClass, ReflectionEnum $reflection, mixed $value): UnitEnum
    {
        $backingType = $reflection->getBackingType()?->getName();

        if ($backingType === 'int') {
            if (is_string($value)) {
                $value = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
            }

            if (! is_int($value)) {
                throw $this->invalidType($key, $enumClass);
            }
        } elseif (! is_int($value) && ! is_string($value)) {
            throw $this->invalidType($key, $enumClass);
        }

        if ($backingType === 'string') {
            $value = (string) $value;
        }

        $enum = $enumClass::tryFrom($value);

        if ($enum === null) {
            throw $this->invalidType($key, $enumClass);
        }

        return $enum;
    }

    protected function invalidType(string $key, string $type): InvalidArgumentException
    {
        return new InvalidArgumentException(
            sprintf('The validated "%s" value cannot be read as %s.', $key, $type),
        );
    }
}
