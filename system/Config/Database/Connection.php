<?php namespace CodeIgniter\Config\Database;

/**
 * Database Connection configuration class.
 *
 * A base class for database connection configuration.
 *
 * This class is intended to be a base class for other system-level configuration
 * classes, not for application-level database connection configuration. Applications
 * should extend the platform-specific system-level connection configuration class
 * for their database.
 */
abstract class Connection extends \CodeIgniter\Config\BaseConfig
{
	/** @var bool Enable database result caching. */
	public $cacheEnabled = false;

	/** @var string The character set. */
	public $characterSet = 'utf8';

	/** @var string The name of the database. */
	public $database = '';

	/** @var bool Enable debug messages. */
	public $debugEnabled = false;

	/**
	 * Data Source Name.
	 *
	 * A string which will usually contain all of the settings required to connect
	 * to a database. Commonly used for database adapters which support multiple
	 * database types, like PDO or ODBC.
	 *
	 * @var string
	 */
	public $dsn = '';

	/** @var array List of connections to use if this one fails to connect. */
	public $failover = [];

	/** @var string The database server's hostname. */
	public $hostname = '';

	/** @var string The password for the database connection. */
	public $password = '';

	/** @var bool Use a persistent connection. */
	public $persistentConnectionEnabled = false;

	/**
	 * The port used to connect to the database server.
	 *
	 * This is usually not set when the server is configured to use the default
	 * port.
	 *
	 * @var int
	 */
	public $port;

	/**
	 * Whether to "save" all executed SQL statements.
	 *
	 * Disabling this will also effectively disable application-level profiling
	 * of SQL statements.
	 *
	 * Enabling this setting may cause high memory use, especially when running
	 * a lot of SQL statements.
	 *
	 * @var bool
	 */
	public $saveStatementsEnabled = false;

	/** @var bool Forces "Strict Mode" connections to enforce strict SQL. */
	public $strictSQLEnabled = false;

	/** @var string The username for the database connection. */
	public $username = '';

	/**
	 * The name of the adapter to be used by this connection.
	 * In most cases, this will match the name of the Connection class itself.
	 *
	 * This is intended to be set by the system-level platform-specific configuration
	 * class, and should not be modified by the application.
	 *
	 * @var string
	 */
	protected $adapter;

	/**
	 * Get the name of the adapter to be used by the connection.
	 *
	 * @return string The name of the adapter.
	 */
	public function getAdapter()
	{
		return $this->adapter;
	}
}
