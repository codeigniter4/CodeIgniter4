<?php namespace CodeIgniter\Commands\Cache;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ClearCache extends BaseCommand
{
	/**
	 * Command grouping.
	 *
	 * @var string
	 */
	protected $group = 'Cache';

	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name = 'cache:clear';

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Clears the current system caches.';

	/**
	 * the Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'cache:clear [driver]';

	/**
	 * the Command's Arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'driver' => 'The cache driver to use',
	];

	/**
	 * Creates a new migration file with the current timestamp.
	 *
	 * @param array $params
	 */
	public function run(array $params = [])
	{
		dd($params);
	}
}
