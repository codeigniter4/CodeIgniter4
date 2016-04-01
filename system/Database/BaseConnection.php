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
	public $protectIdentifiers = true;

	/**
	 * List of reserved identifiers
	 *
	 * Identifiers that must NOT be escaped.
	 *
	 * @var    string[]
	 */
	protected $reservedIdentifiers = ['*'];

	/**
	 * Identifier escape character
	 *
	 * @var    string
	 */
	public $escapeChar = '"';

	/**
	 * ESCAPE statement string
	 *
	 * @var    string
	 */
	public $likeEscapeStr = " ESCAPE '%s' ";

	/**
	 * ESCAPE character
	 *
	 * @var    string
	 */
	public $likeEscapeChar = '!';

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
	public function table($tableName)
	{
		if (empty($tableName))
		{
			throw new DatabaseException('You must set the database table to be used with your query.');
		}

		$className = str_replace('Connection', 'Builder', get_class($this));

		return new $className($tableName, $this);
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

	/**
	 * Protect Identifiers
	 *
	 * This function is used extensively by the Query Builder class, and by
	 * a couple functions in this class.
	 * It takes a column or table name (optionally with an alias) and inserts
	 * the table prefix onto it. Some logic is necessary in order to deal with
	 * column names that include the path. Consider a query like this:
	 *
	 * SELECT hostname.database.table.column AS c FROM hostname.database.table
	 *
	 * Or a query with aliasing:
	 *
	 * SELECT m.member_id, m.member_name FROM members AS m
	 *
	 * Since the column name can include up to four segments (host, DB, table, column)
	 * or also have an alias prefix, we need to do a bit of work to figure this out and
	 * insert the table prefix (if it exists) in the proper position, and escape only
	 * the correct identifiers.
	 *
	 * @todo     Should this move back to the Connection, since we have a link to that now?
	 *
	 * @param    string|array
	 * @param    bool
	 * @param    mixed
	 * @param    bool
	 *
	 * @return    string
	 */
	public function protectIdentifiers($item, $prefixSingle = false, $protectIdentifiers = null, $fieldExists = true)
	{
		if ( ! is_bool($protectIdentifiers))
		{
			$protectIdentifiers = $this->protectIdentifiers;
		}

		if (is_array($item))
		{
			$escaped_array = [];
			foreach ($item as $k => $v)
			{
				$escaped_array[$this->protectIdentifiers($k)] = $this->protectIdentifiers($v, $prefixSingle,
					$protectIdentifiers, $fieldExists);
			}

			return $escaped_array;
		}

		// This is basically a bug fix for queries that use MAX, MIN, etc.
		// If a parenthesis is found we know that we do not need to
		// escape the data or add a prefix. There's probably a more graceful
		// way to deal with this, but I'm not thinking of it -- Rick
		//
		// Added exception for single quotes as well, we don't want to alter
		// literal strings. -- Narf
		if (strcspn($item, "()'") !== strlen($item))
		{
			return $item;
		}

		// Convert tabs or multiple spaces into single spaces
		$item = preg_replace('/\s+/', ' ', trim($item));

		// If the item has an alias declaration we remove it and set it aside.
		// Note: strripos() is used in order to support spaces in table names
		if ($offset = strripos($item, ' AS '))
		{
			$alias = ($protectIdentifiers)
				? substr($item, $offset, 4).$this->escapeIdentifiers(substr($item, $offset + 4))
				: substr($item, $offset);
			$item  = substr($item, 0, $offset);
		}
		elseif ($offset = strrpos($item, ' '))
		{
			$alias = ($protectIdentifiers)
				? ' '.$this->escapeIdentifiers(substr($item, $offset + 1))
				: substr($item, $offset);
			$item  = substr($item, 0, $offset);
		}
		else
		{
			$alias = '';
		}

		// Break the string apart if it contains periods, then insert the table prefix
		// in the correct location, assuming the period doesn't indicate that we're dealing
		// with an alias. While we're at it, we will escape the components
		if (strpos($item, '.') !== false)
		{
			$parts = explode('.', $item);

			// Does the first segment of the exploded item match
			// one of the aliases previously identified? If so,
			// we have nothing more to do other than escape the item
			//
			// NOTE: The ! empty() condition prevents this method
			//       from breaking when QB isn't enabled.
			if ( ! empty($this->qb_aliased_tables) && in_array($parts[0], $this->qb_aliased_tables))
			{
				if ($protectIdentifiers === true)
				{
					foreach ($parts as $key => $val)
					{
						if ( ! in_array($val, $this->reservedIdentifiers))
						{
							$parts[$key] = $this->escapeIdentifiers($val);
						}
					}

					$item = implode('.', $parts);
				}

				return $item.$alias;
			}

			// Is there a table prefix defined in the config file? If not, no need to do anything
			if ($this->dbprefix !== '')
			{
				// We now add the table prefix based on some logic.
				// Do we have 4 segments (hostname.database.table.column)?
				// If so, we add the table prefix to the column name in the 3rd segment.
				if (isset($parts[3]))
				{
					$i = 2;
				}
				// Do we have 3 segments (database.table.column)?
				// If so, we add the table prefix to the column name in 2nd position
				elseif (isset($parts[2]))
				{
					$i = 1;
				}
				// Do we have 2 segments (table.column)?
				// If so, we add the table prefix to the column name in 1st segment
				else
				{
					$i = 0;
				}

				// This flag is set when the supplied $item does not contain a field name.
				// This can happen when this function is being called from a JOIN.
				if ($fieldExists === false)
				{
					$i++;
				}

				// Verify table prefix and replace if necessary
				if ($this->swapPre !== '' && strpos($parts[$i], $this->swapPre) === 0)
				{
					$parts[$i] = preg_replace('/^'.$this->swapPre.'(\S+?)/', $this->dbprefix.'\\1', $parts[$i]);
				}
				// We only add the table prefix if it does not already exist
				elseif (strpos($parts[$i], $this->dbprefix) !== 0)
				{
					$parts[$i] = $this->dbprefix.$parts[$i];
				}

				// Put the parts back together
				$item = implode('.', $parts);
			}

			if ($protectIdentifiers === true)
			{
				$item = $this->escapeIdentifiers($item);
			}

			return $item.$alias;
		}

		// Is there a table prefix? If not, no need to insert it
		if ($this->dbprefix !== '')
		{
			// Verify table prefix and replace if necessary
			if ($this->swapPre !== '' && strpos($item, $this->swapPre) === 0)
			{
				$item = preg_replace('/^'.$this->swapPre.'(\S+?)/', $this->dbprefix.'\\1', $item);
			}
			// Do we prefix an item with no segments?
			elseif ($prefixSingle === true && strpos($item, $this->dbprefix) !== 0)
			{
				$item = $this->dbprefix.$item;
			}
		}

		if ($protectIdentifiers === true && ! in_array($item, $this->reservedIdentifiers))
		{
			$item = $this->escapeIdentifiers($item);
		}

		return $item.$alias;
	}

	//--------------------------------------------------------------------

	/**
	 * Escape the SQL Identifiers
	 *
	 * This function escapes column and table names
	 *
	 * @param    mixed
	 *
	 * @return    mixed
	 */
	public function escapeIdentifiers($item)
	{
		if ($this->escapeChar === '' OR empty($item) OR in_array($item, $this->reservedIdentifiers))
		{
			return $item;
		}
		elseif (is_array($item))
		{
			foreach ($item as $key => $value)
			{
				$item[$key] = $this->escapeIdentifiers($value);
			}

			return $item;
		}
		// Avoid breaking functions and literal values inside queries
		elseif (ctype_digit($item) OR $item[0] === "'" OR ($this->escapeChar !== '"' && $item[0] === '"') OR
		        strpos($item, '(') !== false
		)
		{
			return $item;
		}

		static $preg_ec = [];

		if (empty($preg_ec))
		{
			if (is_array($this->escapeChar))
			{
				$preg_ec = [
					preg_quote($this->escapeChar[0], '/'),
					preg_quote($this->escapeChar[1], '/'),
					$this->escapeChar[0],
					$this->escapeChar[1],
				];
			}
			else
			{
				$preg_ec[0] = $preg_ec[1] = preg_quote($this->escapeChar, '/');
				$preg_ec[2] = $preg_ec[3] = $this->escapeChar;
			}
		}

		foreach ($this->reservedIdentifiers as $id)
		{
			if (strpos($item, '.'.$id) !== false)
			{
				return preg_replace('/'.$preg_ec[0].'?([^'.$preg_ec[1].'\.]+)'.$preg_ec[1].'?\./i',
					$preg_ec[2].'$1'.$preg_ec[3].'.', $item);
			}
		}

		return preg_replace('/'.$preg_ec[0].'?([^'.$preg_ec[1].'\.]+)'.$preg_ec[1].'?(\.)?/i',
			$preg_ec[2].'$1'.$preg_ec[3].'$2', $item);
	}

	//--------------------------------------------------------------------

	/**
	 * DB Prefix
	 *
	 * Prepends a database prefix if one exists in configuration
	 *
	 * @param    string    the table
	 *
	 * @return    string
	 */
	public function prefixTable($table = '')
	{
		if ($table === '')
		{
			$this->display_error('db_table_name_required');
		}

		return $this->dbprefix.$table;
	}

	//--------------------------------------------------------------------

	/**
	 * Set DB Prefix
	 *
	 * Set's the DB Prefix to something new without needing to reconnect
	 *
	 * @param    string    the prefix
	 *
	 * @return    string
	 */
	public function setPrefix($prefix = '')
	{
		return $this->dbprefix = $prefix;
	}

	//--------------------------------------------------------------------
	
	/**
	 * Returns the total number of rows affected by this query.
	 *
	 * @return mixed
	 */
	abstract public function affectedRows(): int;

	//--------------------------------------------------------------------



}
