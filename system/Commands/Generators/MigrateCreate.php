<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Commands\Generators;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Deprecated class for the migration creation command.
 *
 * @deprecated Use make:command instead.
 *
 * @codeCoverageIgnore
 */
class MigrateCreate extends BaseCommand
{
	/**
	 * The group the command is lumped under
	 * when listing commands.
	 *
	 * @var string
	 */
	protected $group = 'Generators';

	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name = 'migrate:create';

	/**
	 * The Command's short description
	 *
	 * @var string
	 */
	protected $description = '[DEPRECATED] Creates a new migration file. Please use "make:migration" instead.';

	/**
	 * The Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'migrate:create <name> [options]';

	/**
	 * The Command's arguments.
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The migration file name.',
	];

	/**
	 * The Command's options.
	 *
	 * @var array
	 */
	protected $options = [
		'-n'      => 'Set root namespace. Defaults to APP_NAMESPACE',
		'--force' => 'Force overwrite existing files.',
	];

	/**
	 * Actually execute a command.
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		// Resolve arguments before passing to make:migration
		$params[0] = $params[0] ?? CLI::getSegment(2);

		$params['namespace'] = $params['namespace'] ?? CLI::getOption('namespace') ?? APP_NAMESPACE;

		if (array_key_exists('force', $params) || CLI::getOption('force'))
		{
			$params['force'] = null;
		}

		$this->call('make:migration', $params);
	}
}
