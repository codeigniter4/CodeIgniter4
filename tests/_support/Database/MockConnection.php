<?php namespace CodeIgniter\Database;

class MockConnection extends BaseConnection
{
	protected $returnValues;

	public $database;

	//--------------------------------------------------------------------

	public function shouldReturn(string $method, $return)
	{
		$this->returnValue = $return;
	}

	//--------------------------------------------------------------------

	public function query(string $sql, $binds = null)
	{
		$queryClass = str_replace('Connection', 'Query', get_class($this));

		$query = new $queryClass();

		$query->setQuery($sql, $binds);

		if (! empty($this->swapPre) && ! empty($this->dbprefix))
		{
			$query->swapPrefix($this->dbprefix, $this->swapPre);
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
	public function connect($persistant = false)
	{
		// ?
		return $this;
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
		return CI_VERSION;
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
		return $this->returnValue;
	}

	//--------------------------------------------------------------------

}
