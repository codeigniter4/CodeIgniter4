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

class Context
{
    /**
     * The data stored in the context.
     *
     * @var array<string, mixed>
     */
    protected array $data;

    /**
     * The data that is stored, but not included in logs.
     *
     * @var array<string, mixed>
     */
    private array $hiddenData;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->data       = [];
        $this->hiddenData = [];
    }

    /**
     * Set a key-value pair to the context.
     *
     * @param array<string, mixed>|string $key   The key to identify the data. Can be a string or an array of key-value pairs.
     * @param mixed                       $value The value to be stored in the context.
     *
     * @return $this
     */
    public function set(array|string $key, mixed $value = null): self
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);

            return $this;
        }

        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Set a hidden key-value pair to the context. This data will not be included in logs.
     *
     * @param array<string, mixed>|string $key   The key to identify the data. Can be a string or an array of key-value pairs.
     * @param mixed                       $value The value to be stored in the context.
     *
     * @return $this
     */
    public function setHidden(array|string $key, mixed $value = null): self
    {
        if (is_array($key)) {
            $this->hiddenData = array_merge($this->hiddenData, $key);

            return $this;
        }

        $this->hiddenData[$key] = $value;

        return $this;
    }

    /**
     * Get a value from the context by its key, or return a default value if the key does not exist.
     * Supports dot notation for nested arrays (e.g., 'user.profile.name' to access $data['user']['profile']['name']).
     *
     * @param string     $key     The key to identify the data.
     * @param mixed|null $default The default value to return if the key does not exist in the context.
     *
     * @return mixed The value associated with the key, or the default value if the key does not exist.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return ArrayHelper::dotSearch($key, $this->data) ?? $default;
    }

    /**
     * Get only the specified keys from the context. If a key does not exist, it will be ignored.
     *
     * @param list<string>|string $keys An array of keys to retrieve from the context.
     *
     * @return array<string, mixed> An array of key-value pairs for the specified keys that exist in the context.
     */
    public function getOnly(array|string $keys): array
    {
        if (is_string($keys)) {
            $keys = [$keys];
        }

        return array_filter($this->data, static fn ($k): bool => in_array($k, $keys, true), ARRAY_FILTER_USE_KEY);
    }

    /**
     * Get all keys from the context except the specified keys.
     *
     * @param list<string>|string $keys An array of keys to exclude from the context.
     *
     * @return array<string, mixed> An array of key-value pairs for all keys in the context except the specified keys.
     */
    public function getExcept(array|string $keys): array
    {
        if (is_string($keys)) {
            $keys = [$keys];
        }

        return array_filter($this->data, static fn ($k): bool => ! in_array($k, $keys, true), ARRAY_FILTER_USE_KEY);
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
     *
     * @param string     $key     The key to identify the data.
     * @param mixed|null $default The default value to return if the key does not exist in the context.
     *
     * @return mixed The value associated with the key, or the default value if the key does not exist.
     */
    public function getHidden(string $key, mixed $default = null): mixed
    {
        return ArrayHelper::dotSearch($key, $this->hiddenData) ?? $default;
    }

    /**
     * Get only the specified keys from the hidden context. If a key does not exist, it will be ignored.
     *
     * @param list<string>|string $keys An array of keys to retrieve from the hidden context.
     *
     * @return array<string, mixed> An array of key-value pairs for the specified keys that exist in the hidden context.
     */
    public function getOnlyHidden(array|string $keys): array
    {
        if (is_string($keys)) {
            $keys = [$keys];
        }

        return array_filter($this->hiddenData, static fn ($k): bool => in_array($k, $keys, true), ARRAY_FILTER_USE_KEY);
    }

    /**
     * Get all keys from the hidden context except the specified keys.
     *
     * @param list<string>|string $keys An array of keys to exclude from the hidden context.
     *
     * @return array<string, mixed> An array of key-value pairs for all keys in the hidden context except the specified keys.
     */
    public function getExceptHidden(array|string $keys): array
    {
        if (is_string($keys)) {
            $keys = [$keys];
        }

        return array_filter($this->hiddenData, static fn ($k): bool => ! in_array($k, $keys, true), ARRAY_FILTER_USE_KEY);
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
     *
     * @param string $key The key to check for existence in the context.
     *
     * @return bool True if the key exists in the context, false otherwise.
     */
    public function has(string $key): bool
    {
        return ArrayHelper::dotKeyExists($key, $this->data);
    }

    /**
     * Check if a key exists in the hidden context.
     *
     * @param string $key The key to check for existence in the hidden context.
     *
     * @return bool True if the key exists in the hidden context, false otherwise.
     */
    public function hasHidden(string $key): bool
    {
        return ArrayHelper::dotKeyExists($key, $this->hiddenData);
    }

    /**
     * Remove a key-value pair from the context by its key.
     *
     * @param list<string>|string $key The key to identify the data to be removed from the context.
     *
     * @return $this
     */
    public function remove(array|string $key): self
    {
        if (is_array($key)) {
            foreach ($key as $k) {
                unset($this->data[$k]);
            }

            return $this;
        }

        unset($this->data[$key]);

        return $this;
    }

    /**
     * Remove a key-value pair from the hidden context by its key.
     *
     * @param list<string>|string $key The key to identify the data to be removed from the hidden context.
     *
     * @return $this
     */
    public function removeHidden(array|string $key): self
    {
        if (is_array($key)) {
            foreach ($key as $k) {
                unset($this->hiddenData[$k]);
            }

            return $this;
        }

        unset($this->hiddenData[$key]);

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
}
