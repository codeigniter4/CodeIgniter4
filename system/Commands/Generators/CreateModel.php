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
 * Creates a skeleton Model file.
 */
class CreateModel extends GeneratorCommand
{
	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name = 'make:model';

	/**
	 * The Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Creates a new model file.';

	/**
	 * The Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'make:model <name> [options]';

	/**
	 * The Command's arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The model class name',
	];

	/**
	 * The Command's options
	 *
	 * @var array
	 */
	protected $options = [
		'--dbgroup' => 'Database group to use. Defaults to "default".',
		'--entity'  => 'Use an Entity as return type.',
		'--table'   => 'Supply a different table name. Defaults to the pluralized name.',
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
		return $rootNamespace . '\\Models\\' . $class;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getTemplate(): string
	{
		$dbgroup = $this->params['dbgroup'] ?? CLI::getOption('dbgroup');

		if (! is_string($dbgroup))
		{
			$dbgroup = 'default';
		}

		$template = $this->getGeneratorViewFile('CodeIgniter\\Commands\\Generators\\Views\\model.tpl.php');

		return str_replace(['<@php', '{dbgroup}'], ['<?php', $dbgroup], $template);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setReplacements(string $template, string $class): string
	{
		$template = parent::setReplacements($template, $class);
		$entity   = array_key_exists('entity', $this->params) || CLI::getOption('entity');

		if (! $entity)
		{
			$entity = 'array'; // default to array return
		}
		else
		{
			$entity = str_replace('\\Models', '\\Entities', $class);

			if ($pos = strripos($entity, 'Model'))
			{
				// Strip 'Model' from name
				$entity = substr($entity, 0, $pos);
			}
		}

		$template = str_replace('{return}', $entity, $template);
		$table    = $this->params['table'] ?? CLI::getOption('table');

		if (! is_string($table))
		{
			$table = str_replace($this->getNamespace($class) . '\\', '', $class);
		}

		if ($pos = strripos($table, 'Model'))
		{
			$table = substr($table, 0, $pos);
		}

		// transform class name to lowercased plural for table name
		$table = strtolower(plural($table));

		return str_replace('{table}', $table, $template);
	}
}
