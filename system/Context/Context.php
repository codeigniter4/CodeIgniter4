<?php

namespace CodeIgniter\Context;

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
        $this->data = [];
        $this->hiddenData = [];
    }

    /**
     * Set a key-value pair to the context.
     *
     * @param string|array<string, mixed> $key The key to identify the data. Can be a string or an array of key-value pairs.
     * @param mixed $value The value to be stored in the context.
     * @return $this
     */
    public function set(string|array $key, mixed $value): self
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
     * @param string|array<string, mixed> $key The key to identify the data. Can be a string or an array of key-value pairs.
     * @param mixed $value The value to be stored in the context.
     * @return $this
     */
    public function setHidden(string|array $key, mixed $value): self
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
     *
     * @param string $key The key to identify the data.
     * @param mixed|null $default The default value to return if the key does not exist in the context.
     * @return mixed The value associated with the key, or the default value if the key does not exist.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
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
     * @param string $key The key to identify the data.
     * @param mixed|null $default The default value to return if the key does not exist in the context.
     * @return mixed The value associated with the key, or the default value if the key does not exist.
     */
    public function getHidden(string $key, mixed $default = null): mixed
    {
        return $this->hiddenData[$key] ?? $default;
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
     * @return bool True if the key exists in the context, false otherwise.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Check if a key exists in the hidden context.
     *
     * @param string $key The key to check for existence in the hidden context.
     * @return bool True if the key exists in the hidden context, false otherwise.
     */
    public function hasHidden(string $key): bool
    {
        return array_key_exists($key, $this->hiddenData);
    }

    /**
     * Remove a key-value pair from the context by its key.
     *
     * @param string $key The key to identify the data to be removed from the context.
     * @return $this
     */
    public function remove(string $key): self
    {
        unset($this->data[$key]);
        return $this;
    }

    /**
     * Remove a key-value pair from the hidden context by its key.
     *
     * @param string $key The key to identify the data to be removed from the hidden context.
     * @return $this
     */
    public function removeHidden(string $key): self
    {
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
