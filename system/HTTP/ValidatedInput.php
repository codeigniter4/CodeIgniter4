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

namespace CodeIgniter\HTTP;

use BackedEnum;
use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\I18n\Time;
use DateTimeZone;
use Exception;
use ReflectionEnum;
use UnitEnum;

/**
 * @see \CodeIgniter\HTTP\ValidatedInputTest
 */
class ValidatedInput
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(private readonly array $data)
    {
    }

    /**
     * Returns a single validated field value by name, or the default value
     * if the field is not present in the validated data.
     *
     * Supports dot-array syntax for nested validated data.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        helper('array');

        if (! dot_array_has($key, $this->data)) {
            return $default;
        }

        return dot_array_search($key, $this->data);
    }

    /**
     * Returns true when the named field exists in the validated data, even if
     * its value is null.
     *
     * Supports dot-array syntax for nested validated data.
     */
    public function has(string $key): bool
    {
        helper('array');

        return dot_array_has($key, $this->data);
    }

    /**
     * Returns a validated field as an integer.
     *
     * Supports dot-array syntax for nested validated data.
     */
    public function integer(string $key, ?int $default = null): ?int
    {
        $value = $this->get($key, $default);

        if ($value === null || is_int($value)) {
            return $value;
        }

        if (is_string($value)) {
            $integer = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

            if ($integer !== null) {
                return $integer;
            }
        }

        throw $this->invalidType($key, 'integer');
    }

    /**
     * Returns a validated field as a boolean.
     *
     * Supports dot-array syntax for nested validated data.
     */
    public function boolean(string $key, ?bool $default = null): ?bool
    {
        $value = $this->get($key, $default);

        if ($value === null || is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_string($value)) {
            $boolean = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($boolean !== null) {
                return $boolean;
            }
        }

        throw $this->invalidType($key, 'boolean');
    }

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

        if (! is_subclass_of($enumClass, BackedEnum::class)) {
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

    private function invalidType(string $key, string $type): InvalidArgumentException
    {
        return new InvalidArgumentException(
            sprintf('The validated "%s" value cannot be read as %s.', $key, $type),
        );
    }
}
