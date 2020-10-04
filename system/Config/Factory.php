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

use CodeIgniter\Autoloader\FileLocator;
use Config\Factories as FactoriesConfig;

/**
 * Dynamic Component Factory.
 *
 * Factories allow dynamic loading of components by their path
 * and name. The "shared instance" implementation provides a
 * large performance boost and helps keep code clean of lengthy
 * instantiation checks.
 */
class Factory
{
	/**
	 * Cache for instance of any component that
	 * has been requested as a "shared" instance.
	 * A multi-dimensional array with components as
	 * keys to the underlying array of instances.
	 *
	 * @var array<string, array>
	 */
	public static $instances = [];

	/**
	 * Mapping of class basenames (no namespace) to
	 * their instances.
	 *
	 * @var array<string, string[]>
	 */
	public static $basenames = [];

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
		$component = strtolower($component);

		// Load the component-specific configuration
		$config = self::getConfig($component);

		// First argument is the name, second is whether to use a shared instance
		$name      = array_shift($arguments);
		$getShared = array_shift($arguments);

		if (! $getShared)
		{
			if ($class = self::locateClass($config, $name))
			{
				return new $class(...$arguments);
			}

			return null;
		}

		$basename = self::basename($name);

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
		if (class_exists($name)
			&& (! $config['prefersApp'] || strpos($name, APP_NAMESPACE) === 0)
			&& (! $config['instanceOf'] || is_a($name, $config['instanceOf']))
		)
		{
			return $name;
		}

		$basename = self::basename($name);

		// Look for an App version if requested
		$appName = APP_NAMESPACE . DIRECTORY_SEPARATOR . $config['path'] . $basename;
		if ($config['prefersApp'] && class_exists($appName))
		{
			return $appName;
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
		}
		// No namespace? Search for it
		else
		{
			// Check all namespaces, prioritizing App and modules
			if (! $files = $locator->search($config['path'] . DIRECTORY_SEPARATOR . $name))
			{
				return null;
			}

			// Use the first match - prioritizes app, then modules, then system
			$file = reset($files);
		}

		if (! $class = $locator->getClassname($file))
		{
			return null;
		}

		return $class;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the component-specific configuration
	 *
	 * @param string $component Lowercase, plural component name
	 *
	 * @return array<string, mixed>
	 */
	protected static function getConfig(string $component): array
	{
		// Load the best Factories config, including Registrars
		$config = config('Factories');

		// Check for an explicit match
		$values = $config->$component ?? [];

		// Add defaults for any missing values
		$values = array_merge($config::$default, $values);

		// If no path was available then guess it based on the component
		$values['path'] = $values['path'] ?? ucfirst($component);

		// Allow the config to replace the component name, to support "aliases"
		$values['component'] = strtolower($values['component'] ?? $component);

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
			unset(static::$instances[$component]);
			unset(static::$basenames[$component]);

			return;
		}

		static::$instances = [];
		static::$basenames = [];
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
		$class = get_class($instance);

		self::$instances[$component][$class]                = $instance;
		self::$basenames[$component][self::basename($name)] = $class;
	}

	/**
	 * Gets a basename from a class name, namespaced or not.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public static function basename(string $name): string
	{
		// Determine the basename
		if ($basename = strrchr($name, '\\'))
		{
			return substr($basename, 1);
		}

		return $name;
	}
}
