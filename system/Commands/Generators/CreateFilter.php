<?php

namespace CodeIgniter\Commands\Generators;

use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\GeneratorCommand;

class CreateFilter extends GeneratorCommand
{
	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name = 'make:filter';

	/**
	 * The Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Creates a new filter file.';

	/**
	 * The Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'make:filter <name> [options]';

	/**
	 * The Command's arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The filter class name',
	];

	protected function getClassName(): string
	{
		$className = parent::getClassName();

		if (empty($className))
		{
			$className = CLI::prompt(lang('CLI.generateClassName'), null, 'required'); // @codeCoverageIgnore
		}

		return $className;
	}

	protected function getNamespacedClass(string $rootNamespace, string $class): string
	{
		return $rootNamespace . '\\Filters\\' . $class;
	}

	protected function getTemplate(): string
	{
		$template = view('CodeIgniter\\Commands\\Generators\\Views\\filter.tpl.php', [], ['debug' => false]);

		return str_replace('<@php', '<?php', $template);
	}
}
