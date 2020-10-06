<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Config;

use Config\Services;

/**
 * Factories for creating instances.
 *
 * Factories allows dynamic loading of components by their path
 * and name. The "shared instance" implementation provides a
 * large performance boost and helps keep code clean of lengthy
 * instantiation checks.
 */
class Factories
{
	/**
	 * Store of component configurations, usually
	 * from CodeIgniter\Config\Factory.
	 *
	 * @var array<string, array>
	 */
	protected static $configs = [];

	/**
	 * Explicit configuration for the Config
	 * component to prevent logic loops.
	 *
	 * @var array<string, mixed>
	 */
	private static $configValues = [
		'component'  => 'config',
		'path'       => 'Config',
		'instanceOf' => null,
		'prefersApp' => true,
	];

	/**
	 * Mapping of class basenames (no namespace) to
	 * their instances.
	 *
	 * @var array<string, string[]>
	 */
	protected static $basenames = [];

	/**
	 * Store for instances of any component that
	 * has been requested as a "shared".
	 * A multi-dimensional array with components as
	 * keys to the array of name-indexed instances.
	 *
	 * @var array<string, array>
	 */
	protected static $instances = [];

	//--------------------------------------------------------------------

	/**
	 * Loads instances based on the method component name. Either
	 * creates a new instance or returns an existing shared instance.
	 *
	 * @param string $component
	 * @param array  $arguments
	 *
	 * @return mixed
	 */
	public static function __callStatic(string $component, array $arguments)
	{
		// Load the component-specific configuration
		$config = self::getConfig(strtolower($component));

		// First argument is the name, second is whether to use a shared instance
		$name      = trim(array_shift($arguments), '\\ ');
		$getShared = array_shift($arguments) ?? true;

		if (! $getShared)
		{
			if ($class = self::locateClass($config, $name))
			{
				return new $class(...$arguments);
			}

			return null;
		}

		$basename = self::getBasename($name);

		// Check for an existing instance
		if (isset(self::$basenames[$config['component']][$basename]))
		{
			$class = self::$basenames[$config['component']][$basename];
		}
		else
		{
			// Try to locate the class
			if (! $class = self::locateClass($config, $name))
			{
				return null;
			}

			self::$instances[$config['component']][$class]    = new $class(...$arguments);
			self::$basenames[$config['component']][$basename] = $class;
		}

		return self::$instances[$config['component']][$class];
	}

	/**
	 * Finds a component class
	 *
	 * @param array  $config The array of component-specific directives
	 * @param string $name   Class name, namespace optional
	 *
	 * @return string|null
	 */
	protected static function locateClass(array $config, string $name): ?string
	{
		// Check for low-hanging fruit
		if (class_exists($name) && self::verifyPrefersApp($config, $name) && self::verifyInstanceOf($config, $name))
		{
			return $name;
		}

		$basename = self::getBasename($name);

		// Look for an App version if requested
		$appname = $config['component'] === 'config' ? 'Config\\' . $basename : APP_NAMESPACE . '\\' . $config['path'] . '\\' . $basename;
		if ($config['prefersApp'] && class_exists($appname))
		{
			return $appname;
		}

		// Have to do this the hard way...
		$locator = Services::locator();

		// Check if the class was namespaced
		if (strpos($name, '\\') !== false)
		{
			if (! $file = $locator->locateFile($name, $config['path']))
			{
				return null;
			}

			$files = [$file];
		}
		// No namespace? Search for it
		else
		{
			// Check all namespaces, prioritizing App and modules
			if (! $files = $locator->search($config['path'] . DIRECTORY_SEPARATOR . $name))
			{
				return null;
			}
		}

		// Return the first valid class
		foreach ($files as $file)
		{
			$class = $locator->getClassname($file);

			if ($class && self::verifyInstanceOf($config, $class))
			{
				return $class;
			}
		}

		return null;
	}

	//--------------------------------------------------------------------

	/**
	 * Verifies that a class & config satisfy the "prefersApp" option
	 *
	 * @param array  $config The array of component-specific directives
	 * @param string $name   Class name, namespace optional
	 *
	 * @return boolean
	 */
	protected static function verifyPrefersApp(array $config, string $name): bool
	{
		// Anything without that restriction passes
		if (! $config['prefersApp'])
		{
			return true;
		}

		// Special case for Config since its App namespace is actually \Config
		if ($config['component'] === 'config')
		{
			return strpos($name, 'Config') === 0;
		}

		return strpos($name, APP_NAMESPACE) === 0;
	}

	/**
	 * Verifies that a class & config satisfy the "instanceOf" option
	 *
	 * @param array  $config The array of component-specific directives
	 * @param string $name   Class name, namespace optional
	 *
	 * @return boolean
	 */
	protected static function verifyInstanceOf(array $config, string $name): bool
	{
		// Anything without that restriction passes
		if (! $config['instanceOf'])
		{
			return true;
		}

		return is_a($name, $config['instanceOf'], true);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the component-specific configuration
	 *
	 * @param string $component Lowercase, plural component name
	 *
	 * @return array<string, mixed>
	 */
	public static function getConfig(string $component): array
	{
		$component = strtolower($component);

		// Check for a stored version
		if (isset(self::$configs[$component]))
		{
			return self::$configs[$component];
		}

		// Handle Config as a special case to prevent logic loops
		if ($component === 'config')
		{
			$values = self::$configValues;
		}
		// Load values from the best Factory configuration (will include Registrars)
		else
		{
			$values = config('Factory')->$component ?? [];
		}

		return self::setConfig($component, $values);
	}

	/**
	 * Normalizes, stores, and returns the configuration for a specific component
	 *
	 * @param string $component Lowercase, plural component name
	 * @param array  $values
	 *
	 * @return array<string, mixed> The result after applying defaults and normalization
	 */
	public static function setConfig(string $component, array $values): array
	{
		// Allow the config to replace the component name, to support "aliases"
		$values['component'] = strtolower($values['component'] ?? $component);

		// Reset this component so instances can be rediscovered with the updated config
		self::reset($values['component']);

		// If no path was available then use the component
		$values['path'] = trim($values['path'] ?? ucfirst($values['component']), '\\ ');

		// Add defaults for any missing values
		$values = array_merge(Factory::$default, $values);

		// Store the result to the supplied name and potential alias
		self::$configs[$component]           = $values;
		self::$configs[$values['component']] = $values;

		return $values;
	}

	/**
	 * Resets the static arrays, optionally just for one component
	 *
	 * @param string $component Lowercase, plural component name
	 */
	public static function reset(string $component = null)
	{
		if ($component)
		{
			unset(static::$configs[$component]);
			unset(static::$basenames[$component]);
			unset(static::$instances[$component]);

			return;
		}

		static::$configs   = [];
		static::$basenames = [];
		static::$instances = [];
	}

	/**
	 * Helper method for injecting mock instances
	 *
	 * @param string $component Lowercase, plural component name
	 * @param string $name      The name of the instance
	 * @param object $instance
	 */
	public static function injectMock(string $component, string $name, $instance)
	{
		// Force a configuration to exist for this component
		$component = strtolower($component);
		self::getConfig($component);

		$class    = get_class($instance);
		$basename = self::getBasename($name);

		self::$instances[$component][$class]    = $instance;
		self::$basenames[$component][$basename] = $class;
	}

	/**
	 * Gets a basename from a class name, namespaced or not.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public static function getBasename(string $name): string
	{
		// Determine the basename
		if ($basename = strrchr($name, '\\'))
		{
			return substr($basename, 1);
		}

		return $name;
	}
}
