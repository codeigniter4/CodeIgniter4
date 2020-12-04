<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Autoloader;

use Config\Autoload;
use Config\Modules;
use InvalidArgumentException;

/**
 * CodeIgniter Autoloader
 *
 * An autoloader that uses both PSR4 autoloading, and traditional classmaps.
 *
 * Given a foo-bar package of classes in the file system at the following paths:
 *
 * 	/path/to/packages/foo-bar/
 *  	/src
 *			Baz.php  # Foo\Bar\Baz
 *		Qux/
 *			Quux.php # Foo\Bar\Qux\Quux
 *
 * you can add the path to the configuration array that is passed in the constructor.
 * The Config array consists of 2 primary keys, both of which are associative arrays:
 * 'psr4', and 'classmap'.
 *
 *	$Config = [
 *		'psr4' => [
 *			'Foo\Bar' => '/path/to/packages/foo-bar'
 *		],
 *		'classmap' => [
 *			'MyClass' => '/path/to/class/file.php'
 *		]
 *	];
 *
 * Example:
 *
 *	$config = [ ... ]; // Configuration array
 *	$loader = new Autoloader($config);
 *	$loader->register(); // Register the autoloader
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
	 * Reads in the configuration array (described above) and stores the
	 * valid parts that we'll need.
	 *
	 * @param Autoload $config
	 * @param Modules  $modules
	 *
	 * @return $this
	 */
	public function initialize(Autoload $config, Modules $modules)
	{
		// We have to have one or the other, though we don't enforce the need
		// to have both present in order to work.
		if (empty($config->psr4) && empty($config->classmap))
		{
			throw new InvalidArgumentException('Config array must contain either the \'psr4\' key or the \'classmap\' key.');
		}

		if (isset($config->psr4))
		{
			$this->addNamespace($config->psr4);
		}

		if (isset($config->classmap))
		{
			$this->classmap = $config->classmap;
		}

		// Load through Composer's namespaces if available.
		if ($modules->discoverInComposer)
		{
			$this->discoverComposerNamespaces();
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Register the loader with the SPL autoloader stack.
	 * 
	 * @see http://php.net/manual/en/function.spl-autoload.php#78053
	 */
	public function register()
	{
		// Since the default file extensions are searched in order of .inc
		// then .php, but we always use .php, put the .php extension first
		// to eek out a bit better performance.
		spl_autoload_extensions('.php,.inc');

		// Prepend the PSR4 autoloader for maximum performance.
		spl_autoload_register([$this, 'loadClass'], true, true); // @phpstan-ignore-line

		// Prepend the classmap autoloader.
		spl_autoload_register([$this, 'loadClassmap'], true, true); // @phpstan-ignore-line
	}

	//--------------------------------------------------------------------

	/**
	 * Registers namespaces with the autoloader.
	 *
	 * @param array|string $namespace
	 * @param string|null  $path
	 *
	 * @return $this
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
					foreach ($path as $directory)
					{
						$this->prefixes[$prefix][] = rtrim($directory, '\\/') . DIRECTORY_SEPARATOR;
					}

					continue;
				}

				$this->prefixes[$prefix][] = rtrim($path, '\\/') . DIRECTORY_SEPARATOR;
			}
		}
		else
		{
			$this->prefixes[trim($namespace, '\\')][] = rtrim($path, '\\/') . DIRECTORY_SEPARATOR;
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Get namespaces with prefixes as keys and paths as values.
	 *
	 * If a prefix param is set, returns only paths to the given prefix.
	 *
	 * @param string|null $prefix
	 *
	 * @return array
	 */
	public function getNamespace(string $prefix = null): array
	{
		if (is_null($prefix))
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
	 * @return $this
	 */
	public function removeNamespace(string $namespace)
	{
		if (isset($this->prefixes[trim($namespace, '\\')]))
		{
			unset($this->prefixes[trim($namespace, '\\')]);
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Loads the class file for the given class name.
	 *
	 * @param string $class The fully qualified class name.
	 *
	 * @return string|false The mapped file on success, or false on failure.
	 */
	public function loadClass(string $class)
	{
		$class = str_ireplace('.php', '', trim($class, '\\'));

		$mappedFile = $this->loadInNamespace($class);

		// Look in the common folders.
		if (! $mappedFile)
		{
			$mappedFile = $this->loadLegacy($class);
		}

		return $mappedFile;
	}

	//--------------------------------------------------------------------

	/**
	 * Loads the classmap file for the given class name.
	 *
	 * @param string $class The class name.
	 *
	 * @return string|false The mapped file on success, or false on failure.
	 */
	public function loadClassmap(string $class)
	{
		$classmap = is_array($this->classmap) ? $this->classmap : [];

		if (empty($classmap[$class]))
		{
			return false;
		}

		include_once $classmap[$class];
	}

	//--------------------------------------------------------------------

	/**
	 * Loads the class file for the given class name.
	 *
	 * @param string $class The fully-qualified class name
	 *
	 * @return string|false The mapped file on success, or false on failure.
	 */
	protected function loadInNamespace(string $class)
	{
		if (strpos($class, '\\') === false)
		{
			$class    = 'Config\\' . $class;
			$filePath = APPPATH . str_replace('\\', DIRECTORY_SEPARATOR, $class);
			$filename = $this->includeFile($filePath);

			if ($filename)
			{
				return $filename;
			}

			return false;
		}

		foreach ($this->prefixes as $namespace => $directories)
		{
			foreach ($directories as $directory)
			{
				if (strpos($class, $namespace) === 0)
				{
					$filename = $this->includeFile(
						rtrim($directory, '\\/') . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($namespace)))
					);

					if ($filename)
					{
						return $filename;
					}
				}
			}
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to load the class from common locations in previous version
	 * of CodeIgniter, namely 'app/Libraries', and 'app/Models'.
	 *
	 * @param string $class The class name. This typically haven't a namespace.
	 *
	 * @return string|false The mapped file on success, or false on failure
	 */
	protected function loadLegacy(string $class)
	{
		// If there is a namespace on this class, then we
		// cannot load it from traditional locations.
		if (strpos($class, '\\') !== false)
		{
			return false;
		}

		$class = str_replace('\\', DIRECTORY_SEPARATOR, $class);

		$paths = [
			APPPATH . 'Controllers/',
			APPPATH . 'Libraries/',
			APPPATH . 'Models/',
		];

		foreach ($paths as $path)
		{
			if ($file = $this->includeFile($path . $class))
			{
				return $file;
			}
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * A central way to include a file.
	 * Split out primarily for testing purposes.
	 *
	 * @param string $file
	 * @param string $ext
	 *
	 * @return string|false The file on success, or false on failure
	 */
	protected function includeFile(string $file, string $ext = '.php')
	{
		$file = $this->sanitizeFilename($file) . $ext;

		if (is_file($file))
		{
			include_once $file;

			return $file;
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Locates all PSR4 compatible namespaces from Composer.
	 * 
	 * @return void
	 */
	protected function discoverComposerNamespaces(): void
	{
		if (is_file(COMPOSER_PATH))
		{
			$composer = include COMPOSER_PATH;

			$paths = $composer->getPrefixesPsr4();
			unset($composer);

			// Get rid of CodeIgniter so we don't have duplicates
			if (isset($paths['CodeIgniter\\']))
			{
				unset($paths['CodeIgniter\\']);
			}

			// Composer stores namespaces with trailing slash. We don't.
			$newPaths = [];

			foreach ($paths as $key => $value)
			{
				$newPaths[rtrim($key, '\\ ')] = $value;
			}

			$this->prefixes = array_merge($this->prefixes, $newPaths);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Sanitizes a filename
	 *
	 * Only allow characters deemed safe for POSIX portable filenames, plus
	 * the forward slash for directory separators since this might be a path.
	 * Removes special characters that are illegal in filenames on certain
	 * operating systems and special characters requiring special escaping
	 * to manipulate at the command line. Replaces spaces and consecutive
	 * dashes with a single dash.
	 * 
	 * Trim period, dash and underscore from beginning and end of filename.
	 * 
	 * @see http://pubs.opengroup.org/onlinepubs/9699919799/basedefs/V1_chap03.html#tag_03_278
	 *
	 * @param string $filename The file name to be sanitized
	 *
	 * @return string The sanitized file name
	 */
	public function sanitizeFilename(string $filename): string
	{
		return trim(preg_replace('/[^0-9\p{L}\s\/\-\_\.\:\\\\]/u', '', $filename), '.-_');
	}
}