<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Database;

/**
 * Interface ConnectionInterface
 */
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
	 * @param  boolean $persistent
	 * @return mixed
	 */
	public function connect(bool $persistent = false);

	//--------------------------------------------------------------------

	/**
	 * Create a persistent database connection.
	 *
	 * @return mixed
	 */
	public function persistentConnect();

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
	public function getConnection(string $alias = null);

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
	 * Must return this format: ['code' => string|int, 'message' => string]
	 * intval(code) === 0 means "no error".
	 *
	 * @return array<string,string|int>
	 */
	public function error(): array;

	//--------------------------------------------------------------------

	/**
	 * The name of the platform in use (MySQLi, mssql, etc)
	 *
	 * @return string
	 */
	public function getPlatform(): string;

	//--------------------------------------------------------------------

	/**
	 * Returns a string containing the version of the database being used.
	 *
	 * @return string
	 */
	public function getVersion(): string;

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
	 * @param mixed  ...$binds
	 *
	 * @return BaseResult|Query|boolean
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
	 * @param string|array $tableName Table name.
	 *
	 * @return BaseBuilder Builder.
	 */
	public function table($tableName);

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
	 * @param mixed $str
	 *
	 * @return mixed
	 */
	public function escape($str);

	//--------------------------------------------------------------------

	/**
	 * Allows for custom calls to the database engine that are not
	 * supported through our database layer.
	 *
	 * @param string $functionName
	 * @param array  ...$params
	 *
	 * @return mixed
	 */
	public function callFunction(string $functionName, ...$params);

	//--------------------------------------------------------------------

	/**
	 * Determines if the statement is a write-type query or not.
	 *
	 * @param  string $sql
	 * @return boolean
	 */
	public function isWriteType($sql): bool;
}
