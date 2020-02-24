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

namespace CodeIgniter\Database;

use CodeIgniter\Config\BaseConfig;

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
	 * @param string|array $group     The name of the connection group to use,
	 *                                or an array of configuration settings.
	 * @param boolean      $getShared Whether to return a shared instance of the connection.
	 *
	 * @return BaseConnection
	 */
	public static function connect($group = null, bool $getShared = true)
	{
		// If a DB connection is passed in, just pass it back
		if ($group instanceof BaseConnection)
		{
			return $group;
		}

		if (is_array($group))
		{
			$config = $group;
			$group  = 'custom-' . md5(json_encode($config));
		}

		$config = $config ?? config('Database');

		if (empty($group))
		{
			$group = ENVIRONMENT === 'testing' ? 'tests' : $config->defaultGroup;
		}

		if (is_string($group) && ! isset($config->$group) && strpos($group, 'custom-') !== 0)
		{
			throw new \InvalidArgumentException($group . ' is not a valid database connection group.');
		}

		if ($getShared && isset(static::$instances[$group]))
		{
			return static::$instances[$group];
		}

		static::ensureFactory();

		if (isset($config->$group))
		{
			$config = $config->$group;
		}

		$connection = static::$factory->load($config, $group);

		static::$instances[$group] = & $connection;

		return $connection;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an array of all db connections currently made.
	 *
	 * @return array
	 */
	public static function getConnections(): array
	{
		return static::$instances;
	}

	//--------------------------------------------------------------------

	/**
	 * Loads and returns an instance of the Forge for the specified
	 * database group, and loads the group if it hasn't been loaded yet.
	 *
	 * @param string|array|null $group
	 *
	 * @return Forge
	 */
	public static function forge($group = null)
	{
		$db = static::connect($group);

		return static::$factory->loadForge($db);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a new instance of the Database Utilities class.
	 *
	 * @param string|array|null $group
	 *
	 * @return BaseUtils
	 */
	public static function utils($group = null)
	{
		$db = static::connect($group);

		return static::$factory->loadUtils($db);
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
		$config = config('Database');

		return new Seeder($config, static::connect($group));
	}

	//--------------------------------------------------------------------

	/**
	 * Ensures the database Connection Manager/Factory is loaded and ready to use.
	 */
	protected static function ensureFactory()
	{
		if (static::$factory instanceof Database)
		{
			return;
		}

		static::$factory = new Database();
	}

	//--------------------------------------------------------------------
}
