<?php namespace CodeIgniter\Database;

use CodeIgniter\Config\BaseConfig;

class Config extends BaseConfig
{
	/**
	 * Cache for instance of any connections that
	 * have been requested as a "shared" instance.
	 *
	 * @var array
	 */
	static protected $instances = [];

	/**
	 * The main instance used to manage all of
	 * our open database connections.
	 *
	 * @var \CodeIgniter\Database\Database
	 */
	static protected $factory;

	//--------------------------------------------------------------------

	/**
	 * Creates the default
	 *
	 * @param string $group      The name of the connection group to use.
	 * @param bool   $useBuilder If the QueryBuilder should be returned
	 */
	public static function connect(string $group = null, $useBuilder = true, $getShared = false)
	{
		if ($getShared)
		{
			return self::getSharedInstance('default');
		}

		self::ensureFactory();

		$config = new \Config\Database();

		if (empty($group))
		{
			$group = $config->defaultGroup;
		}

		if (! isset($config->$group))
		{
			throw new \InvalidArgumentException($group.' is not a valid database connection group.');
		}

		$connection = self::$factory->load($config->$group, $group, $useBuilder);

		self::$instances[$group] =& $connection;

		return $connection;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an array of all db connections currently made.
	 *
	 * @return array
	 */
	public static function getConnections()
	{
	    return self::$instances;
	}

	//--------------------------------------------------------------------

	/**
	 * Loads and returns an instance of the Forge for the specified
	 * database group, and loads the group if it hasn't been loaded yet.
	 *
	 * @param string|null $group
	 */
	public static function forge(string $group = null)
	{
		$config = new \Config\Database();

		self::ensureFactory();

		if (empty($group))
		{
			$group = $config->defaultGroup;
		}

		if (! isset($config->$group))
		{
			throw new \InvalidArgumentException($group.' is not a valid database connection group.');
		}

		if (! isset(self::$instances[$group]))
		{
			$db = self::connect($group);
		}
		else 
		{
			$db = self::$instances[$group];
		}
		
		return self::$factory->loadForge($db);
	}

	//--------------------------------------------------------------------



	/**
	 * Ensures the database Connection Manager/Factory is loaded and ready to use.
	 */
	protected static function ensureFactory()
	{
		if (self::$factory instanceof \CodeIgniter\Database\Database)
		{
			return;
		}

		self::$factory = new \CodeIgniter\Database\Database();
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a shared instance of any of the class' connections.
	 *
	 * $key must be a name matching a db connection.
	 *
	 * @param string $key
	 */
	protected static function getSharedInstance(string $key, ...$params)
	{
		if (! isset(static::$instances[$key]))
		{
			static::$instances[$key] = self::$key(...$params);
		}

		return static::$instances[$key];
	}

	//--------------------------------------------------------------------

	/**
	 * Provides the ability to perform case-insensitive calling of service
	 * names.
	 *
	 * @param string $name
	 * @param array  $arguments
	 */
	public static function __callStatic(string $name, array $arguments)
	{
		$name = strtolower($name);

		if (method_exists('Config\Database', $name))
		{
			return Services::$name(...$arguments);
		}
	}

	//--------------------------------------------------------------------
}
