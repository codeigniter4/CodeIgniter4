<?php namespace CodeIgniter\Commands\Cache;

use CodeIgniter\Cache\CacheFactory;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\I18n\Time;

class InfoCache extends BaseCommand
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
	protected $name = 'cache:info';

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Show info cache in the current system.';

	/**
	 * the Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'cache:info [driver]';

	/**
	 * the Command's Arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'driver' => 'The cache driver to use',
	];

	/**
	 * Clears the cache
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$config = config('Cache');
		helper('number');

		$handler = $params[0] ?? $config->handler;
		if (! array_key_exists($handler, $config->validHandlers))
		{
			CLI::error($handler . ' is not a valid cache handler.');
			return;
		}

		$config->handler = $handler;
		$cache           = CacheFactory::getHandler($config);

		$caches = $cache->getCacheInfo();

		$tbody = [];

		foreach ($caches as $key => $field)
		{
			$tbody[] = [
				$key,
				$field['server_path'],
				number_to_size($field['size']),
				Time::createFromTimestamp($field['date']),
			];
		}

		$thead = [
			CLI::color('Name', 'green'),
			CLI::color('Server Path', 'green'),
			CLI::color('Size', 'green'),
			CLI::color('Date', 'green'),
		];

		CLI::table($tbody, $thead);
	}
}
