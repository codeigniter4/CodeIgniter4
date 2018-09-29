<?php namespace CodeIgniter\Database\MySQLi;

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
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\ConnectionInterface;
use \CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * Connection for MySQLi
 */
class Connection extends BaseConnection implements ConnectionInterface
{

	/**
	 * Database driver
	 *
	 * @var    string
	 */
	public $DBDriver = 'MySQLi';

	/**
	 * DELETE hack flag
	 *
	 * Whether to use the MySQL "delete hack" which allows the number
	 * of affected rows to be shown. Uses a preg_replace when enabled,
	 * adding a bit more processing to all queries.
	 *
	 * @var    bool
	 */
	public $deleteHack = true;

	// --------------------------------------------------------------------

	/**
	 * Identifier escape character
	 *
	 * @var    string
	 */
	public $escapeChar = '`';

	// --------------------------------------------------------------------

	/**
	 * MySQLi object
	 *
	 * Has to be preserved without being assigned to $conn_id.
	 *
	 * @var \MySQLi
	 */
	public $mysqli;

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
		// Do we have a socket path?
		if ($this->hostname[0] === '/')
		{
			$hostname = null;
			$port = null;
			$socket = $this->hostname;
		}
		else
		{
			$hostname = ($persistent === true) ? 'p:' . $this->hostname : $this->hostname;
			$port = empty($this->port) ? null : $this->port;
			$socket = null;
		}

		$client_flags = ($this->compress === true) ? MYSQLI_CLIENT_COMPRESS : 0;
		$this->mysqli = mysqli_init();

		mysqli_report(MYSQLI_REPORT_ALL & ~MYSQLI_REPORT_INDEX);

		$this->mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);

		if (isset($this->strictOn))
		{
			if ($this->strictOn)
			{
				$this->mysqli->options(MYSQLI_INIT_COMMAND, 'SET SESSION sql_mode = CONCAT(@@sql_mode, ",", "STRICT_ALL_TABLES")');
			}
			else
			{
				$this->mysqli->options(MYSQLI_INIT_COMMAND, 'SET SESSION sql_mode =
						REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
												@@sql_mode,
												"STRICT_ALL_TABLES,", ""),
											",STRICT_ALL_TABLES", ""),
										"STRICT_ALL_TABLES", ""),
									"STRICT_TRANS_TABLES,", ""),
								",STRICT_TRANS_TABLES", ""),
							"STRICT_TRANS_TABLES", "")'
				);
			}
		}

		if (is_array($this->encrypt))
		{
			$ssl = [];
			empty($this->encrypt['ssl_key']) || $ssl['key'] = $this->encrypt['ssl_key'];
			empty($this->encrypt['ssl_cert']) || $ssl['cert'] = $this->encrypt['ssl_cert'];
			empty($this->encrypt['ssl_ca']) || $ssl['ca'] = $this->encrypt['ssl_ca'];
			empty($this->encrypt['ssl_capath']) || $ssl['capath'] = $this->encrypt['ssl_capath'];
			empty($this->encrypt['ssl_cipher']) || $ssl['cipher'] = $this->encrypt['ssl_cipher'];

			if ( ! empty($ssl))
			{
				if (isset($this->encrypt['ssl_verify']))
				{
					if ($this->encrypt['ssl_verify'])
					{
						defined('MYSQLI_OPT_SSL_VERIFY_SERVER_CERT') &&
								$this->mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);
					}
					// Apparently (when it exists), setting MYSQLI_OPT_SSL_VERIFY_SERVER_CERT
					// to FALSE didn't do anything, so PHP 5.6.16 introduced yet another
					// constant ...
					//
					// https://secure.php.net/ChangeLog-5.php#5.6.16
					// https://bugs.php.net/bug.php?id=68344
					elseif (defined('MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT'))
					{
						$this->mysqli->options(MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT, true);
					}
				}

				$client_flags |= MYSQLI_CLIENT_SSL;
				$this->mysqli->ssl_set(
						$ssl['key'] ?? null, $ssl['cert'] ?? null, $ssl['ca'] ?? null, $ssl['capath'] ?? null, $ssl['cipher'] ?? null
				);
			}
		}

		if ($this->mysqli->real_connect($hostname, $this->username, $this->password, $this->database, $port, $socket, $client_flags)
		)
		{
			// Prior to version 5.7.3, MySQL silently downgrades to an unencrypted connection if SSL setup fails
			if (
					($client_flags & MYSQLI_CLIENT_SSL) && version_compare($this->mysqli->client_info, '5.7.3', '<=') && empty($this->mysqli->query("SHOW STATUS LIKE 'ssl_cipher'")
									->fetch_object()->Value)
			)
			{
				$this->mysqli->close();
				$message = 'MySQLi was configured for an SSL connection, but got an unencrypted connection instead!';
				log_message('error', $message);

				if ($this->DBDebug)
				{
					throw new DatabaseException($message);
				}
				return false;
			}

			if ( ! $this->mysqli->set_charset($this->charset))
			{
				log_message('error', "Database: Unable to set the configured connection charset ('{$this->charset}').");
				$this->mysqli->close();

				if ($this->db->debug)
				{
					throw new DatabaseException('Unable to set client connection character set: ' . $this->charset);
				}
				return false;
			}

			return $this->mysqli;
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Keep or establish the connection if no queries have been sent for
	 * a length of time exceeding the server's idle timeout.
	 *
	 * @return void
	 */
	public function reconnect()
	{
		$this->close();
		$this->initialize();
	}

	//--------------------------------------------------------------------

	/**
	 * Close the database connection.
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
		if ($databaseName === '')
		{
			$databaseName = $this->database;
		}

		if (empty($this->connID))
		{
			$this->initialize();
		}

		if ($this->connID->select_db($databaseName))
		{
			$this->database = $databaseName;

			return true;
		}

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

		if (empty($this->mysqli))
		{
			$this->initialize();
		}

		return $this->dataCache['version'] = $this->mysqli->server_info;
	}

	//--------------------------------------------------------------------

	/**
	 * Executes the query against the database.
	 *
	 * @param string $sql
	 *
	 * @return mixed
	 */
	public function execute($sql)
	{
		while($this->connID->more_results())
		{
			$this->connID->next_result();
			if($res = $this->connID->store_result())
			{
				$res->free();
			}
		}

		return $this->connID->query($this->prepQuery($sql));
	}

	//--------------------------------------------------------------------

	/**
	 * Prep the query
	 *
	 * If needed, each database adapter can prep the query string
	 *
	 * @param    string $sql an SQL query
	 *
	 * @return    string
	 */
	protected function prepQuery($sql)
	{
		// mysqli_affected_rows() returns 0 for "DELETE FROM TABLE" queries. This hack
		// modifies the query so that it a proper number of affected rows is returned.
		if ($this->deleteHack === true && preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/i', $sql))
		{
			return trim($sql) . ' WHERE 1=1';
		}

		return $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the total number of rows affected by this query.
	 *
	 * @return mixed
	 */
	public function affectedRows(): int
	{
		return $this->connID->affected_rows;
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

		if (! $this->connID)
		{
			$this->initialize();
		}

		return $this->connID->real_escape_string($str);
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
		$sql = 'SHOW TABLES FROM ' . $this->escapeIdentifiers($this->database);

		if ($prefixLimit !== FALSE && $this->DBPrefix !== '')
		{
			return $sql . " LIKE '" . $this->escapeLikeStr($this->DBPrefix) . "%'";
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
		return 'SHOW COLUMNS FROM ' . $this->protectIdentifiers($table, TRUE, NULL, FALSE);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an object with field data
	 *
	 * @param	string	$table
	 * @return	array
	 */
	public function _fieldData(string $table)
	{
		if (($query = $this->query('SHOW COLUMNS FROM ' . $this->protectIdentifiers($table, TRUE, NULL, FALSE))) === FALSE)
		{
			return FALSE;
		}
		$query = $query->getResultObject();

		$retval = [];
		for ($i = 0, $c = count($query); $i < $c; $i ++ )
		{
			$retval[$i] = new \stdClass();
			$retval[$i]->name = $query[$i]->Field;

			sscanf($query[$i]->Type, '%[a-z](%d)', $retval[$i]->type, $retval[$i]->max_length
			);

			$retval[$i]->default = $query[$i]->Default;
			$retval[$i]->primary_key = (int) ($query[$i]->Key === 'PRI');
		}

		return $retval;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an object with index data
	 *
	 * @param	string	$table
	 * @return	array
	 */
	public function _indexData(string $table)
	{
		if (($query = $this->query('SHOW CREATE TABLE ' . $this->protectIdentifiers($table, TRUE, NULL, FALSE))) === FALSE)
		{
			return FALSE;
		}
		$row = $query->getRowArray();
		if ( ! $row)
		{
			return FALSE;
		}

		$retval = [];
		foreach (explode("\n", $row['Create Table']) as $line)
		{
			$line = trim($line);
			if (strpos($line, 'PRIMARY KEY') === 0)
			{
				$obj = new \stdClass();
				$obj->name = 'PRIMARY KEY';
				$_fields = explode(',', preg_replace('/^.*\((.+)\).*$/', '$1', $line));
				$obj->fields = array_map(function($v) {
					return trim($v, '`');
				}, $_fields);
				$obj->type = 'PRIMARY';

				$retval[] = $obj;
			}
			elseif (($unique = strpos($line, 'UNIQUE KEY') === 0) || strpos($line, 'KEY') === 0)
			{
				if (preg_match('/KEY `([^`]+)` \((.+)\)/', $line, $matches))
				{
					$obj = new \stdClass();
					$obj->name = $matches[1];
					$obj->fields = array_map(function($v) {
						return trim($v, '`');
					}, explode(',', $matches[2]));
					$obj->type = $unique ? 'UNIQUE' : 'INDEX';

					$retval[] = $obj;
				}
				else
				{
					throw new \LogicException('parsing key string failed.');
				}
			}
		}

		return $retval;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an object with Foreign key data
	 *
	 * @param	string	$table
	 * @return	array
	 */
	public function _foreignKeyData(string $table)
	{
		$sql = '
                    SELECT
                        tc.CONSTRAINT_NAME,
                        tc.TABLE_NAME,
                        rc.REFERENCED_TABLE_NAME
                    FROM information_schema.TABLE_CONSTRAINTS AS tc
                    INNER JOIN information_schema.REFERENTIAL_CONSTRAINTS AS rc
                        ON tc.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
                    WHERE
                        tc.CONSTRAINT_TYPE = '.$this->escape('FOREIGN KEY').' AND
                        tc.TABLE_SCHEMA = '.$this->escape($this->database).' AND
                        tc.TABLE_NAME = '.$this->escape($table);

		if (($query = $this->query($sql)) === false)
		{
			return false;
		}
		$query = $query->getResultObject();

		$retval = [];
		foreach ($query as $row)
		{
			$obj = new \stdClass();
			$obj->constraint_name = $row->CONSTRAINT_NAME;
                        $obj->table_name = $row->TABLE_NAME;
                        $obj->foreign_table_name = $row->REFERENCED_TABLE_NAME;

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
	 * @return	array
	 */
	public function error()
	{
		if ( ! empty($this->mysqli->connect_errno))
		{
			return [
				'code'		 => $this->mysqli->connect_errno,
				'message'	 => $this->_mysqli->connect_error
			];
		}

		return ['code' => $this->connID->errno, 'message' => $this->connID->error];
	}

	//--------------------------------------------------------------------

	/**
	 * Insert ID
	 *
	 * @return	int
	 */
	public function insertID()
	{
		return $this->connID->insert_id;
	}

	//--------------------------------------------------------------------

	/**
	 * Begin Transaction
	 *
	 * @return	bool
	 */
	protected function _transBegin(): bool
	{
		$this->connID->autocommit(false);

		return $this->connID->begin_transaction();
	}

	//--------------------------------------------------------------------

	/**
	 * Commit Transaction
	 *
	 * @return	bool
	 */
	protected function _transCommit(): bool
	{
		if ($this->connID->commit())
		{
			$this->connID->autocommit(true);
			return true;
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Rollback Transaction
	 *
	 * @return	bool
	 */
	protected function _transRollback(): bool
	{
		if ($this->connID->rollback())
		{
			$this->connID->autocommit(true);
			return true;
		}

		return false;
	}

	//--------------------------------------------------------------------
}
