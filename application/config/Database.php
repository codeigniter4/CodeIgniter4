<?php namespace App\Config;

/**
 * Database configuration class.
 *
 * The default application-level database configuration. Allows the application
 * to set a list of available connection configurations and the active/default connection
 * configuration to be used when no connection configuration is specified.
 */
class Database extends \CodeIgniter\Config\Database
{
	/**
	 * The name of the active connection configuration.
	 *
	 * This value should match a key in $availableConnections to tell the system
	 * which connection configuration to use when no connection configuration is
	 * specified when connecting to the database/loading the database library.
	 *
	 * @var string
	 */
	public $activeConnection = 'default';

	/**
	 * The connection configurations available to this application.
	 *
	 * An array of connection configuration classes with keys specifying the name
	 * which will be used to reference the class in the application and configuration
	 * files.
	 *
	 * @var array
	 */
	public $availableConnections = [
		'default' => "\\App\\Config\\Database\\DefaultConnection",
	];
}
