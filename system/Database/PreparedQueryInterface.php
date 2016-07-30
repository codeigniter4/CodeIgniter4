<?php namespace CodeIgniter\Database;

interface PreparedQueryInterface
{
	/**
	 * Takes a new set of data and runs it against the currently
	 * prepared query. Upon success, will return a Results object.
	 *
	 * @param array $data
	 *
	 * @return ResultInterface
	 */
	public function execute(array $data);

	//--------------------------------------------------------------------

	/**
	 * Takes an array containing multiple rows of data that should
	 * be inserted, one after the other, using the prepared statement.
	 *
	 * @param array $data
	 *
	 * @return \CodeIgniter\Database\ResultInterface
	 */
	public function executeBatch(array $data): ResultInterface;

	//--------------------------------------------------------------------

	/**
	 * Prepares the query against the database, and saves the connection
	 * info necessary to execute the query later.
	 *
	 * @param string $sql
	 * @param array  $options  Passed to the connection's prepare statement.
	 *
	 * @return mixed
	 */
	public function prepare(string $sql, array $options = []);

	//--------------------------------------------------------------------

	/**
	 * Returns the SQL that has been prepared.
	 *
	 * @return string
	 */
	public function getQueryString(): string;

	//--------------------------------------------------------------------

	/**
	 * Returns the error code created while executing this statement.
	 *
	 * @return string
	 */
	public function getErrorCode(): int;

	//--------------------------------------------------------------------

	/**
	 * Returns the error message created while executing this statement.
	 *
	 * @return string
	 */
	public function getErrorMessage(): string;

	//--------------------------------------------------------------------
}
