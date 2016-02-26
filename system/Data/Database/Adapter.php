<?php namespace CodeIgniter\Data\Database;

abstract class Adapter
{
	protected $benchmark;
	protected $queryCount = 0;
	protected $debugData = [];
	protected $performanceData = [];
	protected $queries = [];
	protected $queryTimes = [];
	protected $totalQueryTime = 0;

	protected $connectionConfig;
	protected $connectionId = false;
	protected $connection;

	protected $transactionDepth = 0;

	abstract public function dbQuery($sql);

	public function __construct(\CodeIgniter\Data\Database\Connection $connection)
	{
		$this->connection = $connection;
		$this->connectionConfig = $this->connection->getConfig();
	}

	public function error()
	{
		if (isset($this->errorMessage))
		{
			return [
				'code'    => isset($this->errorCode) ? $this->errorCode : '',
				'message' => $this->errorMessage,
			];
		}

		if ($this->connectionId)
		{
			return [
				'code'    => $this->connection->getErrorCode(),
				'message' => $this->connection->getErrorMessage(),
			];
		}

		return [
			'code'    => null,
			'message' => null,
		];
	}

	public function initialize()
	{
		// If an established connection is available, there's no need to connect
		// and select the database. Depending on the adapter, connectionId can be
		// boolean true, a resource, or an object.
		if ($this->connectionId)
		{
			return;
		}

		// Connect to the database and set the connection ID
		if ( ! isset($this->connection))
		{
			throw new \Exception('Connection initialization attempted without a valid connection object.');
		}
		if ($this->connection->connect())
		{
			$this->connectionId = $this->connection->getId();
		}

		// No connection resource? Check for a failover.
		if ( ! $this->connectionId)
		{
			if ( ! empty($this->connectionConfig->failover) && is_array($this->connectionConfig->failover))
			{
				$failover = $this->connectionConfig->failover;
				foreach ($failover as $failoverConfig)
				{
					$this->connectionConfig = \CodeIgniter\Config\Database\ConnectionConfigFactory::build($failoverConfig);
					// @todo fix this... need to build a Connection from the ConnectionConfig...
					// $this->connectionId = $this->connect();

					// If a connection is made, break the foreach loop
					if ($this->connectionId)
					{
						break;
					}
				}
			}

			// We still don't have a connection?
			if ( ! $this->connectionId)
			{
				throw new \Exception('Unable to connect to the database');
			}
		}

		// Now set the character set
		$this->setCharacterSet($this->connectionConfig->characterSet);
	}

	protected function loadResultDriver()
	{
		$adapter = $this->connectionConfig->getAdapter();

		if (class_exists($resultAdapter = "\\CodeIgniter\\Data\\Database\\{$adapter}\\Result")
			|| $resultAdapter = class_exists("\\App\\Data\\Database\\{$adapter}\\Result")
		)
		{
			return new $resultAdapter($this->connection, $this->resultId);
		}

		throw new \Exception('No Result adapter found for the current connection.');
	}

	public function query($sql, $binds = false, $returnObject = null)
	{
		if ($sql === '')
		{
			throw new \InvalidArgumentException("Invalid query: '{$sql}'");
		}

		if ( ! is_bool($returnObject))
		{
			// @TODO: is_write_type()
			$returnObject = false;
		}

		// @TODO: prefix

		// @TODO: Compile binds if needed
		if ($binds !== false)
		{
			// $sql = $this->compileBinds($sql, $binds);
		}

		// @TODO: Query Caching

		if ($this->connectionConfig->saveStatementsEnabled === true)
		{
			$this->queries[] = $sql;
		}

		// Start the query timer
		// $this->benchmark->start("database_query_{$this->queryCount}");
		$timeStart = microtime(true);

		// Run the query
		if (false === ($this->resultId = $this->dbQuery($sql)))
		{
			if ($this->connectionConfig->saveStatementsEnabled === true)
			{
				$this->queryTimes[] = 0;
			}

			// Trigger a rollback if transactions are being used
			if ($this->transactionDepth !== 0)
			{
				$this->transactionStatus = false;
			}

			$error = $this->error();

			throw new \Exception("Query error: '{$error['message']}'' - Invalid query: '{$sql}'");
		}

		// Stop and aggregate query time results
		$timeEnd = microtime(true);
		$this->logPerformance($timeStart, $timeEnd, "Database Query {$this->queryCount}");

		if ($this->connectionConfig->saveStatementsEnabled === true)
		{
			$this->queryTimes[] = $timeEnd - $timeStart;
		}

		// Increment the query counter
		++$this->queryCount;

		// Will a result object be instantiated? If not, return true.
		if ($returnObject !== true)
		{
			// @TODO: caching

			// return true;
		}

		// Load and instantiate the result
		$result = $this->loadResultDriver();

		// @TODO: caching
		if ($this->connectionConfig->cacheEnabled === true)
		{

		}

		return $result;
	}

	public function setCharacterSet($charset)
	{
		if (method_exists($this, 'setDBCharacterSet') && ! $this->setDBCharacterSet($charset))
		{
			throw new \Exception("Unable to set database connection character set: '{$charset}'");
		}
	}

	public function getDebugData(): array
	{
		if ($this->connectionConfig->debugEnabled)
		{
			if ( ! isset($this->debugData['queryTimes']))
			{
				$this->debugData['queryTimes'] = $this->queryTimes;
			}
			if ($this->connectionConfig->saveStatementsEnabled && ! isset($this->debugData['queries']))
			{
				$this->debugData['queries'] = $this->queries;
			}
		}
		return $this->debugData;
	}

	/**
	 * Returns any performance data which may have been collected during execution.
	 *
	 * @return array
	 */
	public function getPerformanceData(): array
	{
		return $this->performanceData;
	}

	/**
	 * Logs performance data for queries.
	 *
	 * @param float  $start The start time for the data.
	 * @param float  $end   The end time for the data.
	 * @param string $tag   The identifier for this data.
	 */
	protected function logPerformance(float $start, float $end, string $tag)
	{
		if (! $this->connectionConfig->debugEnabled) {
			return;
		}

		$this->performanceData[] = [
			'start' => $start,
			'end'   => $end,
			'tag'   => $tag,
		];
	}
}
