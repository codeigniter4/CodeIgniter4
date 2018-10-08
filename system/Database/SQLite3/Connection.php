<?php namespace CodeIgniter\Database\SQLite3;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2017 British Columbia Institute of Technology
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
 * @package      CodeIgniter
 * @author       CodeIgniter Dev Team
 * @copyright    2014-2017 British Columbia Institute of Technology (https://bcit.ca/)
 * @license      https://opensource.org/licenses/MIT	MIT License
 * @link         https://codeigniter.com
 * @since        Version 3.0.0
 * @filesource
 */
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * Connection for SQLite3
 */
class Connection extends BaseConnection implements ConnectionInterface
{

	/**
	 * Database driver
	 *
	 * @var    string
	 */
	public $DBDriver = 'SQLite3';

	// --------------------------------------------------------------------

	/**
	 * ORDER BY random keyword
	 *
	 * @var    array
	 */
	protected $_random_keyword = ['RANDOM()', 'RANDOM()'];

	//--------------------------------------------------------------------

	/**
	 * Connect to the database.
	 *
	 * @param bool $persistent
	 *
	 * @return mixed
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function connect($persistent = false)
	{
		if ($persistent and $this->db->DBDebug)
		{
			throw new DatabaseException('SQLite3 doesn\'t support persistent connections.');
		}
		try
		{
			return (! $this->password)
				? new \SQLite3($this->database)
				: new \SQLite3($this->database, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE, $this->password);
		} catch (\Exception $e)
		{
			throw new DatabaseException('SQLite3 error: '.$e->getMessage());
		}
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
		$this->close();
		$this->initialize();
	}

	//--------------------------------------------------------------------

	/**
	 * Close the database connection.
	 *
	 * @return void
	 */
	protected function _close()
	{
		$this->connID->close();
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

		$version = \SQLite3::version();

		return $this->dataCache['version'] = $version['versionString'];
	}

	//--------------------------------------------------------------------


	/**
	 * Execute the query
	 *
	 * @param    string $sql
	 *
	 * @return    mixed    \SQLite3Result object or bool
	 */
	public function execute($sql)
	{
		return $this->isWriteType($sql)
			? $this->connID->exec($sql)
			: $this->connID->query($sql);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the total number of rows affected by this query.
	 *
	 * @return mixed
	 */
	public function affectedRows(): int
	{
		return $this->connID->changes();
	}

	//--------------------------------------------------------------------

	/**
	 * Platform-dependant string escape
	 *
	 * @param    string $str
	 *
	 * @return    string
	 */
	protected function _escapeString(string $str): string
	{
		return $this->connID->escapeString($str);
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
		return 'SELECT "NAME" FROM "SQLITE_MASTER" WHERE "TYPE" = \'table\''
		       .(($prefixLimit !== false && $this->DBPrefix != '')
				? ' AND "NAME" LIKE \''.$this->escapeLikeString($this->DBPrefix).'%\' '.sprintf($this->likeEscapeStr,
					$this->likeEscapeChar)
				: '');
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
		return 'PRAGMA TABLE_INFO('.$this->protectIdentifiers($table, true, null, false).')';
	}


	/**
	 * Fetch Field Names
	 *
	 * @param    string $table Table name
	 *
	 * @return array|false
	 * @throws DatabaseException
	 */
	public function getFieldNames($table)
	{
		// Is there a cached result?
		if (isset($this->dataCache['field_names'][$table]))
		{
			return $this->dataCache['field_names'][$table];
		}

		if (empty($this->connID))
		{
			$this->initialize();
		}

		if (false === ($sql = $this->_listColumns($table)))
		{
			if ($this->DBDebug)
			{
				throw new DatabaseException('This feature is not available for the database you are using.');
			}

			return false;
		}

		$query                                  = $this->query($sql);
		$this->dataCache['field_names'][$table] = [];

		foreach ($query->getResultArray() as $row)
		{
			// Do we know from where to get the column's name?
			if (! isset($key))
			{
				if (isset($row['column_name']))
				{
					$key = 'column_name';
				}
				elseif (isset($row['COLUMN_NAME']))
				{
					$key = 'COLUMN_NAME';
				}
				elseif (isset($row['name']))
				{
					$key = 'name';
				}
				else
				{
					// We have no other choice but to just get the first element's key.
					$key = key($row);
				}
			}

			$this->dataCache['field_names'][$table][] = $row[$key];
		}

		return $this->dataCache['field_names'][$table];
	}

	//--------------------------------------------------------------------


	/**
	 * Returns an object with field data
	 *
	 * @param    string $table
	 *
	 * @return    array
	 */
	public function _fieldData(string $table)
	{

		if (($query = $this->query('PRAGMA TABLE_INFO('.$this->protectIdentifiers($table, true, null,
					false).')')) === false)
		{
			return false;
		}
		$query = $query->getResultObject();
		if (empty($query))
		{
			return false;
		}
		$retval = [];
		for ($i = 0, $c = count($query); $i < $c; $i++)
		{
			$retval[$i]              = new \stdClass();
			$retval[$i]->name        = $query[$i]->name;
			$retval[$i]->type        = $query[$i]->type;
			$retval[$i]->max_length  = null;
			$retval[$i]->default     = $query[$i]->dflt_value;
			$retval[$i]->primary_key = isset($query[$i]->pk) ? (int)$query[$i]->pk : 0;
		}

		return $retval;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an object with index data
	 *
	 * @param    string $table
	 *
	 * @return    array
	 */
	public function _indexData(string $table)
	{
		// Get indexes
		// Don't use PRAGMA index_list, so we can preserve index order
		$sql = "SELECT name FROM sqlite_master WHERE type='index' AND tbl_name=".$this->escape(strtolower($table))."";
		if (($query = $this->query($sql)) === false)
		{
			return false;
		}
		$query = $query->getResultObject();

		$retval = [];
		foreach ($query as $row)
		{
			$obj       = new \stdClass();
			$obj->name = $row->name;

			// Get fields for index
			$obj->fields = [];
			if (($fields = $this->query('PRAGMA index_info('.$this->escape(strtolower($row->name)).')')) === false)
			{
				return false;
			}
			$fields = $fields->getResultObject();

			foreach ($fields as $field)
			{
				$obj->fields[] = $field->name;
			}

			$retval[] = $obj;
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
	 * @return    array
	 */
	public function error(): array
	{
		return ['code' => $this->connID->lastErrorCode(), 'message' => $this->connID->lastErrorMsg()];
	}

	//--------------------------------------------------------------------

	/**
	 * Insert ID
	 *
	 * @return    int
	 */
	public function insertID(): int
	{
		return $this->connID->lastInsertRowID();
	}

	//--------------------------------------------------------------------

	/**
	 * Begin Transaction
	 *
	 * @return    bool
	 */
	protected function _transBegin(): bool
	{
		return $this->connID->exec('BEGIN TRANSACTION');
	}

	//--------------------------------------------------------------------

	/**
	 * Commit Transaction
	 *
	 * @return    bool
	 */
	protected function _transCommit(): bool
	{
		return $this->connID->exec('END TRANSACTION');
	}

	//--------------------------------------------------------------------

	/**
	 * Rollback Transaction
	 *
	 * @return    bool
	 */
	protected function _transRollback(): bool
	{
		return $this->connID->exec('ROLLBACK');
	}

	//--------------------------------------------------------------------

	/**
	 * Determines if the statement is a write-type query or not.
	 *
	 * @return bool
	 */
	public function isWriteType($sql): bool
	{
		return (bool)preg_match(
			'/^\s*"?(SET|INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|TRUNCATE|LOAD|COPY|ALTER|RENAME|GRANT|REVOKE|LOCK|UNLOCK|REINDEX)\s/i',
			$sql);
	}

	//--------------------------------------------------------------------

	/**
	 * Checks to see if the current install supports Foreign Keys
	 * and has them enabled.
	 *
	 * @return bool
	 */
	protected function supportsForeignKeys(): bool
	{
		$result = $this->simpleQuery("PRAGMA foreign_keys");

		return (bool)$result;
	}

	/**
	 * Returns an object with Foreign key data
	 *
	 * @param	string	$table
	 * @return	array
	 */
	public function _foreignKeyData(string $table)
	{
		if ($this->supportsForeignKeys() !== true)
		{
			return [];
		}

		$tables = $this->listTables();

		if (empty($tables))
		{
			return [];
		}

		$retval = [];

		foreach ($tables as $table)
		{
			$query = $this->query("PRAGMA foreign_key_list({$table})")->getResult();

			foreach ($query as $row)
			{
				$obj = new \stdClass();
				$obj->constraint_name = $row->from.' to '. $row->table.'.'.$row->to;
				$obj->table_name = $table;
				$obj->foreign_table_name = $row->table;

				$retval[] = $obj;
			}
		}

		return $retval;
	}

	//--------------------------------------------------------------------
}
