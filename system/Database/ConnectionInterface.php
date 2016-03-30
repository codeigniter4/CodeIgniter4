<?php namespace CodeIgniter\Database;

interface ConnectionInterface
{
	/**
	 * Initializes the database connection/settings.
	 *
	 * @return mixed
	 */
	public function initialize();

	//--------------------------------------------------------------------

	/**
	 * Connect to the database.
	 *
	 * @return mixed
	 */
	public function connect($persistant = false);

	//--------------------------------------------------------------------

	/**
	 * Create a persistant database connection.
	 *
	 * @return mixed
	 */
	public function persistantConnect();

	//--------------------------------------------------------------------

	/**
	 * Keep or establish the connection if no queries have been sent for
	 * a length of time exceeding the server's idle timeout.
	 *
	 * @return mixed
	 */
	public function reconnect();

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
	public function getConnection(string $alias=null);

	//--------------------------------------------------------------------

	/**
	 * Select a specific database table to use.
	 *
	 * @param string $databaseName
	 *
	 * @return mixed
	 */
	public function setDatabase(string $databaseName);

	//--------------------------------------------------------------------

	/**
	 * Returns the name of the current database being used.
	 *
	 * @return string
	 */
	public function getDatabase(): string;

	//--------------------------------------------------------------------

	/**
	 * Returns the last error encountered by this connection.
	 *
	 * @return mixed
	 */
	public function getError();

	//--------------------------------------------------------------------

	/**
	 * The name of the platform in use (mysqli, mssql, etc)
	 *
	 * @return mixed
	 */
	public function getPlatform();

	//--------------------------------------------------------------------

	/**
	 * Returns a string containing the version of the database being used.
	 *
	 * @return mixed
	 */
	public function getVersion();

	//--------------------------------------------------------------------

	/**
	 * Specifies whether this connection should keep queries objects or not.
	 *
	 * @param bool $doLog
	 */
	public function saveQueries($doLog = false);

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
	public function query(string $sql, $binds = null);

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
	public function simpleQuery(string $sql);

	//--------------------------------------------------------------------

	/**
	 * Returns an instance of the query builder for this connection.
	 *
	 * @param string $tableName
	 *
	 * @return QueryBuilder
	 */
	public function table(string $tableName);

	//--------------------------------------------------------------------

	/**
	 * Returns an array containing all of the
	 *
	 * @return array
	 */
	public function getQueries(): array;

	//--------------------------------------------------------------------

	/**
	 * Returns the total number of queries that have been performed
	 * on this connection.
	 *
	 * @return mixed
	 */
	public function getQueryCount();

	//--------------------------------------------------------------------

	/**
	 * Returns the last query's statement object.
	 *
	 * @return mixed
	 */
	public function getLastQuery();

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
	public function escape($str);

	//--------------------------------------------------------------------

}
