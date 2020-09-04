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

use InvalidArgumentException;

/**
 * Database Connection Factory
 *
 * Creates and returns an instance of the appropriate DatabaseConnection
 *
 * @package CodeIgniter\Database
 */
class DatabaseFactory
{

	/**
	 * Maintains an array of the instances of all connections
	 * that have been created. Helps to keep track of all open
	 * connections for performance monitoring, logging, etc.
	 *
	 * @var array
	 */
	protected $connections = [];

	//--------------------------------------------------------------------

	/**
	 * Parses the connection binds and returns an instance of the driver.
	 *
	 * @param array  $params
	 * @param string $alias
	 *
	 * @return   mixed
	 * @internal param bool $useBuilder
	 */
	public function connection(array $params = [], string $alias)
	{
		// Handle universal DSN connection string
		if (! empty($params['DSN']) && strpos($params['DSN'], '://') !== false)
		{
			$params = $this->parseDSN($params);
		}

		// No DB specified? Beat them senseless...
		if (empty($params['DBDriver']))
		{
			throw new InvalidArgumentException('You have not selected a database type to connect to.');
		}

		$class = $this->initDriver('Connection', $params['DBDriver']);

		// Store the connection
		$this->connections[$alias] = new $class($params);

		return $this->connections[$alias];
	}

	//--------------------------------------------------------------------

	/**
	 * Creates a new Forge instance for the current database type.
	 *
	 * @param ConnectionInterface $db
	 *
	 * @return mixed
	 */
	public function forge(ConnectionInterface $db)
	{
		// Initialize database connection if not exists
		if (! $db->connID)
		{
			$db->initialize();
		}

		$class = $this->initDriver('Forge', $db->DBDriver);

		return new $class($db);
	}

	//--------------------------------------------------------------------

	/**
	 * Loads the Database Utilities class.
	 *
	 * @param ConnectionInterface $db
	 *
	 * @return mixed
	 */
	public function utils(ConnectionInterface $db)
	{
		// Initialize database connection if not exists
		if (! $db->connID)
		{
			$db->initialize();
		}

		$class = $this->initDriver('Utils', $db->DBDriver);

		return new $class($db);
	}

	//--------------------------------------------------------------------

	/**
	 * Parse universal DSN string
	 *
	 * @param array $params
	 *
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	protected function parseDSN(array $params): array
	{
		$dsn = parse_url($params['DSN']);
		
		if ($dsn === false)
		{
			throw new InvalidArgumentException('Your DSN connection string is invalid.');
		}

		$dsnParams = [
			'DSN'      => '',
			'DBDriver' => $dsn['scheme'],
			'hostname' => isset($dsn['host']) ? rawurldecode($dsn['host']) : '',
			'port'     => isset($dsn['port']) ? rawurldecode((string) $dsn['port']) : '',
			'username' => isset($dsn['user']) ? rawurldecode($dsn['user']) : '',
			'password' => isset($dsn['pass']) ? rawurldecode($dsn['pass']) : '',
			'database' => isset($dsn['path']) ? rawurldecode(substr($dsn['path'], 1)) : '',
		];

		// Do we have additional config items set?
		if (! empty($dsn['query']))
		{
			parse_str($dsn['query'], $extra);

			foreach ($extra as $key => $val)
			{
				if (is_string($val) && in_array(strtolower($val), ['true', 'false', 'null'], true))
				{
					$val = $val === 'null' ? null : filter_var($val, FILTER_VALIDATE_BOOLEAN);
				}

				$dsnParams[$key] = $val;
			}
		}

		return array_merge($params, $dsnParams);
	}

	//--------------------------------------------------------------------

	/**
	 * Initialize database driver to use
	 * 
	 * @param string $class	   Class name
	 * @param string $DBDriver Database driver to use
	 *
	 * @return string
	 */
	protected function initDriver(string $class, string $DBDriver): string
	{
		$class = $DBDriver . '\\' . $class;

		if (! strpos($DBDriver, '\\'))
		{
			$class = 'CodeIgniter\Database\Drivers\\' . $class;
		}

		return $class;
	}
}
