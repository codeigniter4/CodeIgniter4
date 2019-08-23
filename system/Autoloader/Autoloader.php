<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
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
 * @copyright  2014-2019 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Autoloader;

/**
 * CodeIgniter Autoloader
 *
 * An autoloader that uses both PSR4 autoloading, and traditional classmaps.
 *
 * Given a foo-bar package of classes in the file system at the following paths:
 *
 *      /path/to/packages/foo-bar/
 *          /src
 *              Baz.php         # Foo\Bar\Baz
 *              Qux/
 *                  Quux.php    # Foo\Bar\Qux\Quux
 *
 * you can add the path to the configuration array that is passed in the constructor.
 * The Config array consists of 2 primary keys, both of which are associative arrays:
 * 'psr4', and 'classmap'.
 *
 *      $Config = [
 *          'psr4' => [
 *              'Foo\Bar'   => '/path/to/packages/foo-bar'
 *          ],
 *          'classmap' => [
 *              'MyClass'   => '/path/to/class/file.php'
 *          ]
 *      ];
 *
 * Example:
 *
 *      <?php
 *      // our configuration array
 *      $Config = [ ... ];
 *      $loader = new \CodeIgniter\Autoloader\Autoloader($Config);
 *
 *      // register the autoloader
 *      $loader->register();
 *
 * @package CodeIgniter\Autoloader
 */
class Autoloader
{
	/**
	 * Stores namespaces as key, and path as values.
	 *
	 * @var array
	 */
	protected $prefixes = [];

	/**
	 * Stores class name as key, and path as values.
	 *
	 * @var array
	 */
	protected $classmap = [];

	//--------------------------------------------------------------------

	/**
	 * Reads in the configuration array (described above) and stores
	 * the valid parts that we'll need.
	 *
	 * @param \Config\Autoload $config
	 * @param \Config\Modules  $moduleConfig
	 *
	 * @return $this
	 */
	public function initialize(\Config\Autoload $config, \Config\Modules $moduleConfig)
	{
		// We have to have one or the other, though we don't enforce the need
		// to have both present in order to work.
		if (empty($config->psr4) && empty($config->classmap))
		{
			throw new \InvalidArgumentException('Config array must contain either the \'psr4\' key or the \'classmap\' key.');
		}

		if (isset($config->psr4))
		{
			$this->addNamespace($config->psr4);
		}

		if (isset($config->classmap))
		{
			$this->classmap = $config->classmap;
		}

		// Should we load through Composer's namespaces, also?
		if ($moduleConfig->discoverInComposer)
		{
			$this->discoverComposerNamespaces();
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Register the loader with the SPL autoloader stack.
	 */
	public function register()
	{
		// Since the default file extensions are searched
		// in order of .inc then .php, but we always use .php,
		// put the .php extension first to eek out a bit
		// better performance.
		// http://php.net/manual/en/function.spl-autoload.php#78053
		spl_autoload_extensions('.php,.inc');

		// Prepend the PSR4  autoloader for maximum performance.
		spl_autoload_register([$this, 'loadClass'], true, true);

		// Now prepend another loader for the files in our class map.
		$config = is_array($this->classmap) ? $this->classmap : [];

		spl_autoload_register(function ($class) use ($config) {
			if (empty($config[$class]))
			{
				return false;
			}

			include_once $config[$class];
		}, true, // Throw exception
			true // Prepend
		);
	}

	//--------------------------------------------------------------------

	/**
	 * Registers namespaces with the autoloader.
	 *
	 * @param array|string $namespace
	 * @param string       $path
	 *
	 * @return Autoloader
	 */
	public function addNamespace($namespace, string $path = null)
	{
		if (is_array($namespace))
		{
			foreach ($namespace as $prefix => $path)
			{
				$prefix = trim($prefix, '\\');

				if (is_array($path))
				{
					foreach ($path as $dir)
					{
						$this->prefixes[$prefix][] = rtrim($dir, '/') . '/';
					}

					continue;
				}

				$this->prefixes[$prefix][] = rtrim($path, '/') . '/';
			}
		}
		else
		{
			$this->prefixes[trim($namespace, '\\')][] = rtrim($path, '/') . '/';
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Get namespaces with prefixes as keys and paths as values.
	 *
	 * If a prefix param is set, returns only paths to the given prefix.
	 *
	 * @var string|null $prefix
	 *
	 * @return array
	 */
	public function getNamespace(string $prefix = null)
	{
		if ($prefix === null)
		{
			return $this->prefixes;
		}

		return $this->prefixes[trim($prefix, '\\')] ?? [];
	}

	//--------------------------------------------------------------------

	/**
	 * Removes a single namespace from the psr4 settings.
	 *
	 * @param string $namespace
	 *
	 * @return Autoloader
	 */
	public function removeNamespace(string $namespace)
	{
		unset($this->prefixes[trim($namespace, '\\')]);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Loads the class file for a given class name.
	 *
	 * @param string $class The fully qualified class name.
	 *
	 * @return string|false The mapped file on success, or boolean false
	 *                          on failure.
	 */
	public function loadClass(string $class)
	{
		$class = trim($class, '\\');
		$class = str_ireplace('.php', '', $class);

		$mapped_file = $this->loadInNamespace($class);

		// Nothing? One last chance by looking
		// in common CodeIgniter folders.
		if (! $mapped_file)
		{
			$mapped_file = $this->loadLegacy($class);
		}

		return $mapped_file;
	}

	//--------------------------------------------------------------------

	/**
	 * Loads the class file for a given class name.
	 *
	 * @param string $class The fully-qualified class name
	 *
	 * @return string|false The mapped file name on success, or boolean false on fail
	 */
	protected function loadInNamespace(string $class)
	{
		if (strpos($class, '\\') === false)
		{
			return false;
		}

		foreach ($this->prefixes as $namespace => $directories)
		{
			foreach ($directories as $directory)
			{
				$directory = rtrim($directory, '/');

				if (strpos($class, $namespace) === 0)
				{
					$filePath = $directory . str_replace('\\', '/',
							substr($class, strlen($namespace))) . '.php';
					$filename = $this->requireFile($filePath);

					if ($filename)
					{
						return $filename;
					}
				}
			}
		}

		// never found a mapped file
		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to load the class from common locations in previous
	 * version of CodeIgniter, namely 'app/Libraries', and
	 * 'app/Models'.
	 *
	 * @param string $class The class name. This typically should NOT have a namespace.
	 *
	 * @return mixed    The mapped file name on success, or boolean false on failure
	 */
	protected function loadLegacy(string $class)
	{
		// If there is a namespace on this class, then
		// we cannot load it from traditional locations.
		if (strpos($class, '\\') !== false)
		{
			return false;
		}

		$paths = [
			APPPATH . 'Controllers/',
			APPPATH . 'Libraries/',
			APPPATH . 'Models/',
		];

		$class = str_replace('\\', '/', $class) . '.php';

		foreach ($paths as $path)
		{
			if ($file = $this->requireFile($path . $class))
			{
				return $file;
			}
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * A central way to require a file is loaded. Split out primarily
	 * for testing purposes.
	 *
	 * @param string $file
	 *
	 * @return string|false The filename on success, false if the file is not loaded
	 */
	protected function requireFile(string $file)
	{
		$file = $this->sanitizeFilename($file);

		if (is_file($file))
		{
			require_once $file;

			return $file;
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Sanitizes a filename, replacing spaces with dashes.
	 *
	 * Removes special characters that are illegal in filenames on certain
	 * operating systems and special characters requiring special escaping
	 * to manipulate at the command line. Replaces spaces and consecutive
	 * dashes with a single dash. Trim period, dash and underscore from beginning
	 * and end of filename.
	 *
	 * @param string $filename
	 *
	 * @return string       The sanitized filename
	 */
	public function sanitizeFilename(string $filename): string
	{
		// Only allow characters deemed safe for POSIX portable filenames.
		// Plus the forward slash for directory separators since this might
		// be a path.
		// http://pubs.opengroup.org/onlinepubs/9699919799/basedefs/V1_chap03.html#tag_03_278
		// Modified to allow backslash and colons for on Windows machines.
		$filename = preg_replace('/[^a-zA-Z0-9\s\/\-\_\.\:\\\\]/', '', $filename);

		// Clean up our filename edges.
		$filename = trim($filename, '.-_');

		return $filename;
	}

	//--------------------------------------------------------------------

	/**
	 * Locates all PSR4 compatible namespaces from Composer.
	 */
	protected function discoverComposerNamespaces()
	{
		if (! is_file(COMPOSER_PATH))
		{
			return false;
		}

		$composer = include COMPOSER_PATH;

		$paths = $composer->getPrefixesPsr4();
		unset($composer);

		// Get rid of CodeIgniter so we don't have duplicates
		if (isset($paths['CodeIgniter\\']))
		{
			unset($paths['CodeIgniter\\']);
		}

		// Composer stores paths with trailng slash. We don't.
		$newPaths = [];
		foreach ($paths as $key => $value)
		{
			$newPaths[rtrim($key, '\\ ')] = $value;
		}

		$this->prefixes = array_merge($this->prefixes, $newPaths);
	}
}
