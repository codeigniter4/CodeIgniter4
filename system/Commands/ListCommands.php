<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * CI Help command for the spark script.
 *
 * Lists the basic usage information for the spark script,
 * and provides a way to list help for other commands.
 *
 * @package CodeIgniter\Commands
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
	protected $options = [];

	/**
	 * The length of the longest command name.
	 * Used during display in columns.
	 *
	 * @var integer
	 */
	protected $maxFirstLength = 0;

	//--------------------------------------------------------------------

	/**
	 * Displays the help for the spark cli script itself.
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$commands = $this->commands->getCommands();

		$this->describeCommands($commands);

		CLI::newLine();
	}

	//--------------------------------------------------------------------

	/**
	 * Displays the commands on the CLI.
	 *
	 * @param array $commands
	 */
	protected function describeCommands(array $commands = [])
	{
		ksort($commands);

		// Sort into buckets by group
		$sorted         = [];
		$maxTitleLength = 0;

		foreach ($commands as $title => $command)
		{
			if (! isset($sorted[$command['group']]))
			{
				$sorted[$command['group']] = [];
			}

			$sorted[$command['group']][$title] = $command;

			$maxTitleLength = max($maxTitleLength, strlen($title));
		}

		ksort($sorted);

		// Display it all...
		foreach ($sorted as $group => $items)
		{
			CLI::newLine();
			CLI::write($group);

			foreach ($items as $title => $item)
			{
				$title = $this->padTitle($title, $maxTitleLength, 2, 2);

				$out = CLI::color($title, 'yellow');

				if (isset($item['description']))
				{
					$out .= CLI::wrap($item['description'], 125, strlen($title));
				}

				CLI::write($out);
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Pads our string out so that all titles are the same length to nicely line up descriptions.
	 *
	 * @param string  $item
	 * @param integer $max
	 * @param integer $extra  // How many extra spaces to add at the end
	 * @param integer $indent
	 *
	 * @return string
	 */
	protected function padTitle(string $item, int $max, int $extra = 2, int $indent = 0): string
	{
		$max += $extra + $indent;

		$item = str_repeat(' ', $indent) . $item;

		return str_pad($item, $max);
	}

	//--------------------------------------------------------------------
}
