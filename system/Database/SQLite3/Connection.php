<?php namespace CodeIgniter\Database\SQLite3;

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
	 * SQLite3 object
	 *
	 * Has to be preserved without being assigned to $conn_id.
	 *
	 * @var    SQLite3
	 */
	public $sqlite3;

	//--------------------------------------------------------------------

	/**
	 * Connect to the database.
	 *
	 * @param bool $persistent
	 *
	 * @return mixed
	 * @throws \CodeIgniter\DatabaseException
	 */
	public function connect($persistent = false)
	{

			if ($persistent)
		{
			log_message('debug', 'SQLite3 doesn\'t support persistent connections');
		}

		$this->sqlite3 = ( ! $this->password)
				? new \SQLite3($this->database)
				: new \SQLite3($this->database, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE, $this->password);

		if ($this->sqlite3)
		{
			$this->sqlite3->busyTimeout(200);
			return $this->sqlite3;

		}
			
		return FALSE;
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
		if ($this->connID !== false)
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

		if (! $this->sqlite3)
		{
		    $this->initialize();
		}

		return $this->dataCache['version'] = SQLite3::version();
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
            return $this->connID->query($sql);
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
	 * @param	string $str
	 * @return	string
	 */
	protected function _escapeString(string $str): string
	{
		if (is_bool($str))
		{
			return $str;
		}

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
		$sql = 'SELECT "NAME" FROM "SQLITE_MASTER" WHERE "TYPE" = \'table\'';

		if ($prefixLimit !== FALSE && $this->DBPrefix !== '')
		{
			return $sql." LIKE '".$this->escapeLikeStr($this->DBPrefix)."%'";
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
		return 'PRAGMA TABLE_INFO(' . $this->protectIdentifiers($table, TRUE, NULL, FALSE) . ')';
	}

        //--------------------------------------------------------------------

        /**
	 * Fetch Field Names
	 *
	 * @param    string $table Table name
	 *
	 * @return array
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

		$sql = $this->_listColumns($table);
		$query = $this->query($sql);

                $this->dataCache['field_names'][$table] = array();

		foreach ($query->getResultArray() as $row)
		{
                	$this->dataCache['field_names'][$table][] = $row['name'];
		}

		return $this->dataCache['field_names'][$table];
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
		if (($query = $this->query('PRAGMA TABLE_INFO('.$this->protectIdentifiers($table, TRUE, NULL, FALSE).')')) === FALSE)
		{
			return FALSE;
		}
		$query = $query->getResultObject();

		$retval = array();
		for ($i = 0, $c = count($query); $i < $c; $i++)
		{
			$retval[$i]			= new \stdClass();
			$retval[$i]->name		= $query[$i]->name;

			sscanf($query[$i]->Type, '%[a-z](%d)',
				$retval[$i]->type,
				$retval[$i]->max_length
			);

			$retval[$i]->default		= $query[$i]->dflt_value;
			$retval[$i]->primary_key	= ($query[$i]->pk == 1);
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
		return array(
				'code' => $this->connID->lastErrorCode(),
				'message' => $this->connID->lastErrorMsg()
		);
	}

	//--------------------------------------------------------------------

	/**
	 * Insert ID
	 *
	 * @return	int
	 */
	public function insertID()
	{
		return $this->connID->lastInsertRowID();
	}

	//--------------------------------------------------------------------

}
