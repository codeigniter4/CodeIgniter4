<?php namespace CodeIgniter\Config\Database\Connection;

/**
 * This is the base class to be used when configuring a connection to a MySQL database
 * using PHP's MySQLi extension. The application's database connection configuration
 * class should extend this class and configure the appropriate values to connect
 * to the database.
 */
class MySQLi extends \CodeIgniter\Config\Database\Connection
{
	/** @var string The character collation used in communicating with the database. */
	public $collation = 'utf8_general_ci';

	/** @var bool Whether client compression is enabled. */
	public $compressionEnabled = false;

	/**
	 * Whether to use the MySQL "delete hack" which allows the number of affected
	 * rows to be shown.
	 *
	 * Uses a preg_replace when enabled, adding a little more processing to all
	 * queries.
	 *
	 * @var bool
	 */
	public $deleteHack = true;

	/** @var string Path to the private key file for encryption. */
	public $sslKey;

	/** @var string Path to the public key certificate file for encryption. */
	public $sslCert;

	/** @var string Path to the certificate authority file for encryption. */
	public $sslCA;

	/**
	 * Path to a directory containing trusted CA certificates in PEM format.
	 *
	 * @var string
	 */
	public $sslCAPath;

	/**
	 * List of *allowed* ciphers to be used for encryption, separated by colons
	 * (':').
	 *
	 * @var string
	 */
	public $sslCipher;

	/** @var bool Whether to verify the server certificate. */
	public $sslVerify;

	/**
	 * The name of the adapter to be used by this connection.
	 * In most cases, this will match the name of the Connection class itself.
	 *
	 * This should not be modified/overridden by the application.
	 *
	 * @var string
	 */
	protected $adapter = 'MySQLi';
}
