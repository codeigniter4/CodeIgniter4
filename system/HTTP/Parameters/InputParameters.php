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

namespace CodeIgniter\HTTP\Parameters;

use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Exceptions\RuntimeException;

/**
 * @template TKey of string
 * @template TValue of bool|float|int|string|array<int|string, mixed>
 *
 * @see \CodeIgniter\HTTP\Parameters\InputParametersTest
 */
class InputParameters extends Parameters
{
    public function override(array $parameters = []): void
    {
        $this->parameters = [];

        foreach ($parameters as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function get(string $key, $default = null)
    {
        if ($default !== null && ! is_scalar($default)) {
            throw new InvalidArgumentException(sprintf('The default value for the InputParameters must be a scalar type, "%s" given.', gettype($default)));
        }

        // TODO: We need to check that the default value is set. Let's check the unique string
        $tempDefault = bin2hex(random_bytes(8));

        $value = parent::get($key, $tempDefault);

        if ($value !== null && $value !== $tempDefault && ! is_scalar($value)) {
            throw new RuntimeException(sprintf('The value of the key "%s" InputParameters does not contain a scalar value, "%s" given.', $key, gettype($value)));
        }

        return $value === $tempDefault ? $default : $value;
    }

    public function set(string $key, $value): void
    {
        if (! is_scalar($value) && ! is_array($value)) {
            throw new InvalidArgumentException(sprintf('The value for the InputParameters must be a scalar type, "%s" given.', gettype($value)));
        }

        $this->parameters[$key] = $value;
    }
}
