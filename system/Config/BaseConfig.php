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

            if ($this instanceof Encryption && $property === 'key') {
                if (str_starts_with($this->{$property}, 'hex2bin:')) {
                    // Handle hex2bin prefix
                    $this->{$property} = hex2bin(substr($this->{$property}, 8));
                } elseif (str_starts_with($this->{$property}, 'base64:')) {
                    // Handle base64 prefix
                    $this->{$property} = base64_decode(substr($this->{$property}, 7), true);
                }
            }
        }
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
                if (isset($this->{$property}) && is_array($this->{$property}) && is_array($value)) {
                    $this->{$property} = array_merge($this->{$property}, $value);
                } else {
                    $this->{$property} = $value;
                }
            }
        }
    }
}
