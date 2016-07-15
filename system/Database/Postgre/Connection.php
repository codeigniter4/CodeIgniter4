<?php namespace CodeIgniter\Database\Postgre;

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
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\DatabaseException;

/**
 * Connection for Postgre
 */
class Connection extends BaseConnection implements ConnectionInterface
{
	/**
	 * Database driver
	 *
	 * @var string
	 */
	public $DBDriver = 'postgre';

	//--------------------------------------------------------------------

	/**
	 * Database schema
	 *
	 * @var string
	 */
	public $schema = 'public';

	/**
	 * Identifier escape character
	 *
	 * @var    string
	 */
	public $escapeChar = '"';

	//--------------------------------------------------------------------

	/**
	 * Connect to the database.
	 *
	 * @param bool $persistent
	 * @return mixed
	 */
	public function connect($persistent = false)
	{
		if (empty($this->DSN))
		{
			$this->buildDSN();
		}

		$this->connID = $persistent === true
			? pg_pconnect($this->DSN) : pg_connect($this->DSN);

		if ($this->connID !== false)
		{
			if ($persistent === true
				&& pg_connection_status($this->connID) === PGSQL_CONNECTION_BAD
				&& pg_ping($this->connID) === false
			)
			{
				return false;
			}

			empty($this->schema) or $this->simpleQuery("SET search_path TO {$this->schema},public");

			if ($this->setClientEncoding($this->charset) === false)
			{
				return false;
			}
		}

		return $this->connID;
	}

	//--------------------------------------------------------------------

	/**
	 * Keep or establish the connection if no queries have been sent for
	 * a length of time exceeding the server's idle timeout.
	 *
	 * @return mixed
	 */
	public function reconnect()
	{
		if (pg_ping($this->connID) === false)
		{
			$this->connID = false;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Select a specific database table to use.
	 *
	 * @param string $databaseName
	 *
	 * @return mixed
	 */
	public function setDatabase(string $databaseName)
	{
		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a string containing the version of the database being used.
	 *
	 * @return mixed
	 */
	public function getVersion()
	{
		if (isset($this->dataCache['version']))
		{
			return $this->dataCache['version'];
		}

		if ( ! $this->connID or ($pgVersion = pg_version($this->connID)) === false)
		{
			return false;
		}

		return isset($pgVersion['server'])
			? $this->dataCache['version'] = $pgVersion['server'] : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Executes the query against the database.
	 *
	 * @param $sql
	 *
	 * @return mixed
	 */
	public function execute($sql)
	{
		return pg_query($this->connID, $sql);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the total number of rows affected by this query.
	 *
	 * @return mixed
	 */
	public function affectedRows(): int
	{
		return pg_affected_rows($this->resultID);
	}

	//--------------------------------------------------------------------

	/**
	 * "Smart" Escape String
	 *
	 * Escapes data based on type
	 *
	 * @param  string $str
	 * @return mixed
	 */
	public function escape($str)
	{
		if (is_string($str) OR (is_object($str) && method_exists($str, '__toString'))) {
			return pg_escape_literal($this->connID, $str);
		}
		elseif (is_bool($str))
		{
			return $str ? 'TRUE' : 'FALSE';
		}

		return parent::escape($str);
	}

	//--------------------------------------------------------------------

	/**
	 * Platform-dependant string escape
	 *
	 * @param	string $str
	 * @return	string
	 */
	protected function _escapeString(string $str): string
	{
		return pg_escape_string($this->connID, $str);
	}

	//--------------------------------------------------------------------

	/**
	 * Generates the SQL for listing tables in a platform-dependent manner.
	 *
	 * @param bool $prefixLimit
	 *
	 * @return string
	 */
	protected function _listTables($prefixLimit = false): string
	{
		$sql = 'SELECT "table_name" FROM "information_schema"."tables" WHERE "table_schema" = \''.$this->schema."'";

		if ($prefixLimit !== false && $this->DBPrefix !== '')
		{
			return $sql.' AND "table_name" LIKE \''
				.$this->escapeLikeString($this->DBPrefix)."%' "
				.sprintf($this->likeEscapeStr, $this->likeEscapeChar);
		}

		return $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * Generates a platform-specific query string so that the column names can be fetched.
	 *
	 * @param string $table
	 *
	 * @return string
	 */
	protected function _listColumns(string $table = ''): string
	{
		return 'SELECT "column_name"
			FROM "information_schema"."columns"
			WHERE LOWER("table_name") = '
			.$this->escape(strtolower($table));
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an object with field data
	 *
	 * @param	string	$table
	 * @return	array
	 */
	public function fieldData(string $table)
	{
		$sql = 'SELECT "column_name", "data_type", "character_maximum_length", "numeric_precision", "column_default"
			FROM "information_schema"."columns"
			WHERE LOWER("table_name") = '
			.$this->escape(strtolower($table));

		if (($query = $this->query($sql)) === false)
		{
			return false;
		}
		$query = $query->getResultObject();

		$retval = [];
		for ($i = 0, $c = count($query); $i < $c; $i++)
		{
			$retval[$i]             = new \stdClass();
			$retval[$i]->name       = $query[$i]->column_name;
			$retval[$i]->type       = $query[$i]->data_type;
			$retval[$i]->default    = $query[$i]->column_default;
			$retval[$i]->max_length = $query[$i]->character_maximum_length > 0
				? $query[$i]->character_maximum_length
				: $query[$i]->numeric_precision;
		}

		return $retval;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the last error code and message.
	 *
	 * Must return an array with keys 'code' and 'message':
	 *
	 *  return ['code' => null, 'message' => null);
	 *
	 * @return	array
	 */
	public function error()
	{
		return [
			'code'    => '',
			'message' => pg_last_error($this->connID)
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Insert ID
	 *
	 * @return	int
	 */
	public function insertID()
	{
		$v = pg_version($this->connID);
		// 'server' key is only available since PostgreSQL 7.4
		$v = isset($v['server']) ? $v['server'] : 0;

		$table  = func_num_args() > 0 ? func_get_arg(0) : null;
		$column = func_num_args() > 1 ? func_get_arg(1) : null;

		if ($table === null && $v >= '8.1')
		{
			$sql = 'SELECT LASTVAL() AS ins_id';
		}
		elseif ($table !== null)
		{
			if ($column !== null && $v >= '8.0')
			{
				$sql = "SELECT pg_get_serial_sequence('{$table}', '{$column}') AS seq";
				$query = $this->query($sql);
				$query = $query->row();
				$seq = $query->seq;
			}
			else
			{
				// seq_name passed in table parameter
				$seq = $table;
			}

			$sql = "SELECT CURRVAL('{$seq}') AS ins_id";
		}
		else
		{
			return pg_last_oid($this->resultID);
		}

		$query = $this->query($sql);
		$query = $query->getRow();
		return (int) $query->ins_id;
	}

	//--------------------------------------------------------------------

	/**
	 * Build a DSN from the provided parameters
	 *
	 * @return void
	 */
	protected function buildDSN()
	{
		$this->DSN === '' or $this->DSN = '';

		// If UNIX sockets are used, we shouldn't set a port
		if (strpos($this->hostname, '/') !== false)
		{
			$this->port = '';
		}

		$this->hostname === '' or $this->DSN = "host={$this->hostname} ";

		if ( ! empty($this->port) && ctype_digit($this->port))
		{
			$this->DSN .= "port={$this->port} ";
		}

		if ($this->username !== '')
		{
			$this->DSN .= "user={$this->username} ";

			// An empty password is valid!
			// password must be set to null to ignore it.

			$this->password === null or $this->DSN .= "password='{$this->password}' ";
		}

		$this->database === '' or $this->DSN .= "dbname={$this->database} ";

		// We don't have these options as elements in our standard configuration
		// array, but they might be set by parse_url() if the configuration was
		// provided via string> Example:
		//
		// postgre://username:password@localhost:5432/database?connect_timeout=5&sslmode=1
		foreach (['connect_timeout', 'options', 'sslmode', 'service'] as $key)
		{
			if (isset($this->{$key}) && is_string($this->{$key}) && $this->{$key} !== '')
			{
				$this->DSN .= "{$key}='{$this->{$key}}' ";
			}
		}

		$this->DSN = rtrim($this->DSN);
	}

	//--------------------------------------------------------------------

	/**
	 * Set client encoding
	 *
	 * @param string $charset The client encoding to which the data will be converted.
	 * @return bool
	 */
	protected function setClientEncoding($charset)
	{
		return pg_set_client_encoding($this->connID, $charset) === 0;
	}

	//--------------------------------------------------------------------
}
