<?php namespace CodeIgniter\Config\Database;

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
 * Database Connection configuration class.
 *
 * A base class for database connection configuration.
 *
 * This class is intended to be a base class for other system-level configuration
 * classes, not for application-level database connection configuration. Applications
 * should extend the platform-specific system-level connection configuration class
 * for their database.
 */
abstract class Connection extends \CodeIgniter\Config\BaseConfig
{
	/** @var bool Enable database result caching. */
	public $cacheEnabled = false;

	/** @var string The character set. */
	public $characterSet = 'utf8';

	/** @var string The name of the database. */
	public $database = '';

	/** @var bool Enable debug messages. */
	public $debugEnabled = false;

	/**
	 * Data Source Name.
	 *
	 * A string which will usually contain all of the settings required to connect
	 * to a database. Commonly used for database adapters which support multiple
	 * database types, like PDO or ODBC.
	 *
	 * @var string
	 */
	public $dsn = '';

	/** @var array List of connections to use if this one fails to connect. */
	public $failover = [];

	/** @var string The database server's hostname. */
	public $hostname = '';

	/** @var string The password for the database connection. */
	public $password = '';

	/** @var bool Use a persistent connection. */
	public $persistentConnectionEnabled = false;

	/**
	 * The port used to connect to the database server.
	 *
	 * This is usually not set when the server is configured to use the default
	 * port.
	 *
	 * @var int
	 */
	public $port;

	/**
	 * Whether to "save" all executed SQL statements.
	 *
	 * Disabling this will also effectively disable application-level profiling
	 * of SQL statements.
	 *
	 * Enabling this setting may cause high memory use, especially when running
	 * a lot of SQL statements.
	 *
	 * @var bool
	 */
	public $saveStatementsEnabled = false;

	/** @var bool Forces "Strict Mode" connections to enforce strict SQL. */
	public $strictSQLEnabled = false;

	/** @var string The username for the database connection. */
	public $username = '';

	/**
	 * The name of the adapter to be used by this connection.
	 * In most cases, this will match the name of the Connection class itself.
	 *
	 * This is intended to be set by the system-level platform-specific configuration
	 * class, and should not be modified by the application.
	 *
	 * @var string
	 */
	protected $adapter;

	/**
	 * Allows a Database Connection configuration to be built from a parameter array
	 * at run-time from one of the extending classes.
	 *
	 * @param array $params Property name/value pairs to set in the database Config.
	 */
	public function __construct($params = [])
	{
		parent::__construct();

		if ( ! empty($params))
		{
			// Allow $params to override environment variables.
			$properties = array_keys(get_object_vars($this));

			foreach ($properties as $property)
			{
				if (array_key_exists($property, $params))
				{
					$this->{$property} = $params[$property];
				}
			}
		}
	}

	/**
	 * Get the name of the adapter to be used by the connection.
	 *
	 * @return string The name of the adapter.
	 */
	public function getAdapter()
	{
		return $this->adapter;
	}
}
