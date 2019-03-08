<?php namespace Tests\Support\Database;

use CodeIgniter\Database\BaseConnection;

class MockConnection extends BaseConnection
{
	protected $returnValues = [];

	public $database;

	public $lastQuery;

	//--------------------------------------------------------------------

	public function shouldReturn(string $method, $return)
	{
		$this->returnValues[$method] = $return;

		return $this;
	}

	//--------------------------------------------------------------------

	public function query(string $sql, $binds = null, bool $setEscapeFlags = true, $queryClass = 'CodeIgniter\\Database\\Query')
	{
		$queryClass = str_replace('Connection', 'Query', get_class($this));

		$query = new $queryClass($this);

		$query->setQuery($sql, $binds, $setEscapeFlags);

		if (! empty($this->swapPre) && ! empty($this->DBPrefix))
		{
			$query->swapPrefix($this->DBPrefix, $this->swapPre);
		}

		$startTime = microtime(true);

		$this->lastQuery = $query;

		// Run the query
		if (false === ($this->resultID = $this->simpleQuery($query->getQuery())))
		{
			$query->setDuration($startTime, $startTime);

			// @todo deal with errors

			return false;
		}

		$query->setDuration($startTime);

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
	 * @return array
	 */
	public function error()
	{
		return [
			'code'    => null,
			'message' => null,
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Insert ID
	 *
	 * @return integer
	 */
	public function insertID()
	{
		return $this->connID->insert_id;
	}

	//--------------------------------------------------------------------

	/**
	 * Generates the SQL for listing tables in a platform-dependent manner.
	 *
	 * @param boolean $constrainByPrefix
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

	/**
	 * @param  string $table
	 * @return array
	 */
	protected function _fieldData(string $table): array
	{
		return [];
	}

	/**
	 * @param  string $table
	 * @return array
	 */
	protected function _indexData(string $table): array
	{
		return [];
	}

	/**
	 * @param  string $table
	 * @return array
	 */
	protected function _foreignKeyData(string $table): array
	{
		return [];
	}

	//--------------------------------------------------------------------

	/**
	 * Close the connection.
	 */
	protected function _close()
	{
		return;
	}

	//--------------------------------------------------------------------

	/**
	 * Begin Transaction
	 *
	 * @return boolean
	 */
	protected function _transBegin(): bool
	{
		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Commit Transaction
	 *
	 * @return boolean
	 */
	protected function _transCommit(): bool
	{
		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Rollback Transaction
	 *
	 * @return boolean
	 */
	protected function _transRollback(): bool
	{
		return true;
	}

	//--------------------------------------------------------------------
}
