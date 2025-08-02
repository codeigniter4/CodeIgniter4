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

use ArrayIterator;
use CodeIgniter\Exceptions\RuntimeException;

/**
 * @template TKey of string
 * @template TValue
 *
 * @implements ParametersInterface<TKey, TValue>
 *
 * @see \CodeIgniter\HTTP\Parameters\ParametersTest
 */
class Parameters implements ParametersInterface
{
    /**
     * @param array<TKey, TValue> $parameters
     */
    public function __construct(
        protected array $parameters = [],
    ) {
    }

    public function override(array $parameters = []): void
    {
        $this->parameters = $parameters;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->parameters);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $this->parameters) ? $this->parameters[$key] : $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->parameters[$key] = $value;
    }

    public function remove(string $key): void
    {
        unset($this->parameters[$key]);
    }

    public function all(?string $key = null): array
    {
        if ($key === null) {
            return $this->parameters;
        }

        if (! isset($this->parameters[$key]) || ! is_array($this->parameters[$key])) {
            throw new RuntimeException(sprintf('The key "%s" value for Parameters is not an array or was not found.', $key));
        }

        return $this->parameters[$key];
    }

    public function keys(): array
    {
        return array_keys($this->parameters);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->parameters);
    }

    public function count(): int
    {
        return count($this->parameters);
    }
}
