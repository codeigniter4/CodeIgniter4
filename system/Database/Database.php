<?php namespace CodeIgniter\Database;

use Zend\Escaper\Exception\InvalidArgumentException;

/**
 * Database Connection Factory
 *
 * Creates and returns an instance of the appropriate DatabaseConnection
 *
 * @package CodeIgniter\Database
 */
class Database
{
	/**
	 * Maintains an array of the instances of all connections
	 * that have been created. Helps to keep track of all open
	 * connections for performance monitoring, logging, etc.
	 *
	 * @var array
	 */
	protected $connections = [];

	//--------------------------------------------------------------------

	/**
	 * Parses the connection binds and returns an instance of
	 * the driver ready to go.
	 *
	 * @param array $params
	 * @param bool  $useBuilder
	 */
	public function load(array $params = [], string $alias, $useBuilder = false)
	{
		// No DB specified? Beat them senseless...
		if (empty($params['dbdriver']))
		{
			throw new InvalidArgumentException('You have not selected a database type to connect to.');
		}
		
		$className = strpos($params['dbdriver'], '\\') === false
			? '\CodeIgniter\Database\\'.$params['dbdriver'].'\\Connection'
			: $params['dbdriver'].'\\Connection';

		$class = new $className($params);

		// Store the connection
		$this->connections[$alias] = $class;

		return $this->connections[$alias];
	}

	//--------------------------------------------------------------------

}
