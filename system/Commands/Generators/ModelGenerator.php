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
use CodeIgniter\CLI\GeneratorTrait;

/**
 * Generates a skeleton Model file.
 */
class ModelGenerator extends BaseCommand
{
	use GeneratorTrait;

	/**
	 * The Command's Group
	 *
	 * @var string
	 */
	protected $group = 'Generators';

	/**
	 * The Command's Name
	 *
	 * @var string
	 */
	protected $name = 'make:model';

	/**
	 * The Command's Description
	 *
	 * @var string
	 */
	protected $description = 'Generates a new model file.';

	/**
	 * The Command's Usage
	 *
	 * @var string
	 */
	protected $usage = 'make:model <name> [options]';

	/**
	 * The Command's Arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The model class name.',
	];

	/**
	 * The Command's Options
	 *
	 * @var array
	 */
	protected $options = [
		'--table'     => 'Supply a table name. Default: "the lowercased plural of the class name".',
		'--dbgroup'   => 'Database group to use. Default: "default".',
		'--return'    => 'Return type, Options: [array, object, entity]. Default: "array".',
		'--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
		'--suffix'    => 'Append the component title to the class name (e.g. User => UserModel).',
		'--force'     => 'Force overwrite existing file.',
	];

	/**
	 * Actually execute a command.
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$this->component = 'Model';
		$this->directory = 'Models';
		$this->template  = 'model.tpl.php';

		$this->execute($params);
	}

	/**
	 * Prepare options and do the necessary replacements.
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	protected function prepare(string $class): string
	{
		$table   = $this->getOption('table');
		$DBGroup = $this->getOption('dbgroup');
		$return  = $this->getOption('return');

		$baseClass = strtolower(str_replace(trim(implode('\\', array_slice(explode('\\', $class), 0, -1)), '\\') . '\\', '', $class));
		$baseClass = strpos($baseClass, 'model') ? str_replace('model', '', $baseClass) : $baseClass;

		$table   = is_string($table) ? $table : plural($baseClass);
		$DBGroup = is_string($DBGroup) ? $DBGroup : 'default';
		$return  = is_string($return) ? $return : 'array';

		if (! in_array($return, ['array', 'object', 'entity'], true))
		{
			// @codeCoverageIgnoreStart
			$return = CLI::prompt(lang('CLI.generator.returnType'), ['array', 'object', 'entity'], 'required');
			CLI::newLine();
			// @codeCoverageIgnoreEnd
		}

		if ($return === 'entity')
		{
			$return = str_replace('Models', 'Entities', $class);

			if ($pos = strpos($return, 'Model'))
			{
				$return = substr($return, 0, $pos);

				if ($this->getOption('suffix'))
				{
					$return .= 'Entity';
				}
			}

			$this->call('make:entity', array_merge([$baseClass], $this->params));
		}

		return $this->parseTemplate($class, ['{table}', '{DBGroup}', '{return}'], [$table, $DBGroup, $return]);
	}
}
