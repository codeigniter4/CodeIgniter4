<?php namespace CodeIgniter\Database\SQLite3;

use CodeIgniter\Database\PreparedQueryInterface;
use \CodeIgniter\Database\BasePreparedQuery;

class PreparedQuery extends BasePreparedQuery implements PreparedQueryInterface
{
	/**
	 * Prepares the query against the database, and saves the connection
	 * info necessary to execute the query later.
	 *
	 * NOTE: This version is based on SQL code. Child classes should
	 * override this method.
	 *
	 * @param string $sql
	 * @param array  $options  Passed to the connection's prepare statement.
	 *                         Unused in the SQLite3 driver.
	 *
	 * @return mixed
	 */
	public function _prepare(string $sql, array $options = [])
	{
		// sqlite driver doesn't like statements
		// with terminating semicolons.
		$this->sql = rtrim($sql, ';');

		if (! $this->statement = $this->db->sqlite3->prepare($this->sql))
		{
			$this->errorCode   = $this->db->sqlite3->lastErrorCode();
			$this->errorString = $this->db->sqlite3->lastErrorMsg ;
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Takes a new set of data and runs it against the currently
	 * prepared query. Upon success, will return a Results object.
	 *
	 * @param array $data
	 *
	 * @return ResultInterface
	 */
	public function _execute($data)
	{
		if (is_null($this->statement))
		{
			throw new \BadMethodCallException('You must call prepare before trying to execute a prepared statement.');
		}

		$i = 1;
		foreach ($data as $value) {

			if (is_array($value)) {

				$this->statement->bindParam($i, $value);

			} else {

			    switch (gettype($value)) {

				case 'double':
				    $type = SQLITE3_FLOAT;
				    break;
				case 'integer':
				case 'boolean':
				    $type = SQLITE3_INTEGER;
				    break;
				case 'NULL':
				    $type = SQLITE3_NULL;
				    break;
				case 'string':
				    $type = SQLITE3_TEXT;
				    break;
				default:
				    $type = SQLITE3_BLOB;
			    }

			    $this->statement->bindValue($i, $value, $type);
			}

			$i++;
		}

		$this->result = $this->statement->execute();

		return (bool) $this->result;

	}

	//--------------------------------------------------------------------

	/**
	 * Returns the result object for the prepared query.
	 *
	 * @return mixed
	 */
	public function _getResult()
	{
	    return $this->result;
	}

	//--------------------------------------------------------------------

}
