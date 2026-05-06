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

namespace CodeIgniter\Input;

use CodeIgniter\Exceptions\InvalidArgumentException;

/**
 * @see \CodeIgniter\Input\InputDataTest
 */
class InputData
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(private readonly array $data)
    {
    }

    /**
     * Returns a single input value by name, or the default value if the field
     * is not present.
     *
     * Supports dot-array syntax for nested input data.
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
     * Returns true when the named field exists, even if its value is null.
     *
     * Supports dot-array syntax for nested input data.
     */
    public function has(string $key): bool
    {
        helper('array');

        return dot_array_has($key, $this->data);
    }

    /**
     * Returns an input field as a string.
     *
     * Supports dot-array syntax for nested input data.
     */
    public function string(string $key, ?string $default = null): ?string
    {
        $value = $this->get($key, $default);

        if ($value === null || is_string($value)) {
            return $value;
        }

        throw $this->invalidType($key, 'string');
    }

    /**
     * Returns an input field as an integer.
     *
     * Supports dot-array syntax for nested input data.
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
     * Returns an input field as a float.
     *
     * Supports dot-array syntax for nested input data.
     */
    public function float(string $key, ?float $default = null): ?float
    {
        $value = $this->get($key, $default);

        if ($value === null || is_float($value)) {
            return $value;
        }

        if (is_int($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            $float = filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);

            if ($float !== null) {
                return $float;
            }
        }

        throw $this->invalidType($key, 'float');
    }

    /**
     * Returns an input field as a boolean.
     *
     * Supports dot-array syntax for nested input data.
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
     * Returns an input field as an array.
     *
     * Supports dot-array syntax for nested input data.
     *
     * @param array<array-key, mixed>|null $default
     *
     * @return array<array-key, mixed>|null
     */
    public function array(string $key, ?array $default = null): ?array
    {
        $value = $this->get($key, $default);

        if ($value === null || is_array($value)) {
            return $value;
        }

        throw $this->invalidType($key, 'array');
    }

    protected function invalidType(string $key, string $type): InvalidArgumentException
    {
        return new InvalidArgumentException(
            sprintf('The input "%s" value cannot be read as %s.', $key, $type),
        );
    }
}
