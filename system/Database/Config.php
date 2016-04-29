<?php namespace CodeIgniter\Database;

use CodeIgniter\Config\BaseConfig;
use Config\Database;

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
	 * @param string $group     The name of the connection group to use.
	 * @param bool   $getShared Whether to return a shared instance of the connection.
	 *
	 * @return mixed
	 */
	public static function connect(string $group = null, $getShared = true)
	{
		if ($getShared && isset(self::$instances[$group]))
		{
			return self::$instances[$group];
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

		$connection = self::$factory->load($config->$group, $group);

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
			$group = ENVIRONMENT == 'testing' ? 'tests' : $config->defaultGroup;
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
	 * Returns a new instance of the Database Utilities class.
	 * 
	 * @param string|null $group
	 *
	 * @return mixed
	 */
	public static function utils(string $group = null)
	{
	    $config = new \Config\Database();

		self::ensureFactory();

		if (empty($group))
		{
			$group = $config->defaultGroup;
		}

		if (! isset($config->group))
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

		return self::$factory->loadUtils($db);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a new instance of the Database Seeder.
	 *
	 * @param string|null $group
	 *
	 * @return Seeder
	 */
	public static function seeder(string $group = null)
	{
		$config = new \Config\Database();

		return new Seeder($config, self::connect($group));
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

}
