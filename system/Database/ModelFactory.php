<?php namespace CodeIgniter\Database;

use Config\Services;

class ModelFactory
{
	/**
	 * Cache for instance of any models that
	 * have been requested as "shared" instance.
	 *
	 * @var array
	 */
	static private $instances = [];

	/**
	 * Mapping of class basenames (no namespace) to instances.
	 *
	 * @var string[]
	 */
	static private $basenames = [];

	/**
	 * Creates new Model instances or returns a shared instance
	 *
	 * @param string              $name       Model name, namespace optional
	 * @param boolean             $getShared  Use shared instance
	 * @param ConnectionInterface $connection
	 *
	 * @return mixed|null
	 */
	public static function get(string $name, bool $getShared = true, ConnectionInterface $connection = null)
	{
		$basename = $name;
		if ($test = strrchr($name, '\\'))
		{
			$basename = substr($test, 1);
		}

		if (! $getShared)
		{
			return self::createClass($name, $connection);
		}

		if (! isset(self::$basenames[$basename]))
		{
			if (! $instance = self::createClass($name, $connection))
			{
				return null;
			}
			$class = get_class($instance);

			self::$instances[$class]    = $instance;
			self::$basenames[$basename] = $class;
		}

		return self::$instances[self::$basenames[$basename]];
	}

	/**
	 * Helper method for injecting mock instances while testing.
	 *
	 * @param string $name
	 * @param object $instance
	 */
	public static function injectMock(string $name, $instance)
	{
		$basename = $name;
		if ($test = strrchr($name, '\\'))
		{
			$basename = substr($test, 1);
		}

		$class = get_class($instance);

		self::$instances[$class]    = $instance;
		self::$basenames[$basename] = $class;
	}

	/**
	 * Resets the static arrays
	 */
	public static function reset()
	{
		static::$instances = [];
		static::$basenames = [];
	}

	/**
	 * Finds a Model class and creates an instance
	 *
	 * @param string                   $name       Classname
	 * @param ConnectionInterface|null $connection
	 *
	 * @return mixed|null
	 */
	private static function createClass(string $name, ConnectionInterface &$connection = null)
	{
		if (class_exists($name))
		{
			return new $name();
		}

		$locator = Services::locator();

		// Check if the class was namespaced
		if (strpos($name, '\\') !== false)
		{
			if (! $file = $locator->locateFile($name, 'Models'))
			{
				return null;
			}
		}
		// No namespace? Search for it
		else
		{
			// Check all namespaces, prioritizing App and modules
			if (! $files = $locator->search('Models/' . $name))
			{
				return null;
			}

			// Use the first match
			$file = reset($files);
		}

		if (! $name = $locator->getClassname($file))
		{
			return null;
		}

		return new $name($connection);
	}
}
