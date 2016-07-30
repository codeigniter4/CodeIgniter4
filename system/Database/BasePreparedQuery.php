<?php namespace CodeIgniter\Database;

abstract class BasePreparedQuery implements PreparedQueryInterface
{
	/**
	 * The SQL that this statement uses.
	 *
	 * @var string
	 */
	protected $sql;

	/**
	 * The prepared statement itself.
	 *
	 * @var
	 */
	protected $statement;

	/**
	 * The error code, if any.
	 *
	 * @var int
	 */
	protected $errorCode;

	/**
	 * The error message, if any.
	 *
	 * @var string
	 */
	protected $errorString;

	/**
	 * A reference to the db connection to use.
	 *
	 * @var \CodeIgniter\Database\ConnectionInterface
	 */
	protected $db;

	//--------------------------------------------------------------------

	public function __construct(ConnectionInterface $db)
	{
		$this->db =& $db;
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
	abstract public function execute(array $data);

	//--------------------------------------------------------------------

	/**
	 * Takes an array containing multiple rows of data that should
	 * be inserted, one after the other, using the prepared statement.
	 *
	 * @param array $data
	 *
	 * @return \CodeIgniter\Database\ResultInterface
	 */
	public function executeBatch(array $data): ResultInterface
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
	 *
	 * @return mixed
	 */
	abstract public function prepare(string $sql, array $options = []);

	//--------------------------------------------------------------------

	/**
	 * Returns the SQL that has been prepared.
	 *
	 * @return string
	 */
	public function getQueryString(): string
	{
		return $this->sql;
	}

	//--------------------------------------------------------------------

	/**
	 * A helper to determine if any error exists.
	 *
	 * @return bool
	 */
	public function hasError()
	{
	    return ! empty($this->errorString);
	}

	//--------------------------------------------------------------------


	/**
	 * Returns the error code created while executing this statement.
	 *
	 * @return string
	 */
	public function getErrorCode(): int
	{
		return $this->errorCode;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the error message created while executing this statement.
	 *
	 * @return string
	 */
	public function getErrorMessage(): string
	{
		return $this->errorString;
	}

	//--------------------------------------------------------------------
}
