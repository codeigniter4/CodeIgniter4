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

class CreateScaffold extends BaseCommand
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
	protected $name = 'make:scaffold';

	/**
	 * The Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Creates a complete set of scaffold files.';

	/**
	 * The Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'make:scaffold <name> [options]';

	/**
	 * The Command's arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The class name',
	];

	/**
	 * The Command's options.
	 *
	 * @var array
	 */
	protected $options = [
		'--bare'    => 'Add the \'-bare\' option to controller scaffold.',
		'--restful' => 'Add the \'-restful\' option to controller scaffold.',
		'--dbgroup' => 'Add the \'-dbgroup\' option to model scaffold.',
		'--table'   => 'Add the \'-table\' option to the model scaffold.',
		'-n'        => 'Set root namespace. Defaults to APP_NAMESPACE.',
		'--force'   => 'Force overwrite existing files.',
	];

	/**
	 * {@inheritDoc}
	 */
	public function run(array $params)
	{
		// Resolve options
		$bare       = array_key_exists('bare', $params) || CLI::getOption('bare');
		$rest       = array_key_exists('restful', $params) ? ($params['restful'] ?? true) : CLI::getOption('restful');
		$group      = array_key_exists('dbgroup', $params) ? ($params['dbgroup'] ?? 'default') : CLI::getOption('dbgroup');
		$tableModel = $params['table'] ?? CLI::getOption('table');
		$namespace  = $params['n'] ?? CLI::getOption('n');
		$force      = array_key_exists('force', $params) || CLI::getOption('force');

		// Sets additional options
		$genOptions = ['n' => $namespace];

		if ($force)
		{
			$genOptions['force'] = null;
		}

		$controllerOpts = [];

		if ($bare)
		{
			$controllerOpts['bare'] = null;
		}
		elseif ($rest)
		{
			$controllerOpts['restful'] = $rest;
		}

		$modelOpts = [
			'dbgroup' => $group,
			'entity'  => null,
			'table'   => $tableModel,
		];

		// Call those commands!
		$class = $params[0] ?? CLI::getSegment(2);
		$this->call('make:controller', array_merge([$class], $controllerOpts, $genOptions));
		$this->call('make:model', array_merge([$class], $modelOpts, $genOptions));
		$this->call('make:entity', array_merge([$class], $genOptions));
		$this->call('make:migration', array_merge([$class], $genOptions));
		$this->call('make:seeder', array_merge([$class], $genOptions));
	}
}
