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
	public function __construct($config)
	{
		// We have to have one or the other, though we don't enforce the need
		// to have both present in order to work.
		if (empty($config['psr4']) && empty($config['classmap']))
		{
			throw new \InvalidArgumentException('Config array must contain either the \'psr4\' key or the \'classmap\' key.');
		}

		if (isset($config['psr4']))
		{
			$this->prefixes = $config['psr4'];
		}

		if (isset($config['classmap']))
		{
			$this->classmap = $config['classmap'];
		}

		unset($config);
	}

	//--------------------------------------------------------------------

	/**
	 * Register the loader with the SPL autoloader stack.
	 */
	public function register()
	{
		spl_autoload_register([$this, 'loadClass']);
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
		// Try loading through class map
		$mapped_file = $this->loadFromClassmap($class);

		// Nothing? Then try PSR4.
		if ( ! $mapped_file)
		{
			$mapped_file = $this->loadInNamespace($class);
		}

		return $mapped_file;
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to locate the file as one in our classmap and loads it
	 * if possible.
	 *
	 * @param string $class The fully-qualified class name
	 *
	 * @return mixed        The mapped file name on success, or boolean false on failure
	 */
	protected function loadFromClassmap($class)
	{
		if ( ! array_key_exists($class, $this->classmap))
		{
			return false;
		}

		if ( ! file_exists($this->classmap[$class]))
		{
			return false;
		}

		require $this->classmap[$class];

		return $this->classmap[$class];
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
			$prefix = substr($class, 0, $pos + 1);

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
		// are there any base directories for this namespace prefix?
		if ( ! isset($this->prefixes[$prefix]))
		{
			return false;
		}

		// look through base directories for this namespace prefix
		$file = $this->prefixes[$prefix].str_replace('\\', '/', $relative_class).'.php';

		// if the mapped file exists, grab it
		if (file_exists($file))
		{
			require $file;

			return $file;
		}

		return false;
	}

	//--------------------------------------------------------------------

}
