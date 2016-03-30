<?php namespace CodeIgniter\Database;

use CodeIgniter\DatabaseException;

abstract class BaseConnection implements ConnectionInterface
{
	/**
	 * Data Source Name / Connect string
	 *
	 * @var    string
	 */
	public $dsn;

	/**
	 * Username
	 *
	 * @var    string
	 */
	public $username;

	/**
	 * Password
	 *
	 * @var    string
	 */
	public $password;

	/**
	 * Hostname
	 *
	 * @var    string
	 */
	public $hostname;

	/**
	 * Database name
	 *
	 * @var    string
	 */
	public $database;

	/**
	 * Database driver
	 *
	 * @var    string
	 */
	public $dbdriver = 'mysqli';

	/**
	 * Sub-driver
	 *
	 * @used-by    CI_DB_pdo_driver
	 * @var    string
	 */
	public $subdriver;

	/**
	 * Table prefix
	 *
	 * @var    string
	 */
	public $dbprefix = '';

	/**
	 * Character set
	 *
	 * @var    string
	 */
	public $charset = 'utf8';

	/**
	 * Collation
	 *
	 * @var    string
	 */
	public $dbcollat = 'utf8_general_ci';

	/**
	 * Encryption flag/data
	 *
	 * @var    mixed
	 */
	public $encrypt = false;

	/**
	 * Swap Prefix
	 *
	 * @var    string
	 */
	public $swapPre = '';

	/**
	 * Database port
	 *
	 * @var    int
	 */
	public $port = '';

	/**
	 * Persistent connection flag
	 *
	 * @var    bool
	 */
	public $pconnect = false;

	/**
	 * Connection ID
	 *
	 * @var    object|resource
	 */
	public $connID = false;

	/**
	 * Result ID
	 *
	 * @var    object|resource
	 */
	public $resultID = false;

	/**
	 * Array of query objects that have executed
	 * on this connection.
	 *
	 * @var array
	 */
	protected $queries = [];

	/**
	 * Whether to keep an in-memory history of queries
	 * for debugging and timeline purposes.
	 *
	 * @var bool
	 */
	protected $saveQueries = false;

	/**
	 * Protect identifiers flag
	 *
	 * @var    bool
	 */
	protected $protectIdentifiers = true;

	/**
	 * List of reserved identifiers
	 *
	 * Identifiers that must NOT be escaped.
	 *
	 * @var    string[]
	 */
	protected $reservedIdentifiers = ['*'];

	/**
	 * Debug flag
	 *
	 * Whether to display error messages.
	 *
	 * @var    bool
	 */
	protected $db_debug = false;

	/**
	 * Holds previously looked up data
	 * for performance reasons.
	 *
	 * @var array
	 */
	protected $dataCache = [];

	protected $connectTime;

	protected $connectDuration;

	//--------------------------------------------------------------------

	/**
	 * Saves our connection settings.
	 *
	 * @param array $params
	 */
	public function __construct(array $params)
	{
	    foreach ($params as $key => $value)
	    {
		    $this->$key = $value;
	    }
	}

	//--------------------------------------------------------------------


	/**
	 * Initializes the database connection/settings.
	 *
	 * @return mixed
	 */
	public function initialize()
	{
		/* If an established connection is available, then there's
		 * no need to connect and select the database.
		 *
		 * Depending on the database driver, conn_id can be either
		 * boolean TRUE, a resource or an object.
		 */
		if ($this->connID)
		{
			return;
		}

		//--------------------------------------------------------------------

		$this->connectTime = microtime(true);

		// Connect to the database and set the connection ID
		$this->connID = $this->connect($this->pconnect);

		// No connection resource? Check if there is a failover else throw an error
		if ( ! $this->connID)
		{
			// Check if there is a failover set
			if ( ! empty($this->failover) && is_array($this->failover))
			{
				// Go over all the failovers
				foreach ($this->failover as $failover)
				{
					// Replace the current settings with those of the failover
					foreach ($failover as $key => $val)
					{
						$this->$key = $val;
					}

					// Try to connect
					$this->conn_id = $this->connect($this->pconnect);

					// If a connection is made break the foreach loop
					if ($this->connID)
					{
						break;
					}
				}
			}

			// We still don't have a connection?
			if ( ! $this->connID)
			{
				throw new \RuntimeException('Unable to connect to the database.');
			}
		}

		$this->connectDuration = microtime(true) - $this->connectTime;
	}

	//--------------------------------------------------------------------

	/**
	 * Connect to the database.
	 *
	 * @return mixed
	 */
	abstract public function connect($persistant = false);

	//--------------------------------------------------------------------

	/**
	 * Create a persistant database connection.
	 *
	 * @return mixed
	 */
	public function persistantConnect()
	{
		return $this->connect(true);
	}

	//--------------------------------------------------------------------

	/**
	 * Keep or establish the connection if no queries have been sent for
	 * a length of time exceeding the server's idle timeout.
	 *
	 * @return mixed
	 */
	abstract public function reconnect();

	//--------------------------------------------------------------------

	/**
	 * Returns the actual connection object. If both a 'read' and 'write'
	 * connection has been specified, you can pass either term in to
	 * get that connection. If you pass either alias in and only a single
	 * connection is present, it must return the sole connection.
	 *
	 * @param string|null $alias
	 *
	 * @return mixed
	 */
	public function getConnection(string $alias = null)
	{
		//@todo work with read/write connections
		return $this->connID;
	}

	//--------------------------------------------------------------------

	/**
	 * Select a specific database table to use.
	 *
	 * @param string $databaseName
	 *
	 * @return mixed
	 */
	abstract function setDatabase(string $databaseName);

	//--------------------------------------------------------------------

	/**
	 * Returns the name of the current database being used.
	 *
	 * @return string
	 */
	public function getDatabase(): string
	{
		return empty($this->database) ? '' : $this->database;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the last error encountered by this connection.
	 *
	 * @return mixed
	 */
	public function getError()
	{
	}

	//--------------------------------------------------------------------

	/**
	 * The name of the platform in use (mysqli, mssql, etc)
	 *
	 * @return mixed
	 */
	public function getPlatform()
	{
		return $this->dbdriver;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a string containing the version of the database being used.
	 *
	 * @return mixed
	 */
	abstract function getVersion();

	//--------------------------------------------------------------------

	/**
	 * Specifies whether this connection should keep queries objects or not.
	 *
	 * @param bool $doLog
	 */
	public function saveQueries($save = false)
	{
		$this->saveQueries = $save;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Executes the query against the database.
	 *
	 * @param $sql
	 *
	 * @return mixed
	 */
	abstract protected function execute($sql);

	//--------------------------------------------------------------------


	/**
	 * Orchestrates a query against the database. Queries must use
	 * Database\Statement objects to store the query and build it.
	 * This method works with the cache.
	 *
	 * Should automatically handle different connections for read/write
	 * queries if needed.
	 *
	 * @param string $sql
	 * @param array  ...$binds
	 *
	 * @return mixed
	 */
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
	 * Performs a basic query against the database. No binding or caching
	 * is performed, nor are transactions handled. Simply takes a raw
	 * query string and returns the database-specific result id.
	 *
	 * @param string $sql
	 *
	 * @return mixed
	 */
	public function simpleQuery(string $sql)
	{
		empty($this->connID) && $this->initialize();

		return $this->execute($sql);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an instance of the query builder for this connection.
	 *
	 * @param string $tableName
	 *
	 * @return BaseBuilder
	 */
	public function table(string $tableName)
	{
		if (empty($table))
		{
			throw new DatabaseException('You must set the database table to be used with your query.');
		}

		$className = str_replace('Connection', 'Builder', get_class($this));

		$options = [
			'swapPre' => $this->swapPre,
		    'dbprefix' => $this->dbprefix
		];

		return new $className($tableName, $this, $options);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an array containing all of the
	 *
	 * @return array
	 */
	public function getQueries(): array
	{
		return $this->queries;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the total number of queries that have been performed
	 * on this connection.
	 *
	 * @return mixed
	 */
	public function getQueryCount()
	{
		return count($this->queries);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the last query's statement object.
	 *
	 * @return mixed
	 */
	public function getLastQuery()
	{
		return end($this->queries);
	}

	//--------------------------------------------------------------------

	/**
	 * "Smart" Escaping
	 *
	 * Escapes data based on type.
	 * Sets boolean and null types.
	 *
	 * @param $str
	 *
	 * @return mixed
	 */
	public function escape($str)
	{
		if (count($this->queries))
		{
			$query = end($this->queries);
		}
		else
		{
			$queryClass = str_replace('Connection', 'Query', get_class($this));
			$query = new $queryClass();
		}

		return $query->escape($str);
	}

	//--------------------------------------------------------------------

	public function getConnectStart()
	{
	    return $this->connectTime;
	}

	//--------------------------------------------------------------------

	public function getConnectDuration($decimals = 6)
	{
	    return number_format($this->connectDuration, $decimals);
	}

	//--------------------------------------------------------------------



}
