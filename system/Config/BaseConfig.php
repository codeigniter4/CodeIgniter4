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
use ReflectionMethod;

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
 */
class BaseConfig
{
    /**
     * SAPIs whose live getenv() only finds names, with the exact case, that are
     * also in $_SERVER or the getenv() array: the OS environment, plus FastCGI
     * params under FPM and FrankenPHP's own env layers.
     */
    private const EXACT_ENV_SAPIS = ['cli', 'phpdbg', 'cli-server', 'fpm-fcgi', 'frankenphp'];

    /**
     * An optional array of classes that will act as Registrars
     * for rapidly setting config class properties.
     *
     * @var list<class-string|object>
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

    /**
     * Environment variables grouped by the text before their first "." or "_",
     * then by source ("env", "server", "getenv").
     *
     * @var array<string, array<string, array<array-key, mixed>>>|null
     */
    private static ?array $envIndex = null;

    /**
     * The [$_ENV, $_SERVER] arrays the index was built from.
     *
     * @var array{array<array-key, mixed>, array<array-key, mixed>}|null
     */
    private static ?array $envSnapshot = null;

    /**
     * Env var names that exist according to live getenv() calls, keyed by class
     * name, then by leaf name. Each leaf is probed once; its names are re-read on
     * every build. The getenv() array omits names that contain ".", " " or "[",
     * and on Windows only live calls match names case-insensitively.
     *
     * @var array<class-string, array<string, list<string>>>
     */
    private static array $liveEnvNames = [];

    /**
     * The property values of each class's last build, and the env var names of
     * all its leaves from $liveEnvNames, keyed by class name.
     *
     * @var array<class-string, array{array<string, mixed>, list<string>}>
     */
    private static array $liveEnvLeaves = [];

    /**
     * Whether a class overrides getEnvValue() or initEnvValue(), keyed by class name.
     *
     * @var array<class-string, bool>
     */
    private static array $overridesEnvHooks = [];

    /**
     * @param array<string, mixed> $array
     */
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

        self::$envIndex     = self::$envSnapshot = null;
        self::$liveEnvNames = self::$liveEnvLeaves = [];
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

        $values      = get_object_vars($this);
        $properties  = array_keys($values);
        $prefix      = static::class;
        $slashAt     = strrpos($prefix, '\\');
        $shortPrefix = strtolower(substr($prefix, $slashAt === false ? 0 : $slashAt + 1));

        // Classes with custom env hooks keep the full walk with live lookups.
        $sources = (self::$overridesEnvHooks[static::class] ??= self::overridesEnvHooks(static::class))
            ? null
            : self::scopedEnvSources($prefix, $shortPrefix);

        if ($sources !== null) {
            // Unchanged property values share memory with the stored ones, so the
            // leaves are only walked again when a build changes them.
            if (($leaves = self::$liveEnvLeaves[static::class] ?? null) === null || $leaves[0] !== $values) {
                $leaves = self::$liveEnvLeaves[static::class] = [$values, self::findLiveEnvNames($values, $prefix, $shortPrefix)];
            }

            $live = [];

            foreach ($leaves[1] as $envKey) {
                if (($envValue = getenv($envKey)) !== false) {
                    $live[$envKey] = $envValue;
                }
            }

            $sources['getenv'] = $live + $sources['getenv'];

            // Every env var that can override this class, for a fast miss check per leaf.
            $candidates = $sources['env'] + $sources['server'] + $sources['getenv'];
        }

        foreach ($properties as $property) {
            if ($sources === null) {
                $this->initEnvValue($this->{$property}, $property, $prefix, $shortPrefix);
            } elseif ($candidates !== []) {
                $this->initScopedEnvValue($sources, $candidates, $this->{$property}, $property, $prefix, $shortPrefix);
            }

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
     * @param array<array-key, mixed>|bool|float|int|string|null $property
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
            // If the default value of the property is `null` and the type is not
            // `string`, TypeError will happen.
            // So cannot set `declare(strict_types=1)` in this file.
            $property = self::castEnvValue($value, $property);
        }
    }

    /**
     * Same as initEnvValue(), but looks values up in the pre-filtered sources
     * from scopedEnvSources() instead of the live environment.
     *
     * @param array{env: array<array-key, mixed>, server: array<array-key, mixed>, getenv: array<array-key, mixed>} $sources
     * @param array<array-key, mixed>                                                                               $candidates All keys of $sources
     * @param array<array-key, mixed>|bool|float|int|string|null                                                    $property
     */
    private function initScopedEnvValue(array $sources, array $candidates, &$property, string $name, string $prefix, string $shortPrefix): void
    {
        if (is_array($property)) {
            foreach (array_keys($property) as $key) {
                $this->initScopedEnvValue($sources, $candidates, $property[$key], "{$name}.{$key}", $prefix, $shortPrefix);
            }

            return;
        }

        $keys = self::envKeys($name, $prefix, $shortPrefix);

        if (! array_key_exists($keys[0], $candidates) && ! array_key_exists($keys[1], $candidates)
            && ! array_key_exists($keys[2], $candidates) && ! array_key_exists($keys[3], $candidates)) {
            return;
        }

        if (($value = self::lookupEnvValue($sources['env'], $sources['server'], $sources['getenv'], $keys)) !== false && $value !== null) {
            $property = self::castEnvValue($value, $property);
        }
    }

    /**
     * Converts a raw env value to the type of the property's default value.
     *
     * @param bool|float|int|string|null $default
     *
     * @return bool|float|int|string
     */
    private static function castEnvValue(mixed $value, $default)
    {
        if ($value === 'false') {
            return false;
        }

        if ($value === 'true') {
            return true;
        }

        if (is_bool($value)) {
            return $value;
        }

        $value = trim($value, '\'"');

        if (is_int($default)) {
            return (int) $value;
        }

        if (is_float($default)) {
            return (float) $value;
        }

        return $value;
    }

    /**
     * The env var names for a property, in lookup order: short prefix dot and
     * underscore forms, then full prefix dot and underscore forms.
     *
     * @return array{string, string, string, string}
     */
    private static function envKeys(string $property, string $prefix, string $shortPrefix): array
    {
        $underscoreProperty = str_replace('.', '_', $property);

        return [
            "{$shortPrefix}.{$property}",
            "{$shortPrefix}_{$underscoreProperty}",
            "{$prefix}.{$property}",
            "{$prefix}_{$underscoreProperty}",
        ];
    }

    /**
     * Finds the first of the env var names from envKeys(). For each prefix,
     * $_ENV wins over $_SERVER. getenv() is checked last, and is read live
     * when $getenv is null.
     *
     * @param array<array-key, mixed>               $env
     * @param array<array-key, mixed>               $server
     * @param array<array-key, mixed>|null          $getenv
     * @param array{string, string, string, string} $keys
     */
    private static function lookupEnvValue(array $env, array $server, ?array $getenv, array $keys): mixed
    {
        foreach ([[$keys[0], $keys[1]], [$keys[2], $keys[3]]] as $prefixKeys) {
            foreach ([$env, $server] as $source) {
                foreach ($prefixKeys as $key) {
                    if (array_key_exists($key, $source)) {
                        return $source[$key];
                    }
                }
            }
        }

        foreach ($keys as $key) {
            if (($value = $getenv === null ? getenv($key) : ($getenv[$key] ?? false)) !== false) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Returns the env vars that can override a class with these prefixes, by source.
     *
     * @return array{env: array<array-key, mixed>, server: array<array-key, mixed>, getenv: array<array-key, mixed>}
     */
    private static function scopedEnvSources(string $prefix, string $shortPrefix): array
    {
        // Unchanged superglobals share memory with the snapshot, so this check is cheap.
        if ([$_ENV, $_SERVER] !== self::$envSnapshot) {
            self::$envIndex = [];

            foreach (['env' => $_ENV, 'server' => $_SERVER, 'getenv' => getenv()] as $source => $vars) {
                foreach ($vars as $key => $value) {
                    self::$envIndex[self::envGroup((string) $key)][$source][$key] = $value;
                }
            }
        }

        // Re-share memory even when only equal contents were written.
        self::$envSnapshot = [$_ENV, $_SERVER];

        $sources = ['env' => [], 'server' => [], 'getenv' => []];

        foreach ([$shortPrefix, $prefix] as $p) {
            // The prefix itself may contain "_", so match within its group.
            foreach (self::$envIndex[self::envGroup($p)] ?? [] as $source => $vars) {
                foreach ($vars as $key => $value) {
                    if (str_starts_with((string) $key, "{$p}.") || str_starts_with((string) $key, "{$p}_")) {
                        $sources[$source][$key] = $value;
                    }
                }
            }
        }

        return $sources;
    }

    /**
     * Returns every leaf's env var names that exist according to live getenv()
     * calls. Only leaves not seen before are probed.
     *
     * @param class-string            $prefix
     * @param array<array-key, mixed> $values
     *
     * @return list<string>
     */
    private static function findLiveEnvNames(array $values, string $prefix, string $shortPrefix, string $parent = ''): array
    {
        $found = [];

        foreach ($values as $key => $value) {
            $name = $parent === '' ? (string) $key : "{$parent}.{$key}";

            if (is_array($value)) {
                array_push($found, ...self::findLiveEnvNames($value, $prefix, $shortPrefix, $name));

                continue;
            }

            if (! isset(self::$liveEnvNames[$prefix][$name])) {
                self::$liveEnvNames[$prefix][$name] = [];

                // Dot forms are never in the getenv() array, and underscore forms
                // only miss it when the name has " " or "[". Live getenv() calls
                // also match names case-insensitively on Windows, and under
                // Apache mod_php, where they read SetEnv vars (e.g. APP_BASEURL
                // for app_baseURL). So every form is probed there, and under
                // any SAPI not known to match exactly. Elsewhere that would
                // double the getenv() calls on a cold build for no gain.
                $inexactEnv = PHP_OS_FAMILY === 'Windows' || ! in_array(PHP_SAPI, self::EXACT_ENV_SAPIS, true);

                $envKeys = $inexactEnv || strpbrk($name, ' [') !== false
                    ? self::envKeys($name, $prefix, $shortPrefix)
                    : ["{$shortPrefix}.{$name}", "{$prefix}.{$name}"];

                foreach ($envKeys as $envKey) {
                    if (getenv($envKey) !== false) {
                        self::$liveEnvNames[$prefix][$name][] = $envKey;
                    }
                }
            }

            array_push($found, ...self::$liveEnvNames[$prefix][$name]);
        }

        return $found;
    }

    private static function envGroup(string $key): string
    {
        return substr($key, 0, strcspn($key, '._'));
    }

    /**
     * @param class-string $class
     */
    private static function overridesEnvHooks(string $class): bool
    {
        return (new ReflectionMethod($class, 'getEnvValue'))->class !== self::class
            || (new ReflectionMethod($class, 'initEnvValue'))->class !== self::class;
    }

    /**
     * Retrieve an environment-specific configuration setting
     *
     * @return string|null
     */
    protected function getEnvValue(string $property, string $prefix, string $shortPrefix)
    {
        $keys = self::envKeys($property, $prefix, ltrim($shortPrefix, '\\'));

        return self::lookupEnvValue($_ENV, $_SERVER, null, $keys);
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
