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

namespace CodeIgniter\Context;

use CodeIgniter\Helpers\Array\ArrayHelper;
use SensitiveParameter;
use SensitiveParameterValue;

final class Context
{
    /**
     * The data stored in the context.
     *
     * @var array<string, mixed>
     */
    private array $data = [];

    /**
     * The data that is stored but not included in logs.
     *
     * @var array<string, mixed>
     */
    private array $hiddenData = [];

    /**
     * Set a key-value pair to the context.
     * Supports dot notation for nested arrays.
     *
     * @param array<string, mixed>|string $key   The key to identify the data. Can be a string or an array of key-value pairs.
     * @param mixed                       $value The value to be stored in the context.
     *
     * @return $this
     */
    public function set(array|string $key, mixed $value = null): self
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                if (! $this->hasDotNotation($k)) {
                    $this->data[$k] = $v;

                    continue;
                }

                ArrayHelper::dotSet($this->data, $k, $v);
            }

            return $this;
        }

        if (! $this->hasDotNotation($key)) {
            $this->data[$key] = $value;

            return $this;
        }

        ArrayHelper::dotSet($this->data, $key, $value);

        return $this;
    }

    /**
     * Set a hidden key-value pair to the context. This data will not be included in logs.
     * Supports dot notation for nested arrays.
     *
     * @param array<string, mixed>|string $key   The key to identify the data. Can be a string or an array of key-value pairs.
     * @param mixed                       $value The value to be stored in the context.
     *
     * @return $this
     */
    public function setHidden(#[SensitiveParameter] array|string $key, #[SensitiveParameter] mixed $value = null): self
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                if (! $this->hasDotNotation($k)) {
                    $this->hiddenData[$k] = $v;

                    continue;
                }

                ArrayHelper::dotSet($this->hiddenData, $k, $v);
            }

            return $this;
        }

        if (! $this->hasDotNotation($key)) {
            $this->hiddenData[$key] = $value;

            return $this;
        }

        ArrayHelper::dotSet($this->hiddenData, $key, $value);

        return $this;
    }

    /**
     * Get a value from the context by its key, or return a default value if the key does not exist.
     * Supports dot notation for nested arrays.
     *
     * @param string $key     The key to identify the data.
     * @param mixed  $default The default value to return if the key does not exist in the context.
     *
     * @return mixed The value associated with the key, or the default value if the key does not exist.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (! $this->has($key)) {
            return $default;
        }

        // Exit early if the key is not a dot notation to avoid unnecessary processing in the common case.
        if (! $this->hasDotNotation($key)) {
            return $this->data[$key];
        }

        return ArrayHelper::dotSearch($key, $this->data);
    }

    /**
     * Get only the specified keys from the context. If a key does not exist, it will be ignored.
     * Supports dot notation for nested arrays.
     *
     * @param list<string>|string $keys An array of keys to retrieve from the context.
     *
     * @return array<string, mixed> An array of key-value pairs for the specified keys that exist in the context.
     */
    public function getOnly(array|string $keys): array
    {
        return ArrayHelper::dotOnly($this->data, $keys);
    }

    /**
     * Get all keys from the context except the specified keys.
     * Supports dot notation for nested arrays.
     *
     * @param list<string>|string $keys An array of keys to exclude from the context.
     *
     * @return array<string, mixed> An array of key-value pairs for all keys in the context except the specified keys.
     */
    public function getExcept(array|string $keys): array
    {
        return ArrayHelper::dotExcept($this->data, $keys);
    }

    /**
     * Get all data from the context
     *
     * @return array<string, mixed> An array of all key-value pairs in the context.
     */
    public function getAll(): array
    {
        return $this->data;
    }

    /**
     * Get a hidden value from the context by its key, or return a default value if the key does not exist.
     * Supports dot notation for nested arrays.
     *
     * @param string $key     The key to identify the data.
     * @param mixed  $default The default value to return if the key does not exist in the context.
     *
     * @return mixed The value associated with the key, or the default value if the key does not exist.
     */
    public function getHidden(#[SensitiveParameter] string $key, #[SensitiveParameter] mixed $default = null): mixed
    {
        if (! $this->hasHidden($key)) {
            return $default;
        }

        // Exit early if the key is not a dot notation to avoid unnecessary processing in the common case.
        if (! $this->hasDotNotation($key)) {
            return $this->hiddenData[$key];
        }

        return ArrayHelper::dotSearch($key, $this->hiddenData);
    }

    /**
     * Get only the specified keys from the hidden context. If a key does not exist, it will be ignored.
     * Supports dot notation for nested arrays.
     *
     * @param list<string>|string $keys An array of keys to retrieve from the hidden context.
     *
     * @return array<string, mixed> An array of key-value pairs for the specified keys that exist in the hidden context.
     */
    public function getOnlyHidden(#[SensitiveParameter] array|string $keys): array
    {
        return ArrayHelper::dotOnly($this->hiddenData, $keys);
    }

    /**
     * Get all keys from the hidden context except the specified keys.
     * Supports dot notation for nested arrays.
     *
     * @param list<string>|string $keys An array of keys to exclude from the hidden context.
     *
     * @return array<string, mixed> An array of key-value pairs for all keys in the hidden context except the specified keys.
     */
    public function getExceptHidden(#[SensitiveParameter] array|string $keys): array
    {
        return ArrayHelper::dotExcept($this->hiddenData, $keys);
    }

    /**
     * Get all hidden data from the context
     *
     * @return array<string, mixed> An array of all key-value pairs in the hidden context.
     */
    public function getAllHidden(): array
    {
        return $this->hiddenData;
    }

    /**
     * Check if a key exists in the context.
     * Supports dot notation for nested arrays.
     *
     * @param string $key The key to check for existence in the context.
     *
     * @return bool True if the key exists in the context, false otherwise.
     */
    public function has(string $key): bool
    {
        if (! $this->hasDotNotation($key)) {
            return array_key_exists($key, $this->data);
        }

        return ArrayHelper::dotHas($key, $this->data);
    }

    /**
     * Check if a key exists in the hidden context.
     * Supports dot notation for nested arrays.
     *
     * @param string $key The key to check for existence in the hidden context.
     *
     * @return bool True if the key exists in the hidden context, false otherwise.
     */
    public function hasHidden(string $key): bool
    {
        if (! $this->hasDotNotation($key)) {
            return array_key_exists($key, $this->hiddenData);
        }

        return ArrayHelper::dotHas($key, $this->hiddenData);
    }

    /**
     * Remove a key-value pair from the context by its key.
     * Supports dot notation for nested arrays.
     *
     * @param list<string>|string $key The key to identify the data to be removed from the context.
     *
     * @return $this
     */
    public function remove(array|string $key): self
    {
        if (is_array($key)) {
            foreach ($key as $k) {
                if (! $this->hasDotNotation($k)) {
                    unset($this->data[$k]);

                    continue;
                }

                ArrayHelper::dotUnset($this->data, $k);
            }

            return $this;
        }

        if (! $this->hasDotNotation($key)) {
            unset($this->data[$key]);

            return $this;
        }

        ArrayHelper::dotUnset($this->data, $key);

        return $this;
    }

    /**
     * Remove a key-value pair from the hidden context by its key.
     * Supports dot notation for nested arrays.
     *
     * @param list<string>|string $key The key to identify the data to be removed from the hidden context.
     *
     * @return $this
     */
    public function removeHidden(#[SensitiveParameter] array|string $key): self
    {
        if (is_array($key)) {
            foreach ($key as $k) {
                if (! $this->hasDotNotation($k)) {
                    unset($this->hiddenData[$k]);

                    continue;
                }

                ArrayHelper::dotUnset($this->hiddenData, $k);
            }

            return $this;
        }

        if (! $this->hasDotNotation($key)) {
            unset($this->hiddenData[$key]);

            return $this;
        }

        ArrayHelper::dotUnset($this->hiddenData, $key);

        return $this;
    }

    /**
     * Clear all data from the context, including hidden data.
     *
     * @return $this
     */
    public function clearAll(): self
    {
        $this->clear();
        $this->clearHidden();

        return $this;
    }

    /**
     * Clear all data from the context.
     *
     * @return $this
     */
    public function clear(): self
    {
        $this->data = [];

        return $this;
    }

    /**
     * Clear all hidden data from the context.
     *
     * @return $this
     */
    public function clearHidden(): self
    {
        $this->hiddenData = [];

        return $this;
    }

    public function __debugInfo(): array
    {
        return [
            'data'       => $this->data,
            'hiddenData' => new SensitiveParameterValue($this->hiddenData),
        ];
    }

    public function __clone()
    {
        $this->hiddenData = [];
    }

    public function __serialize(): array
    {
        return [
            'data' => $this->data,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __unserialize(array $data): void
    {
        $this->data       = $data['data'] ?? [];
        $this->hiddenData = [];
    }

    private function hasDotNotation(string $key): bool
    {
        return str_contains($key, '.');
    }
}
