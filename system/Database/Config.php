<?php namespace CodeIgniter\Database;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2018 British Columbia Institute of Technology
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
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */
use CodeIgniter\Config\BaseConfig;
use Config\Database;

/**
 * Class Config
 */
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
	 * @param string|array  $group     The name of the connection group to use,
	 *                                 or an array of configuration settings.
	 * @param bool          $getShared Whether to return a shared instance of the connection.
	 *
	 * @return BaseConnection
	 */
	public static function connect($group = null, bool $getShared = true)
	{
		if (is_array($group))
		{
			$config = $group;
			$group = 'custom';
		}

		$config = $config ?? new \Config\Database();

		if (empty($group))
		{
			$group = ENVIRONMENT == 'testing' ? 'tests' : $config->defaultGroup;
		}

		if (is_string($group) && ! isset($config->$group) && $group != 'custom')
		{
			throw new \InvalidArgumentException($group . ' is not a valid database connection group.');
		}

		if ($getShared && isset(self::$instances[$group]))
		{
			return self::$instances[$group];
		}

		self::ensureFactory();

		if (isset($config->$group))
		{
			$config = $config->$group;
		}

		$connection = self::$factory->load($config, $group);

		self::$instances[$group] = & $connection;

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
	 *
	 * @return Forge
	 */
	public static function forge(string $group = null)
	{
		$config = new \Config\Database();

		self::ensureFactory();

		if (empty($group))
		{
			$group = ENVIRONMENT == 'testing' ? 'tests' : $config->defaultGroup;
		}

		if ( ! isset($config->$group))
		{
			throw new \InvalidArgumentException($group . ' is not a valid database connection group.');
		}

		if ( ! isset(self::$instances[$group]))
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
	 * @return BaseUtils
	 */
	public static function utils(string $group = null)
	{
		$config = new \Config\Database();

		self::ensureFactory();

		if (empty($group))
		{
			$group = $config->defaultGroup;
		}

		if ( ! isset($config->group))
		{
			throw new \InvalidArgumentException($group . ' is not a valid database connection group.');
		}

		if ( ! isset(self::$instances[$group]))
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
