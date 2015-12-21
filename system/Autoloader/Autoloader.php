<?php namespace CodeIgniter\Autoloader;

/**
 * Class Autoloader
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
 * The config array consists of 2 primary keys, both of which are associative arrays:
 * 'psr4', and 'classmap'.
 *
 *      $config = [
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
 *      $config = [ ... ];
 *      $loader = new \CodeIgniter\Autoloader\Autoloader($config);
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
	 * @param $config
	 */
	public function initialize(\App\Config\AutoloadConfig $config)
	{
		// We have to have one or the other, though we don't enforce the need
		// to have both present in order to work.
		if (empty($config->psr4) && empty($config->classmap))
		{
			throw new \InvalidArgumentException('Config array must contain either the \'psr4\' key or the \'classmap\' key.');
		}

		if (isset($config->psr4))
		{
			$this->prefixes = $config->psr4;
		}

		if (isset($config->classmap))
		{
			$this->classmap = $config->classmap;
		}

		unset($config);
	}

	//--------------------------------------------------------------------

	/**
	 * Register the loader with the SPL autoloader stack.
	 *
	 * @codeCoverageIgnore
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

		spl_autoload_register(function ($class) use ($config)
		{
			if ( ! array_key_exists($class, $config))
			{
				return false;
			}

			if ( ! file_exists($config[$class]))
			{
				return false;
			}

			include $config[$class];
		},
			true,   // Throw exception
			true    // Prepend
		);
	}

	//--------------------------------------------------------------------

	/**
	 * Registers a namespace with the autoloader.
	 *
	 * @param $namespace
	 * @param $path
	 *
	 * @return $this
	 */
	public function addNamespace($namespace, $path)
	{
		$this->prefixes[$namespace] = $path;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Removes a single namespace from the psr4 settings.
	 *
	 * @param $namespace
	 *
	 * @return $this
	 */
	public function removeNamespace($namespace)
	{
		unset($this->prefixes[$namespace]);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Loads the class file for a given class name.
	 *
	 * @param string $class The fully qualified class name.
	 *
	 * @return mixed            The mapped file on success, or boolean false
	 *                          on failure.
	 */
	public function loadClass($class)
	{
		$class = trim($class, '\\');
		$class = str_ireplace('.php', '', $class);

		$mapped_file = $this->loadInNamespace($class);

		// Nothing? One last chance by looking
		// in common CodeIgniter folders.
		if ( ! $mapped_file)
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
	 * @return mixed            The mapped file name on success, or boolean false on fail
	 */
	protected function loadInNamespace($class)
	{
		// the current namespace prefix
		$prefix = $class;

		// work backwards through the namespace names of the fully-qualified
		// class name to find a mapped file name.
		while (false !== $pos = strrpos($prefix, '\\'))
		{
			// retain trailing namespace separator in the prefix
			$prefix = substr($class, 0, $pos);

			// the rest is the relative class name
			$relative_class = substr($class, $pos + 1);

			// try to load the mapped file for prefix and relative class
			$mapped_file = $this->loadMappedFile($prefix, $relative_class);

			if ($mapped_file)
			{
				return $mapped_file;
			}

			// remove the trailing namespace separator for the next iteration
			// of strpos()
			$prefix = rtrim($prefix, '\\');
		}

		// never found a mapped file
		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Loads the mapped file for a namespace prefix and relative class.
	 *
	 * @param string $prefix         The namespace prefix
	 * @param string $relative_class The relative class name
	 *
	 * @return mixed                    Boolean false if no mapped file can be loaded,
	 *                                  or the name of the mapped file that was loaded.
	 */
	protected function loadMappedFile($prefix, $relative_class)
	{
		$prefix = rtrim($prefix, '\\');

		// are there any base directories for this namespace prefix?
		if ( ! isset($this->prefixes[$prefix]))
		{
			return false;
		}

		// look through base directories for this namespace prefix
		$file = $this->prefixes[$prefix].'/'.str_replace('\\', '/', $relative_class).'.php';

		return $this->requireFile($file);
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to load the class from common locations in previous
	 * version of CodeIgniter, namely 'application/libraries', and
	 * 'application/models'.
	 *
	 * @param $class    The class name. This typically should NOT have a namespace.
	 *
	 * @return mixed    The mapped file name on success, or boolean false on failure
	 */
	protected function loadLegacy($class)
	{
		// If there is a namespace on this class, then
		// we cannot load it from traditional locations.
		if (strpos('\\', $class) !== false)
		{
			return false;
		}

		$paths = [
			APPPATH.'controllers/',
			APPPATH.'libraries/',
			APPPATH.'models/',
		];

		$class = str_replace('\\', '/', $class).'.php';

		foreach ($paths as $path)
		{
			if ($file = $this->requireFile($path.$class))
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
	 * @codeCoverageIgnore
	 *
	 * @param $file
	 *
	 * @return bool
	 */
	protected function requireFile($file)
	{
		$file = $this->sanitizeFilename($file);

		if (file_exists($file))
		{
			require $file;

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
	 * @todo Move to a helper?
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
		$filename = preg_replace('/[^a-zA-Z0-9\s\/\-\_\.]/', '', $filename);

		// Replace one or more spaces with a dash
		$filename = preg_replace('/[\s-]+/', '-', $filename);

		// Clean up our filename edges.
		$filename = trim($filename, '.-_');

		return $filename;
	}

	//--------------------------------------------------------------------

}
