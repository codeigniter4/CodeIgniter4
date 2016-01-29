<?php namespace CodeIgniter\Config;

/**
 * Database configuration class.
 *
 * A base class for application-level database configuration. Allows the application
 * to set a list of available connection configurations and the active/default connection
 * configuration to be used when no connection configuration is specified.
 */
class Database extends \CodeIgniter\Config\BaseConfig
{
	/** @var string The name of the active connection. */
	public $activeConnection;

	/** @var array Connection names and the classes those names reference. */
	public $availableConnections = [];

	/**
	 * Allows a Database configuration to be built from a parameter array at run-time.
	 *
	 * @param array $params Property name/value pairs to set in the database config.
	 */
	public function __construct($params = [])
	{
		parent::__construct();

		// Allow $params to override environment variables.
		if (isset($params['availableConnections']))
		{
			$this->availableConnections = $params['availableConnections'];
		}
		if (isset($params['activeConnection']))
		{
			$this->activeConnection = $params['activeConnection'];
		}
	}
}
