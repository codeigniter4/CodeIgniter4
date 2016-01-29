<?php namespace App\Config\Database;

/**
 * The default connection configuration for the application.
 *
 * This file will contain the settings needed to access your database.
 *
 * Mapping values from CI3 database configuration:
 * $active_group  - not set here, see $activeConnection in the application's Database config
 * $query_builder - currently not supported
 * $db            - set by a combination of $availableConnections in the application's
 *                  Database config and files like this one to set the configuration
 *                  of the individual connections.
 *
 * The following are the keys used in configuring connection groups in CI3's $db
 * property and their equivalent properties in CI4's Database Connection config:
 * 'dsn'          - $dsn
 * 'hostname'     - $hostname
 * 'username'     - $username
 * 'password'     - $password
 * 'dbdriver'     - Instead of setting this value directly, extend the platform-specific
 *                  Connection Configuration class (to be added here when available):
 *      'mysqli' - \CodeIgniter\Config\Database\Connection\MySQLi
 * 'dbprefix'     - currently not supported
 * 'pconnect'     - $persistentConnectionEnabled
 * 'db_debug'     - $debugEnabled
 * 'cache_on'     - $cacheEnabled - may be modified to reference the cache configuration
 * 'cachedir'     - currently not supported (to be configured elsewhere)
 * 'char_set'     - $characterSet
 * 'dbcollat'     - $collation (MySQLi-only)
 * 'swap_pre'     - currently not supported
 * 'encrypt'      - (MySQLi-only) instead of an array with the following options, set
 *                  the associated properties directly:
 *      'ssl_key'    - $sslKey
 *      'ssl_cert'   - $sslCert
 *      'ssl_ca'     - $sslCA
 *      'ssl_capath' - $sslCAPath
 *      'ssl_cipher' - $sslCipher
 *      'ssl_verify' - $sslVerify
 * 'compress'     - $compressionEnabled (MySQLi-only)
 * 'stricton'     - $strictSQLEnabled
 * 'failover'     - $failover Instead of an array containing connection configurations,
 *                  this will contain values matching keys used in the application's
 *                  database configuration's $availableConnections property
 * 'save_queries' - $saveStatementsEnabled
 * 'port'         - $port
 *
 * Additional configuration options:
 * $deleteHack    - (MySQLi-only)
 *
 * The order of the properties below has been arranged (and their values have been
 * set) to match the default connection group in CI3's database config.
 */
class DefaultConnection extends \CodeIgniter\Config\Database\Connection\MySQLi
{
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

	/** @var string The database server's hostname. */
	public $hostname = 'localhost';

	/** @var string The username for the database connection. */
	public $username = '';

	/** @var string The password for the database connection. */
	public $password = '';

	/** @var string The name of the database. */
	public $database = '';

	/** @var bool Use a persistent connection. */
	public $persistentConnectionEnabled = false;

	/** @var bool Enable debug messages. */
	public $debugEnabled = (ENVIRONMENT !== 'production');

	/** @var bool Enable database result caching. */
	public $cacheEnabled = false;

	/** @var string The character set. */
	public $characterSet = 'utf8';

	/** @var string The character collation used in communicating with the database. */
	public $collation = 'utf8_general_ci';

	/** @var bool Whether client compression is enabled. */
	public $compressionEnabled = false;

	/** @var bool Forces "Strict Mode" connections to enforce strict SQL. */
	public $strictSQLEnabled = false;

	/**
	 * List of connections to use if this one fails to connect.
	 *
	 * The values in this array should match the keys used in the application's
	 * Database config to reference other connection configuration classes.
	 *
	 * @var array
	 */
	public $failover = [];

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
	public $saveStatementsEnabled = true;
}
