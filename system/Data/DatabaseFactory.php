<?php namespace CodeIgniter\Data;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
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
 * @package	  CodeIgniter
 * @author	  CodeIgniter Dev Team
 * @copyright Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	  http://opensource.org/licenses/MIT	MIT License
 * @link	  http://codeigniter.com
 * @since	  Version 4.0.0
 * @filesource
 */

/**
 * Ensures the required parts are assembled to create an instance of the Database
 * library using the current application's configuration or the passed configuration
 * information.
 */
class DatabaseFactory
{
	/** @var array The default namespaces used by the Database library. */
	protected static $databaseNamespaces = [
		"\\App\\Data\\Database\\",
		"\\CodeIgniter\\Data\\Database\\",
	];

	protected static $databaseAdapters = [
		'mysqli' => "\\CodeIgniter\\Config\\Database\\Connection\\MySQLi",
	];

	/**
	 * Build an instance of the Database library.
	 *
	 * @param mixed $connectionConfig Optionally:
	 * - An instance of \CodeIgniter\Config\Database\Connection
	 * - A string containing the connection configuration class name
	 * - A string containing a key from $availableConnections in the database configuration
	 * - An array of connection parameters (@todo implement buildConnectionConfigFromArray())
	 * If null/omitted, returns the active connection configuration from the database
	 * configuration.
	 *
	 * @param mixed $dbConfig         Optionally:
	 * - An instance of \CodeIgniter\Config\Database
	 * - The database configuration class name
	 * - An array of database configuration parameters
	 * If null/omitted and the connection configuration can not be found, attempts
	 * to load \Config\Database.
	 *
	 * @param array $validNamespaces  An array of namespaces which will be searched
	 * for the Database library.
	 *
	 * @return \CodeIgniter\Data\Database An instance of the Database library.
	 */
	public static function build($connectionConfig = null, $dbConfig = null, array $validNamespaces = [])
	{
		if ( ! ($connectionConfig instanceof \CodeIgniter\Config\Database\Connection))
		{
			$connectionConfig = self::buildConnectionConfig($connectionConfig, $dbConfig);
		}

		$validNamespaces = empty($validNamespaces) ? self::$databaseNamespaces : array_merge(self::$databaseNamespaces, $validNamespaces);
		return new Database(self::buildAdapter(
			self::buildConnection($connectionConfig, $validNamespaces),
			$connectionConfig,
			$validNamespaces
		));
	}

	/**
	 * Create an instance of the Database Adapter.
	 *
	 * @param \CodeIgniter\Data\Database\Connection   $connection The database Connection.
	 * @param \CodeIgniter\Config\Database\Connection $config     The configuration
	 * which was used to build the database Connection.
	 * @param array $adapterNamespaces The namespaces in which the adapter may be
	 * found.
	 *
	 * @return \CodeIgniter\Data\Database\Adapter The database Adapter.
	 */
	public static function buildAdapter(
		\CodeIgniter\Data\Database\Connection $connection,
		\CodeIgniter\Config\Database\Connection $config,
		array $adapterNamespaces
	): \CodeIgniter\Data\Database\Adapter
	{
		$adapterClass = self::buildClass($config, 'Adapter', $adapterNamespaces);
		$adapterClass = new $adapterClass($config, $connection);
		if ( ! ($adapterClass instanceof \CodeIgniter\Data\Database\Adapter))
		{
			throw new \Exception('Invalid database adapter.');
		}

		return $adapterClass;
	}

	/**
	 * Create an instance of the Database Connection.
	 *
	 * @param \CodeIgniter\Config\Database\Connection $config The configuration
	 * used to build the database Connection.
	 * @param array $connectionNamespaces The namespaces in which the connection
	 * may be found.
	 *
	 * @return \CodeIgniter\Data\Database\Connection The database Connection.
	 */
	public static function buildConnection(
		\CodeIgniter\Config\Database\Connection $config,
		array $connectionNamespaces
	): \CodeIgniter\Data\Database\Connection
	{
		$connectionClass = self::buildClass($config, 'Connection', $connectionNamespaces);
		$connectionClass = new $connectionClass($config);
		if ( ! ($connectionClass instanceof \CodeIgniter\Data\Database\Connection))
		{
			throw new \Exception('Invalid database connection.');
		}

		return $connectionClass;
	}

	/**
	 * Find a platform-specific class to be used by the Database library.
	 *
	 * Used to load Database Connections, Adapters, etc. Not used for Config files.
	 *
	 * @param \CodeIgniter\Config\Database\Connection $config The active connection
	 * configuration.
	 * @param string $className       The name of the class.
	 * @param array  $validNamespaces Namespaces in which the class may be located.
	 *
	 * @return mixed The fully-namespaced name of the requested class.
	 */
	protected static function buildClass(
		\CodeIgniter\Config\Database\Connection $config,
		string $className,
		array $validNamespaces
	)
	{
		$className = ltrim($className, '\\');
		if ($className === '')
		{
			throw new \InvalidArgumentException('Invalid class name.');
		}

		$classDir = trim($config->getAdapter(), '\\');
		if ($classDir === '')
		{
			throw new \InvalidArgumentException('Invalid connection configuration.');
		}

		foreach ($validNamespaces as $validNs)
		{
			$validNs = rtrim($validNs, '\\');
			if (class_exists($validClass = "{$validNs}\\{$classDir}\\{$className}"))
			{
				return $validClass;
			}
		}

		throw new \Exception("Class not found: '{$className}'.");
	}

	//--------------------------------------------------------------------
	// Build or get a connection configuration.
	//--------------------------------------------------------------------

	/**
	 * Build a Connection Configuration class.
	 *
	 * @param mixed $connectionConfig Optionally:
	 * - An instance of \CodeIgniter\Config\Database\Connection
	 * - A string containing the connection configuration class name
	 * - A string containing a key from $availableConnections in the database configuration
	 * - An array of connection parameters (@todo implement buildConnectionConfigFromArray())
	 * If null/omitted, returns the active connection configuration from the database
	 * configuration.
	 *
	 * @param mixed $dbConfig         Optionally:
	 * - An instance of \CodeIgniter\Config\Database
	 * - The database configuration class name
	 * - An array of database configuration parameters
	 * If null/omitted and the connection configuration can not be found, attempts
	 * to load \Config\Database.
	 *
	 * @return \CodeIgniter\Config\Database\Connection The connection configuration.
	 */
	protected static function buildConnectionConfig(
		$connectionConfig = null,
		$dbConfig = null
	): \CodeIgniter\Config\Database\Connection
	{
		if ($connectionConfig instanceof \CodeIgniter\Config\Database\Connection)
		{
			return $connectionConfig;
		}

		if (is_array($connectionConfig))
		{
			return self::buildConnectionConfigFromArray($connectionConfig);
		}

		return self::getConnectionConfig($connectionConfig, $dbConfig);
	}

	/**
	 * Build a connection configuration class from an array of parameters.
	 *
	 * @todo implement building a connection configuration class from a parameter
	 * array.
	 *
	 * @param array $connectionConfig The array keys should match the properties
	 * to which the values should be assigned.
	 *
	 * @return \CodeIgniter\Config\Database\Connection The connection configuration.
	 */
	protected static function buildConnectionConfigFromArray(array $connectionConfig): \CodeIgniter\Config\Database\Connection
	{
		throw new \Exception('Database connection configuration array is not supported, yet.');

		if ( ! isset($connectionConfig['adapter'])
			|| ! in_array(strtolower($connectionConfig['adapter']), array_keys(self::$databaseAdapters))
		)
		{
			throw new \InvalidArgumentException('Invalid connection configuration array.');
		}

		$config = self::$dabaseAdapters[strtolower($connectionConfig['adapter'])];
		unset($connectionConfig['adapter']);

		return new $config($connectionConfig);
	}

	/**
	 * Find the connection configuration class.
	 *
	 * @param string|null $connectionConfig Optionally:
	 * - A string containing the connection configuration class name
	 * - A string containing a key from $availableConnections in the database configuration
	 * If null/omitted, returns the configuration for the active connection in the
	 * database configuration.
	 *
	 * @param mixed       $dbConfig         Optionally:
	 * - An instance of \CodeIgniter\Config\Database
	 * - The database configuration class name
	 * - An array of database configuration parameters
	 * If null/omitted and the connection configuration can not be found, attempts
	 * to load \Config\Database.
	 *
	 * @return \CodeIgniter\Config\Database\Connection The connection configuration.
	 */
	protected static function getConnectionConfig($connectionConfig = null, $dbConfig = null): \CodeIgniter\Config\Database\Connection
	{
		// Check for any resolutions which don't require the database config.
		if (is_string($connectionConfig))
		{
			if (class_exists($connectionConfig))
			{
				return new $connectionConfig();
			}
			elseif (class_exists($configClass = "\\App\\Config\\Database\\{$connectionConfig}"))
			{
				return new $configClass();
			}
		}

		// $connectionConfig is null or a connection name, so the database config
		// must be loaded.

		return self::getConnectionConfigFromDbConfig(self::getDbConfig($dbConfig), $connectionConfig);
	}

	/**
	 * Get the connection configuration from the database configuration.
	 *
	 * @param \CodeIgniter\Config\Database $dbConfig The database configuration.
	 * @param string|null $connectionConfig The connection configuration to load.
	 * If null, the default/active connection will be retrieved.
	 *
	 * @return \CodeIgniter\Config\Database\Connection The database connection configuration.
	 */
	protected static function getConnectionConfigFromDbConfig(\CodeIgniter\Config\Database $dbConfig, $connectionConfig = null): \CodeIgniter\Config\Database\Connection
	{
		if (is_string($connectionConfig)
			&& in_array($connectionConfig, array_keys($dbConfig->availableConnections))
			&& class_exists($dbConfig->availableConnections[$connectionConfig])
		)
		{
			return new $dbConfig->availableConnections[$connectionConfig]();
		}
		elseif ($connectionConfig === null
			&& class_exists($dbConfig->availableConnections[$dbConfig->activeConnection])
		)
		{
			return new $dbConfig->availableConnections[$dbConfig->activeConnection]();
		}

		throw new \InvalidArgumentException('Invalid connection configuration.');
	}

	//--------------------------------------------------------------------
	// Get or build the database configuration
	//--------------------------------------------------------------------

	/**
	 * Find the database configuration class.
	 *
	 * @param mixed $dbConfig Optionally:
	 * - An instance of \CodeIgniter\Config\Database
	 * - The database configuration class name
	 * - An array of database configuration parameters
	 * If null/omitted, returns \Config\Database
	 *
	 * @return \CodeIgniter\Config\Database The database configuration.
	 */
	protected static function getDbConfig($dbConfig = null): \CodeIgniter\Config\Database
	{
		if ($dbConfig instanceof \CodeIgniter\Config\Database)
		{
			return $dbConfig;
		}
		elseif (is_string($dbConfig) && $dbConfig !== '')
		{
			return self::getDbConfigFromString($dbConfig);
		}
		elseif (is_array($dbConfig) && ! empty($dbConfig))
		{
			// Build the database configuration from an array of parameters.
			return new \CodeIgniter\Config\Database($dbConfig);
		}
		elseif ($dbConfig === null)
		{
			return self::getDefaultDbConfig();
		}

		throw new \InvalidArgumentException('Invalid Database configuration.');
	}

	/**
	 * Get the database configuration.
	 *
	 * @param string $dbConfig The name of the database configuration class. This
	 * should be either the fully-namespaced class name, or the name of a class
	 * within either Config or Config\Database.
	 *
	 * @return \CodeIgniter\Config\Database The database configuration.
	 */
	protected static function getDbConfigFromString(string $dbConfig): \CodeIgniter\Config\Database
	{
		if (class_exists($dbConfig))
		{
			return new $dbConfig();
		}
		elseif (class_exists($dbConf = "\\App\\Config\\{$dbConfig}")
			|| class_exists($dbConf = "\\App\\Config\\Database\\{$dbConfig}")
		)
		{
			return new $dbConf();
		}

		throw new \InvalidArgumentException('Invalid Database configuration.');
	}

	/**
	 * Get the default database configuration for the application.
	 * @return \CodeIgniter\Config\Database The application's default database configuration.
	 * Looks for and attempts to load \Config\Database.
	 */
	protected static function getDefaultDbConfig(): \CodeIgniter\Config\Database
	{
		if (class_exists($dbConf = "\\App\\Config\\Database"))
		{
			return new $dbConf();
		}

		throw new \Exception('Default Database Config not found.');
	}
}
