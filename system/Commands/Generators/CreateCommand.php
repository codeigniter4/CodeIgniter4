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

class CreateCommand extends GeneratorCommand
{
	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name = 'make:command';

	/**
	 * The Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Creates a new spark command.';

	/**
	 * The Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'make:command <name> [options]';

	/**
	 * The Command's arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The command class name',
	];

	/**
	 * The Command's options
	 *
	 * @var array
	 */
	protected $options = [
		'--command' => 'The command name. Defaults to "command:name"',
		'--group'   => 'The group of command. Defaults to "CodeIgniter" for basic commands, and "Generators" for generator commands.',
		'--type'    => 'Type of command. Whether a basic command or a generator command. Defaults to "basic".',
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
		return $rootNamespace . '\\Commands\\' . $class;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getTemplate(): string
	{
		$template = $this->getGeneratorViewFile('CodeIgniter\\Commands\\Generators\\Views\\command.tpl.php');

		return str_replace('<@php', '<?php', $template);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setReplacements(string $template, string $class): string
	{
		// Get options
		$commandName  = $this->params['command'] ?? CLI::getOption('command');
		$commandGroup = $this->params['group'] ?? CLI::getOption('group');
		$commandType  = $this->params['type'] ?? CLI::getOption('type');

		// Resolve options
		if (! is_string($commandName))
		{
			$commandName = 'command:name';
		}

		if (! is_string($commandType))
		{
			$commandType = 'basic';
		}
		// @codeCoverageIgnoreStart
		elseif (! in_array($commandType, ['basic', 'generator'], true))
		{
			$commandType = CLI::prompt('Command type', ['basic', 'generator'], 'required');
		}
		// @codeCoverageIgnoreEnd

		if ($commandType === 'generator')
		{
			$useStatement = 'use CodeIgniter\\CLI\\GeneratorCommand;';
			$extends      = 'extends GeneratorCommand';
		}
		else
		{
			$useStatement = 'use CodeIgniter\\CLI\\BaseCommand;';
			$extends      = 'extends BaseCommand';
		}

		if (! is_string($commandGroup))
		{
			$commandGroup = $commandType === 'generator' ? 'Generators' : 'CodeIgniter';
		}
		$commandGroup = $this->getCommandGroupProperty($commandType, $commandGroup);

		$commandAbstractMethodsToImplement = $this->getRequiredAbstractMethodsToImplement($commandType);

		// Do the replacements
		$template = parent::setReplacements($template, $class);

		return str_replace([
			'{useStatement}',
			'{extends}',
			'{commandGroup}',
			'{commandName}',
			'{commandAbstractMethodsToImplement}',
		], [
			$useStatement,
			$extends,
			$commandGroup,
			$commandName,
			$commandAbstractMethodsToImplement,
		],
			$template
		);
	}

	/**
	 * Gets the proper class property for group name.
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	protected function getCommandGroupProperty(string $type, string $group): string
	{
		if ($type === 'generator' && $group === 'Generators')
		{
			// Assume it is already extending from Generator Command
			// so this is really redundant.
			return '';
		}

		return <<<EOF

	/**
	 * The group the command is lumped under
	 * when listing commands.
	 *
	 * @var string
	 */
	protected \$group = '{$group}';

EOF;
	}

	/**
	 * Gets the required abstract methods.
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	protected function getRequiredAbstractMethodsToImplement(string $type): string
	{
		if ($type !== 'generator')
		{
			return <<<'EOF'

	/**
	 * Actually execute a command.
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		//
	}

EOF;
		}

		return <<<'EOF'

	protected function getClassName(): string
	{
		// If the class name is required you need to have this.
		// Otherwise, you can safely remove this method.

		$className = parent::getClassName();

		if (empty($className))
		{
			$className = CLI::prompt(lang('CLI.generateClassName'), null, 'required');
		}

		return $className;
	}

	protected function getNamespacedClass(string $rootNamespace, string $class): string
	{
		return '';
	}

	protected function getTemplate(): string
	{
		return '';
	}

EOF;
	}
}
