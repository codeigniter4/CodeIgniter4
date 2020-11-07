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
 * Creates a new seeder file
 */
class CreateSeeder extends GeneratorCommand
{
	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name = 'make:seeder';

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Creates a new seeder file.';

	/**
	 * the Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'make:seeder <name> [options]';

	/**
	 * the Command's Arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The seeder file name',
	];

	/**
	 * Gets the class name from input.
	 *
	 * @return string
	 */
	protected function getClassName(): string
	{
		$class = parent::getClassName();

		if (empty($class))
		{
			$class = CLI::prompt(lang('Migrations.nameSeeder'), null, 'required'); // @codeCoverageIgnore
		}

		return $class;
	}

	/**
	 * Gets the qualified class name.
	 *
	 * @param string $rootNamespace
	 * @param string $class
	 *
	 * @return string
	 */
	protected function getNamespacedClass(string $rootNamespace, string $class): string
	{
		return $rootNamespace . '\\Database\\Seeds\\' . $class;
	}

	/**
	 * Gets the template for this class.
	 *
	 * @return string
	 */
	protected function getTemplate(): string
	{
		$template = $this->getGeneratorViewFile('CodeIgniter\\Commands\\Generators\\Views\\seed.tpl.php');

		return str_replace('<@php', '<?php', $template);
	}
}
