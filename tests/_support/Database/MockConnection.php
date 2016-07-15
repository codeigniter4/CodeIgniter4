<?php namespace CodeIgniter\Database;

class MockConnection extends BaseConnection
{
	protected $returnValues = [];

	public $database;

	public $saveQueries = true;

	//--------------------------------------------------------------------

	public function shouldReturn(string $method, $return)
	{
		$this->returnValues[$method] = $return;

		return $this;
	}

	//--------------------------------------------------------------------

	public function query(string $sql, $binds = null)
	{
		$queryClass = str_replace('Connection', 'Query', get_class($this));

		$query = new $queryClass($this);

		$query->setQuery($sql, $binds);

		if (! empty($this->swapPre) && ! empty($this->DBPrefix))
		{
			$query->swapPrefix($this->DBPrefix, $this->swapPre);
		}

		$startTime = microtime(true);

		// Run the query
		if (false === ($this->resultID = $this->simpleQuery($query->getQuery())))
		{
			$query->setDuration($startTime, $startTime);

			// @todo deal with errors

			if ($this->saveQueries)
			{
				$this->queries[] = $query;
			}

			return false;
		}

		$query->setDuration($startTime);

		if ($this->saveQueries)
		{
			$this->queries[] = $query;
		}

		$resultClass = str_replace('Connection', 'Result', get_class($this));

		return new $resultClass($this->connID, $this->resultID);
	}

	//--------------------------------------------------------------------

	/**
	 * Connect to the database.
	 *
	 * @return mixed
	 */
	public function connect($persistent = false)
	{
		$return = $this->returnValues['connect'] ?? true;

		if (is_array($return))
		{
			// By removing the top item here, we can
			// get a different value for, say, testing failover connections.
			$return = array_shift($this->returnValues['connect']);
		}

		return $return;
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
		return true;
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
		$this->database = $databaseName;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a string containing the version of the database being used.
	 *
	 * @return mixed
	 */
	public function getVersion()
	{
		return \CodeIgniter\CodeIgniter::CI_VERSION;
	}

	//--------------------------------------------------------------------

	/**
	 * Executes the query against the database.
	 *
	 * @param $sql
	 *
	 * @return mixed
	 */
	protected function execute($sql)
	{
		return $this->returnValues['execute'];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the total number of rows affected by this query.
	 *
	 * @return mixed
	 */
	public function affectedRows(): int
	{
		return 1;
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
		return ['code' => null, 'message' => null];
	}

	//--------------------------------------------------------------------

	/**
	 * Insert ID
	 *
	 * @return	int
	 */
	public function insertID()
	{
		return $this->conn_id->insert_id;
	}

	//--------------------------------------------------------------------

	/**
	 * Generates the SQL for listing tables in a platform-dependent manner.
	 *
	 * @param bool $constrainByPrefix
	 *
	 * @return string
	 */
	protected function _listTables($constrainByPrefix = false): string
	{
		return '';
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
		return '';
	}

	//--------------------------------------------------------------------
}
