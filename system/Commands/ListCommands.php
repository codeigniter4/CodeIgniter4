<?php namespace CodeIgniter\Commands;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2017 British Columbia Institute of Technology
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
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	2014-2017 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * CI Help command for the ci.php script.
 *
 * Lists the basic usage information for the ci.php script,
 * and provides a way to list help for other commands.
 *
 * @package CodeIgniter\Commands
 */
class ListCommands extends BaseCommand
{

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
	protected $arguments = array();

	/**
	 * the Command's Options
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * The length of the longest command name.
	 * Used during display in columns.
	 *
	 * @var int
	 */
	protected $maxFirstLength = 0;

	//--------------------------------------------------------------------

	/**
	 * Displays the help for the ci.php cli script itself.
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
		arsort($commands);

		$names = array_keys($commands);
		$descs = array_column($commands, 'description');
		$groups = array_column($commands, 'group');
		$lastGroup = '';

		// Pad each item to the same length
		$names = $this->padArray($names, 2, 2);

		for ($i = 0; $i < count($names); $i ++ )
		{
			$lastGroup = $this->describeGroup($groups[$i], $lastGroup);

			$out = CLI::color($names[$i], 'yellow');

			if (isset($descs[$i]))
			{
				$out .= CLI::wrap($descs[$i], 125, strlen($names[$i]));
			}

			CLI::write($out);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Outputs the description, if necessary.
	 *
	 * @param string $new
	 * @param string $old
	 *
	 * @return string
	 */
	protected function describeGroup(string $new, string $old)
	{
		if ($new == $old)
		{
			return $old;
		}

		CLI::newLine();
		CLI::write($new);

		return $new;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a new array where all of the string elements have
	 * been padding with trailing spaces to be the same length.
	 *
	 * @param array $array
	 * @param int   $extra // How many extra spaces to add at the end
	 *
	 * @return array
	 */
	protected function padArray($array, $extra = 2, $indent = 0)
	{
		$max = max(array_map('strlen', $array)) + $extra + $indent;

		foreach ($array as &$item)
		{
			$item = str_repeat(' ', $indent) . $item;
			$item = str_pad($item, $max);
		}

		return $array;
	}

	//--------------------------------------------------------------------
}
