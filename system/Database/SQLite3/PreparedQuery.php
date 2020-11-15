<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Database\SQLite3;

use BadMethodCallException;
use CodeIgniter\Database\BasePreparedQuery;

/**
 * Prepared query for SQLite3
 */
class PreparedQuery extends BasePreparedQuery
{
	/**
	 * The SQLite3Result resource, or false.
	 *
	 * @var Result|boolean
	 */
	protected $result;

	/**
	 * Prepares the query against the database, and saves the connection
	 * info necessary to execute the query later.
	 *
	 * NOTE: This version is based on SQL code. Child classes should
	 * override this method.
	 *
	 * @param string $sql
	 * @param array  $options Passed to the connection's prepare statement.
	 *                        Unused in the MySQLi driver.
	 *
	 * @return mixed
	 */
	public function _prepare(string $sql, array $options = [])
	{
		if (! ($this->statement = $this->db->connID->prepare($sql)))
		{
			$this->errorCode   = $this->db->connID->lastErrorCode();
			$this->errorString = $this->db->connID->lastErrorMsg();
		}

		return $this;
	}

	/**
	 * Takes a new set of data and runs it against the currently
	 * prepared query. Upon success, will return a Results object.
	 *
	 * @todo finalize()
	 *
	 * @param array $data
	 *
	 * @return boolean
	 */
	public function _execute(array $data): bool
	{
		if (! isset($this->statement))
		{
			throw new BadMethodCallException('You must call prepare before trying to execute a prepared statement.');
		}

		foreach ($data as $key => $item)
		{
			// Determine the type string
			if (is_integer($item))
			{
				$bindType = SQLITE3_INTEGER;
			}
			elseif (is_float($item))
			{
				$bindType = SQLITE3_FLOAT;
			}
			else
			{
				$bindType = SQLITE3_TEXT;
			}

			// Bind it
			$this->statement->bindValue($key + 1, $item, $bindType);
		}

		$this->result = $this->statement->execute();

		return $this->result !== false;
	}

	/**
	 * Returns the result object for the prepared query.
	 *
	 * @return mixed
	 */
	public function _getResult()
	{
		return $this->result;
	}
}
