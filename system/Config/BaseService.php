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

use CodeIgniter\Autoloader\Autoloader;
use CodeIgniter\Autoloader\FileLocator;
use Config\Autoload;
use Config\Modules;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This is used in place of a Dependency Injection container primarily
 * due to its simplicity, which allows a better long-term maintenance
 * of the applications built on top of CodeIgniter. A bonus side-effect
 * is that IDEs are able to determine what class you are calling
 * whereas with DI Containers there usually isn't a way for them to do this.
 *
 * @see http://blog.ircmaxell.com/2015/11/simple-easy-risk-and-change.html
 * @see http://www.infoq.com/presentations/Simple-Made-Easy
 */
class BaseService
{
	/**
	 * Cache for instance of any services that
	 * have been requested as a "shared" instance.
	 * Keys should be lowercase service names.
	 *
	 * @var array
	 */
	protected static $instances = [];

	/**
	 * Mock objects for testing which are returned if exist.
	 *
	 * @var array
	 */
	protected static $mocks = [];

	/**
	 * Have we already discovered other Services?
	 *
	 * @var boolean
	 */
	protected static $discovered = false;

	/**
	 * A cache of other service classes we've found.
	 *
	 * @var array
	 */
	protected static $services = [];

	//--------------------------------------------------------------------

	/**
	 * Returns a shared instance of any of the class' services.
	 *
	 * $key must be a name matching a service.
	 *
	 * @param string $key
	 * @param array  ...$params
	 *
	 * @return mixed
	 */
	protected static function getSharedInstance(string $key, ...$params)
	{
		$key = strtolower($key);

		// Returns mock if exists
		if (isset(static::$mocks[$key]))
		{
			return static::$mocks[$key];
		}

		if (! isset(static::$instances[$key]))
		{
			// Make sure $getShared is false
			array_push($params, false);

			static::$instances[$key] = static::$key(...$params);
		}

		return static::$instances[$key];
	}

	//--------------------------------------------------------------------

	/**
	 * The Autoloader class is the central class that handles our
	 * spl_autoload_register method, and helper methods.
	 *
	 * @param boolean $getShared
	 *
	 * @return \CodeIgniter\Autoloader\Autoloader
	 */
	public static function autoloader(bool $getShared = true)
	{
		if ($getShared)
		{
			if (empty(static::$instances['autoloader']))
			{
				static::$instances['autoloader'] = new Autoloader();
			}

			return static::$instances['autoloader'];
		}

		return new Autoloader();
	}

	//--------------------------------------------------------------------

	/**
	 * The file locator provides utility methods for looking for non-classes
	 * within namespaced folders, as well as convenience methods for
	 * loading 'helpers', and 'libraries'.
	 *
	 * @param boolean $getShared
	 *
	 * @return \CodeIgniter\Autoloader\FileLocator
	 */
	public static function locator(bool $getShared = true)
	{
		if ($getShared)
		{
			if (empty(static::$instances['locator']))
			{
				static::$instances['locator'] = new FileLocator(static::autoloader());
			}

			return static::$instances['locator'];
		}

		return new FileLocator(static::autoloader());
	}

	//--------------------------------------------------------------------

	/**
	 * Provides the ability to perform case-insensitive calling of service
	 * names.
	 *
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return mixed
	 */
	public static function __callStatic(string $name, array $arguments)
	{
		$name = strtolower($name);

		if (method_exists(Services::class, $name))
		{
			return Services::$name(...$arguments);
		}

		return static::discoverServices($name, $arguments);
	}

	//--------------------------------------------------------------------

	/**
	 * Reset shared instances and mocks for testing.
	 *
	 * @param boolean $init_autoloader Initializes autoloader instance
	 */
	public static function reset(bool $init_autoloader = false)
	{
		static::$mocks = [];

		static::$instances = [];

		if ($init_autoloader)
		{
			static::autoloader()->initialize(new Autoload(), new Modules());
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Inject mock object for testing.
	 *
	 * @param string $name
	 * @param mixed  $mock
	 */
	public static function injectMock(string $name, $mock)
	{
		static::$mocks[strtolower($name)] = $mock;
	}

	//--------------------------------------------------------------------

	/**
	 * Will scan all psr4 namespaces registered with system to look
	 * for new Config\Services files. Caches a copy of each one, then
	 * looks for the service method in each, returning an instance of
	 * the service, if available.
	 *
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return mixed
	 */
	protected static function discoverServices(string $name, array $arguments)
	{
		if (! static::$discovered)
		{
			$config = config('Modules');

			if ($config->shouldDiscover('services'))
			{
				$locator = static::locator();
				$files   = $locator->search('Config/Services');

				if (empty($files))
				{
					// no files at all found - this would be really, really bad
					return null;
				}

				// Get instances of all service classes and cache them locally.
				foreach ($files as $file)
				{
					$classname = $locator->getClassname($file);

					if (! in_array($classname, ['CodeIgniter\\Config\\Services'], true))
					{
						static::$services[] = new $classname();
					}
				}
			}

			static::$discovered = true;
		}

		if (! static::$services)
		{
			// we found stuff, but no services - this would be really bad
			return null;
		}

		// Try to find the desired service method
		foreach (static::$services as $class)
		{
			if (method_exists($class, $name))
			{
				return $class::$name(...$arguments);
			}
		}

		return null;
	}
}
