<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * CI Help command for the spark script.
 *
 * Lists the basic usage information for the spark script,
 * and provides a way to list help for other commands.
 */
class ListCommands extends BaseCommand
{
	/**
	 * The group the command is lumped under
	 * when listing commands.
	 *
	 * @var string
	 */
	protected $group = 'CodeIgniter';

	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name = 'list';

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Lists the available commands.';

	/**
	 * the Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'list';

	/**
	 * the Command's Arguments
	 *
	 * @var array
	 */
	protected $arguments = [];

	/**
	 * the Command's Options
	 *
	 * @var array
	 */
	protected $options = [
		'--simple' => 'Prints a list of the commands with no other info',
	];

	//--------------------------------------------------------------------

	/**
	 * Displays the help for the spark cli script itself.
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$commands = $this->commands->getCommands();
		ksort($commands);

		// Check for 'simple' format
		return array_key_exists('simple', $params) || CLI::getOption('simple')
			? $this->listSimple($commands)
			: $this->listFull($commands);
	}

	/**
	 * Lists the commands with accompanying info.
	 *
	 * @param array $commands
	 */
	protected function listFull(array $commands)
	{
		// Sort into buckets by group
		$groups = [];

		foreach ($commands as $title => $command)
		{
			if (! isset($groups[$command['group']]))
			{
				$groups[$command['group']] = [];
			}

			$groups[$command['group']][$title] = $command;
		}

		$length = max(array_map('strlen', array_keys($commands)));

		ksort($groups);

		// Display it all...
		foreach ($groups as $group => $commands)
		{
			CLI::write($group, 'yellow');
			foreach ($commands as $name => $command)
			{
				$name   = $this->setPad($name, $length, 2, 2);
				$output = CLI::color($name, 'green');
				if (isset($command['description']))
				{
					$output .= CLI::wrap($command['description'], 125, strlen($name));
				}
				CLI::write($output);
			}

			if ($group !== array_key_last($groups))
			{
				CLI::newLine();
			}
		}
	}

	/**
	 * Lists the commands only.
	 *
	 * @param array $commands
	 */
	protected function listSimple(array $commands)
	{
		foreach (array_keys($commands) as $title)
		{
			CLI::write($title);
		}
	}
}
