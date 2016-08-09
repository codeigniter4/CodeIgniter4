<?php namespace CodeIgniter\Database;

abstract class BasePreparedQuery implements PreparedQueryInterface
{
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
	 * Holds the prepared query object
	 * that is cloned during execute.
	 *
	 * @var Query
	 */
	protected $query;

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
	public function prepare(string $sql, array $options = [], $queryClass = 'CodeIgniter\\Database\\Query')
	{
		// We only supports positional placeholders (?)
		// in order to work with the execute method below, so we
		// need to replace our named placeholders (:name)
		$sql = preg_replace('/:[^\s,)]+/', '?', $sql);

		$query = new $queryClass($this->db);

		$query->setQuery($sql);

		if (! empty($this->db->swapPre) && ! empty($this->db->DBPrefix))
		{
			$query->swapPrefix($this->db->DBPrefix, $this->db->swapPre);
		}

		$this->query = $query;

		return $this->_prepare($query->getOriginalQuery(), $options);
	}

	//--------------------------------------------------------------------

	/**
	 * The database-dependent portion of the prepare statement.
	 *
	 * @param string $sql
	 * @param array  $options  Passed to the connection's prepare statement.
	 *
	 * @return mixed
	 */
	abstract public function _prepare(string $sql, array $options = []);

	//--------------------------------------------------------------------

	/**
	 * Takes a new set of data and runs it against the currently
	 * prepared query. Upon success, will return a Results object.
	 *
	 * @param array $data
	 *
	 * @return ResultInterface
	 */
	public function execute(...$data)
	{
		// Execute the Query.
		$startTime = microtime(true);

		$this->_execute($data);

		// Update our query object
		$query = clone $this->query;
		$query->setBinds($data);

		$query->setDuration($startTime);

		// Save it to the connection
		$this->db->addQuery($query);

		// Return a result object
		$resultClass = str_replace('PreparedQuery', 'Result', get_class($this));

		$resultID = $this->_getResult();

		return new $resultClass($this->db->connID, $resultID);
	}

	//--------------------------------------------------------------------

	/**
	 * The database dependant version of the execute method.
	 *
	 * @param array $data
	 *
	 * @return ResultInterface
	 */
	abstract public function _execute($data);

	//--------------------------------------------------------------------

	/**
	 * Returns the result object for the prepared query.
	 *
	 * @return mixed
	 */
	abstract public function _getResult();

	//--------------------------------------------------------------------

	/**
	 * Explicity closes the statement.
	 */
	public function close()
	{
		if (! is_object($this->statement))
		{
			return;
		}

	    $this->statement->close();
	}

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
