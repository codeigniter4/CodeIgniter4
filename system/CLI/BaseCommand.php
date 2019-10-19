<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019 CodeIgniter Foundation
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
 * @copyright  2019 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\CLI;

use Psr\Log\LoggerInterface;

/**
 * Class BaseCommand
 *
 * @property $group
 * @property $name
 * @property $description
 *
 * @package CodeIgniter\CLI
 */
abstract class BaseCommand
{

	/**
	 * The group the command is lumped under
	 * when listing commands.
	 *
	 * @var string
	 */
	protected $group;

	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * the Command's usage description
	 *
	 * @var string
	 */
	protected $usage;

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * the Command's options description
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 * the Command's Arguments description
	 *
	 * @var array
	 */
	protected $arguments = [];

	/**
	 * The Logger to use for a command
	 *
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * Instance of the CommandRunner controller
	 * so commands can call other commands.
	 *
	 * @var \CodeIgniter\CLI\CommandRunner
	 */
	protected $commands;

	//--------------------------------------------------------------------

	/**
	 * BaseCommand constructor.
	 *
	 * @param \Psr\Log\LoggerInterface       $logger
	 * @param \CodeIgniter\CLI\CommandRunner $commands
	 */
	public function __construct(LoggerInterface $logger, CommandRunner $commands)
	{
		$this->logger   = $logger;
		$this->commands = $commands;
	}

	//--------------------------------------------------------------------

	/**
	 * Actually execute a command.
	 * This has to be over-ridden in any concrete implementation.
	 *
	 * @param array $params
	 */
	abstract public function run(array $params);

	//--------------------------------------------------------------------

	/**
	 * Can be used by a command to run other commands.
	 *
	 * @param string $command
	 * @param array  $params
	 *
	 * @return mixed
	 * @throws \ReflectionException
	 */
	protected function call(string $command, array $params = [])
	{
		// The CommandRunner will grab the first element
		// for the command name.
		array_unshift($params, $command);

		return $this->commands->index($params);
	}

	//--------------------------------------------------------------------

	/**
	 * A simple method to display an error with line/file,
	 * in child commands.
	 *
	 * @param \Exception $e
	 */
	protected function showError(\Exception $e)
	{
		CLI::newLine();
		CLI::error($e->getMessage());
		CLI::write($e->getFile() . ' - ' . $e->getLine());
		CLI::newLine();
	}

	//--------------------------------------------------------------------

	/**
	 * Makes it simple to access our protected properties.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function __get(string $key)
	{
		if (isset($this->$key))
		{
			return $this->$key;
		}

		return null;
	}

	//--------------------------------------------------------------------

	/**
	 * Makes it simple to check our protected properties.
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	public function __isset(string $key): bool
	{
		return isset($this->$key);
	}

	//--------------------------------------------------------------------

	/**
	 * show Help include (usage,arguments,description,options)
	 */
	public function showHelp()
	{
		// 4 spaces instead of tab
		$tab = '   ';
		CLI::write(lang('CLI.helpDescription'), 'yellow');
		CLI::write($tab . $this->description);
		CLI::newLine();

		CLI::write(lang('CLI.helpUsage'), 'yellow');
		$usage = empty($this->usage) ? $this->name . ' [arguments]' : $this->usage;
		CLI::write($tab . $usage);
		CLI::newLine();

		$pad = max($this->getPad($this->options, 6), $this->getPad($this->arguments, 6));

		if (! empty($this->arguments))
		{
			CLI::write(lang('CLI.helpArguments'), 'yellow');
			foreach ($this->arguments as $argument => $description)
			{
				CLI::write($tab . CLI::color(str_pad($argument, $pad), 'green') . $description, 'yellow');
			}
			CLI::newLine();
		}

		if (! empty($this->options))
		{
			CLI::write(lang('CLI.helpOptions'), 'yellow');
			foreach ($this->options as $option => $description)
			{
				CLI::write($tab . CLI::color(str_pad($option, $pad), 'green') . $description, 'yellow');
			}
			CLI::newLine();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Get pad for $key => $value array output
	 *
	 * @param array   $array
	 * @param integer $pad
	 *
	 * @return integer
	 */
	public function getPad(array $array, int $pad): int
	{
		$max = 0;
		foreach ($array as $key => $value)
		{
			$max = max($max, strlen($key));
		}
		return $max + $pad;
	}

	//--------------------------------------------------------------------
}
