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
 * Creates a skeleton Filter file.
 */
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

	/**
	 * {@inheritDoc}
	 */
	protected function getClassName(): string
	{
		$className = parent::getClassName();

		if (empty($className))
		{
			$className = CLI::prompt(lang('CLI.generateClassName'), null, 'required'); // @codeCoverageIgnore
		}

		return $className;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getNamespacedClass(string $rootNamespace, string $class): string
	{
		return $rootNamespace . '\\Filters\\' . $class;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getTemplate(): string
	{
		$template = $this->getGeneratorViewFile('CodeIgniter\\Commands\\Generators\\Views\\filter.tpl.php');

		return str_replace('<@php', '<?php', $template);
	}
}
