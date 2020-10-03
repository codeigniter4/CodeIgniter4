<?php namespace CodeIgniter\Database;

use CodeIgniter\Config\Factories;
use Config\Services;

/**
 * Returns new or shared Model instances
 *
 * @deprecated Use CodeIgniter\Config\Factories::models()
 */
class ModelFactory
{
	/**
	 * Creates new Model instances or returns a shared instance
	 *
	 * @param string              $name       Model name, namespace optional
	 * @param boolean             $getShared  Use shared instance
	 * @param ConnectionInterface $connection
	 *
	 * @return mixed|null
	 */
	public static function get(string $name, bool $getShared = true, ConnectionInterface $connection = null)
	{
		return Factories::models($name, $getShared, $connection);
	}

	/**
	 * Helper method for injecting mock instances while testing.
	 *
	 * @param string $name
	 * @param object $instance
	 */
	public static function injectMock(string $name, $instance)
	{
		Factories::injectMock('models', $name, $instance);
	}

	/**
	 * Resets the static arrays
	 */
	public static function reset()
	{
		Factories::reset('models');
	}
}
