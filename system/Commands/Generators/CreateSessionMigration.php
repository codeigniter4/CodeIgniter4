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

use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\GeneratorCommand;

/**
 * Creates a migration file for database sessions.
 */
class CreateSessionMigration extends GeneratorCommand
{
	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name = 'session:migration';

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Generates the migration file for database sessions.';

	/**
	 * The Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'session:migration [options]';

	/**
	 * The Command's Options
	 *
	 * @var array
	 */
	protected $options = [
		'-g' => 'Set database group',
		'-t' => 'Set table name',
	];

	/**
	 * {@inheritDoc}
	 */
	protected function getClassName(): string
	{
		$tableName = $this->params['t'] ?? CLI::getOption('t') ?? 'ci_sessions';

		return "Migration_create_{$tableName}_table";
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getNamespacedClass(string $rootNamespace, string $class): string
	{
		return $rootNamespace . '\\Database\\Migrations\\' . $class;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function modifyBasename(string $filename): string
	{
		return str_replace('Migration', gmdate(config('Migrations')->timestampFormat), $filename);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getTemplate(): string
	{
		$data = [
			'DBGroup'   => $this->params['g'] ?? CLI::getOption('g'),
			'tableName' => $this->params['t'] ?? CLI::getOption('t') ?? 'ci_sessions',
			'matchIP'   => config('App')->sessionMatchIP ?? false,
		];

		$template = $this->getGeneratorViewFile('CodeIgniter\\Commands\\Generators\\Views\\session_migration.tpl.php', $data);

		return str_replace('<@php', '<?php', $template);
	}
}
