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
 * Creates a new migration file.
 */
class CreateMigration extends GeneratorCommand
{
	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name = 'make:migration';

	/**
	 * The Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Creates a new migration file.';

	/**
	 * The Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'make:migration <name> [options]';

	/**
	 * The Command's Arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The migration file name',
	];

	/**
	 * {@inheritDoc}
	 */
	protected function getClassName(): string
	{
		$class = parent::getClassName();

		if (empty($class))
		{
			$class = CLI::prompt(lang('Migrations.nameMigration'), null, 'required'); // @codeCoverageIgnore
		}

		return $class;
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
		return gmdate(config('Migrations')->timestampFormat) . $filename;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getTemplate(): string
	{
		$template = $this->getGeneratorViewFile('CodeIgniter\\Commands\\Generators\\Views\\migration.tpl.php');

		return str_replace('<@php', '<?php', $template);
	}
}
