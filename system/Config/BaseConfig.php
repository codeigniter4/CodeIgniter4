<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Config;

use CodeIgniter\Autoloader\FileLocatorInterface;
use CodeIgniter\Exceptions\ConfigException;
use CodeIgniter\Exceptions\RuntimeException;
use Config\Encryption;
use Config\Modules;
use ReflectionClass;
use ReflectionException;

/**
 * Class BaseConfig
 *
 * Not intended to be used on its own, this class will attempt to
 * automatically populate the child class' properties with values
 * from the environment.
 *
 * These can be set within the .env file.
 *
 * @phpstan-consistent-constructor
 * @see \CodeIgniter\Config\BaseConfigTest
 */
class BaseConfig
{
    /**
     * An optional array of classes that will act as Registrars
     * for rapidly setting config class properties.
     *
     * @var array
     */
    public static $registrars = [];

    /**
     * Whether to override properties by Env vars and Registrars.
     */
    public static bool $override = true;

    /**
     * Has module discovery completed?
     *
     * @var bool
     */
    protected static $didDiscovery = false;

    /**
     * Is module discovery running or not?
     */
    protected static bool $discovering = false;

    /**
     * The processing Registrar file for error message.
     */
    protected static string $registrarFile = '';

    /**
     * The modules configuration.
     *
     * @var Modules|null
     */
    protected static $moduleConfig;

    public static function __set_state(array $array)
    {
        static::$override = false;
        $obj              = new static();
        static::$override = true;

        $properties = array_keys(get_object_vars($obj));

        foreach ($properties as $property) {
            $obj->{$property} = $array[$property];
        }

        return $obj;
    }

    /**
     * @internal For testing purposes only.
     * @testTag
     */
    public static function setModules(Modules $modules): void
    {
        static::$moduleConfig = $modules;
    }

    /**
     * @internal For testing purposes only.
     * @testTag
     */
    public static function reset(): void
    {
        static::$registrars   = [];
        static::$override     = true;
        static::$didDiscovery = false;
        static::$moduleConfig = null;
    }

    /**
     * Will attempt to get environment variables with names
     * that match the properties of the child class.
     *
     * The "shortPrefix" is the lowercase-only config class name.
     */
    public function __construct()
    {
        static::$moduleConfig ??= new Modules();

        if (! static::$override) {
            return;
        }

        $this->registerProperties();

        $properties  = array_keys(get_object_vars($this));
        $prefix      = static::class;
        $slashAt     = strrpos($prefix, '\\');
        $shortPrefix = strtolower(substr($prefix, $slashAt === false ? 0 : $slashAt + 1));

        foreach ($properties as $property) {
            $this->initEnvValue($this->{$property}, $property, $prefix, $shortPrefix);

            if ($this instanceof Encryption) {
                if ($property === 'key') {
                    $this->{$property} = $this->parseEncryptionKey($this->{$property});
                } elseif ($property === 'previousKeys') {
                    $keysArray  = is_string($this->{$property}) ? array_map(trim(...), explode(',', $this->{$property})) : $this->{$property};
                    $parsedKeys = [];

                    foreach ($keysArray as $key) {
                        $parsedKeys[] = $this->parseEncryptionKey($key);
                    }

                    $this->{$property} = $parsedKeys;
                }
            }
        }
    }

    /**
     * Parse encryption key with hex2bin: or base64: prefix
     */
    protected function parseEncryptionKey(string $key): string
    {
        if (str_starts_with($key, 'hex2bin:')) {
            return hex2bin(substr($key, 8));
        }

        if (str_starts_with($key, 'base64:')) {
            return base64_decode(substr($key, 7), true);
        }

        return $key;
    }

    /**
     * Initialization an environment-specific configuration setting
     *
     * @param array|bool|float|int|string|null $property
     *
     * @return void
     */
    protected function initEnvValue(&$property, string $name, string $prefix, string $shortPrefix)
    {
        if (is_array($property)) {
            foreach (array_keys($property) as $key) {
                $this->initEnvValue($property[$key], "{$name}.{$key}", $prefix, $shortPrefix);
            }
        } elseif (($value = $this->getEnvValue($name, $prefix, $shortPrefix)) !== false && $value !== null) {
            if ($value === 'false') {
                $value = false;
            } elseif ($value === 'true') {
                $value = true;
            }
            if (is_bool($value)) {
                $property = $value;

                return;
            }

            $value = trim($value, '\'"');

            if (is_int($property)) {
                $value = (int) $value;
            } elseif (is_float($property)) {
                $value = (float) $value;
            }

            // If the default value of the property is `null` and the type is not
            // `string`, TypeError will happen.
            // So cannot set `declare(strict_types=1)` in this file.
            $property = $value;
        }
    }

    /**
     * Retrieve an environment-specific configuration setting
     *
     * @return string|null
     */
    protected function getEnvValue(string $property, string $prefix, string $shortPrefix)
    {
        $shortPrefix        = ltrim($shortPrefix, '\\');
        $underscoreProperty = str_replace('.', '_', $property);

        switch (true) {
            case array_key_exists("{$shortPrefix}.{$property}", $_ENV):
                return $_ENV["{$shortPrefix}.{$property}"];

            case array_key_exists("{$shortPrefix}_{$underscoreProperty}", $_ENV):
                return $_ENV["{$shortPrefix}_{$underscoreProperty}"];

            case array_key_exists("{$shortPrefix}.{$property}", $_SERVER):
                return $_SERVER["{$shortPrefix}.{$property}"];

            case array_key_exists("{$shortPrefix}_{$underscoreProperty}", $_SERVER):
                return $_SERVER["{$shortPrefix}_{$underscoreProperty}"];

            case array_key_exists("{$prefix}.{$property}", $_ENV):
                return $_ENV["{$prefix}.{$property}"];

            case array_key_exists("{$prefix}_{$underscoreProperty}", $_ENV):
                return $_ENV["{$prefix}_{$underscoreProperty}"];

            case array_key_exists("{$prefix}.{$property}", $_SERVER):
                return $_SERVER["{$prefix}.{$property}"];

            case array_key_exists("{$prefix}_{$underscoreProperty}", $_SERVER):
                return $_SERVER["{$prefix}_{$underscoreProperty}"];

            default:
                $value = getenv("{$shortPrefix}.{$property}");
                $value = $value === false ? getenv("{$shortPrefix}_{$underscoreProperty}") : $value;
                $value = $value === false ? getenv("{$prefix}.{$property}") : $value;
                $value = $value === false ? getenv("{$prefix}_{$underscoreProperty}") : $value;

                return $value === false ? null : $value;
        }
    }

    /**
     * Provides external libraries a simple way to register one or more
     * options into a config file.
     *
     * @return void
     *
     * @throws ReflectionException
     */
    protected function registerProperties()
    {
        if (! static::$moduleConfig->shouldDiscover('registrars')) {
            return;
        }

        if (! static::$didDiscovery) {
            // Discovery must be completed before the first instantiation of any Config class.
            if (static::$discovering) {
                throw new ConfigException(
                    'During Auto-Discovery of Registrars,'
                    . ' "' . static::class . '" executes Auto-Discovery again.'
                    . ' "' . clean_path(static::$registrarFile) . '" seems to have bad code.',
                );
            }

            static::$discovering = true;

            /** @var FileLocatorInterface */
            $locator         = service('locator');
            $registrarsFiles = $locator->search('Config/Registrar.php');

            foreach ($registrarsFiles as $file) {
                // Saves the file for error message.
                static::$registrarFile = $file;

                $className = $locator->findQualifiedNameFromPath($file);

                if ($className === false) {
                    continue;
                }

                static::$registrars[] = new $className();
            }

            static::$didDiscovery = true;
            static::$discovering  = false;
        }

        $shortName = (new ReflectionClass($this))->getShortName();

        // Check the registrar class for a method named after this class' shortName
        foreach (static::$registrars as $callable) {
            // ignore non-applicable registrars
            if (! method_exists($callable, $shortName)) {
                continue; // @codeCoverageIgnore
            }

            $properties = $callable::$shortName();

            if (! is_array($properties)) {
                throw new RuntimeException('Registrars must return an array of properties and their values.');
            }

            foreach ($properties as $property => $value) {
                // Directives are recognized only at the property root.
                if ($value instanceof Merge) {
                    $this->{$property} = $this->applyMerge($this->{$property} ?? null, $value);

                    continue;
                }

                // Legacy behavior - unchanged, and on the hot path with no extra checks.
                if (isset($this->{$property}) && is_array($this->{$property}) && is_array($value)) {
                    $this->{$property} = array_merge($this->{$property}, $value);
                } else {
                    $this->{$property} = $value;
                }
            }
        }
    }

    /**
     * Applies a property-root Merge directive against the current value.
     *
     * REPLACE is terminal - its payload is taken verbatim. The list strategies
     * (APPEND/PREPEND/BEFORE/AFTER) resolve via mergeList(). BY_KEY recurses via
     * mergeByKey(), honoring nested directives.
     */
    private function applyMerge(mixed $current, Merge $directive): mixed
    {
        return match ($directive->strategy) {
            Merge::REPLACE                                             => $directive->value,
            Merge::BY_KEY                                              => $this->mergeByKey(is_array($current) ? $current : [], $directive->value),
            Merge::APPEND, Merge::PREPEND, Merge::BEFORE, Merge::AFTER => $this->mergeList(is_array($current) ? $current : [], $directive),
        };
    }

    /**
     * Resolves a list directive (APPEND, PREPEND, BEFORE, AFTER) against the
     * current value treated as a list.
     *
     * The directives never introduce a duplicate value: the incoming payload is
     * de-duplicated against itself (keeping first-seen order) and values already
     * in the list are not added again. Duplicates that already exist in the
     * current list are left untouched. Then:
     *  - APPEND/PREPEND add only the values that are absent - already-present
     *    values are left where they are (no relocation).
     *  - BEFORE/AFTER move an already-present value to the anchor position, but
     *    only when the anchor exists. If the anchor is missing they fall back to
     *    APPEND/PREPEND respectively and do not relocate already-present values.
     *
     * The anchor is matched strictly (===) against the list elements, using the
     * first match. Do not use a value as both the anchor and an inserted value.
     *
     * @param array<array-key, mixed> $current
     *
     * @return list<mixed>
     */
    private function mergeList(array $current, Merge $directive): array
    {
        $current = array_values($current);

        // De-duplicate the payload itself (strict, first-seen order) so a value
        // repeated within it is not inserted twice.
        $incoming = [];

        foreach ($directive->value as $value) {
            if (! in_array($value, $incoming, true)) {
                $incoming[] = $value;
            }
        }

        $anchored    = $directive->strategy === Merge::BEFORE || $directive->strategy === Merge::AFTER;
        $anchorFound = $anchored && in_array($directive->anchor, $current, true);

        if ($anchorFound) {
            // Move-to-position: pull out any present copies, then insert the
            // whole incoming block at the (recomputed) anchor position.
            $current = array_values(array_filter(
                $current,
                static fn ($value): bool => ! in_array($value, $incoming, true),
            ));

            $index  = (int) array_search($directive->anchor, $current, true);
            $offset = $directive->strategy === Merge::AFTER ? $index + 1 : $index;

            array_splice($current, $offset, 0, $incoming);

            return $current;
        }

        // APPEND/PREPEND, or BEFORE/AFTER with a missing anchor: add only the
        // values not already present, without relocating anything.
        $incoming = array_values(array_filter(
            $incoming,
            static fn ($value): bool => ! in_array($value, $current, true),
        ));

        return $directive->strategy === Merge::PREPEND || $directive->strategy === Merge::BEFORE
            ? array_merge($incoming, $current)
            : array_merge($current, $incoming);
    }

    /**
     * Recursive by-key merge used by Merge::byKey(): string keys recurse, integer
     * keys append, scalar leaves are replaced, and nested Merge directives are
     * honored. A missing/non-array current child uses [] as its base, so directives
     * in brand-new subtrees are still resolved.
     *
     * @param array<array-key, mixed> $current
     * @param array<array-key, mixed> $incoming
     *
     * @return array<array-key, mixed>
     */
    private function mergeByKey(array $current, array $incoming): array
    {
        foreach ($incoming as $key => $value) {
            if ($value instanceof Merge) {
                if (is_int($key)) {
                    // No stable current element at an appended position; resolve against null.
                    $current[] = $this->applyMerge(null, $value);
                } else {
                    $current[$key] = $this->applyMerge($current[$key] ?? null, $value);
                }
            } elseif (is_int($key)) {
                $current[] = $value;
            } elseif (is_array($value)) {
                $current[$key] = $this->mergeByKey(
                    isset($current[$key]) && is_array($current[$key]) ? $current[$key] : [],
                    $value,
                );
            } else {
                $current[$key] = $value;
            }
        }

        return $current;
    }
}
