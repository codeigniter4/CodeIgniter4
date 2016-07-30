<?php namespace CodeIgniter\Database\MySQLi;

use CodeIgniter\Database\PreparedQueryInterface;
use \CodeIgniter\Database\BasePreparedQuery;

class PreparedQuery extends BasePreparedQuery implements PreparedQueryInterface
{
	/**
	 * Takes a new set of data and runs it against the currently
	 * prepared query. Upon success, will return a Results object.
	 *
	 * @param array $data
	 *
	 * @return ResultInterface|null
	 */
	public function execute(array $data)
	{

	}

	//--------------------------------------------------------------------

	/**
	 * Prepares the query against the database, and saves the connection
	 * info necessary to execute the query later.
	 *
	 * NOTE: This version is based on SQL code. Child classes should
	 * override this method.
	 *
	 * @param string $sql
	 * @param array  $options  Passed to the connection's prepare statement.
	 *                         Unused in the MySQLi driver.
	 *
	 * @return mixed
	 */
	public function prepare(string $sql, array $options = [])
	{
		// Mysqli driver doesn't like statements
		// with terminating semicolons.
		$this->sql = rtrim($sql, ';');

		// MySQLi also only supports positional placeholders (?)
		// so we need to replace our named placeholders (:name)
		$this->sql = preg_replace('/:[\S]+/', '?', $this->sql);

		if (! $this->statement = $this->db->mysqli->prepare($this->sql))
		{
			$this->errorCode   = $this->db->mysqli->errno;
			$this->errorString = $this->db->mysqli->error;
		}

		return $this;
	}

	//--------------------------------------------------------------------
}
