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

namespace CodeIgniter\CLI;

use CodeIgniter\Controller;
use Config\Services;

/**
 * Command runner
 */
class CommandRunner extends Controller
{

	/**
	 * Stores the info about found Commands.
	 *
	 * @var array
	 */
	protected $commands = [];

	/**
	 * Message logger.
	 *
	 * @var \CodeIgniter\Log\Logger
	 */
	protected $logger;

	//--------------------------------------------------------------------

	/**
	 * We map all un-routed CLI methods through this function
	 * so we have the chance to look for a Command first.
	 *
	 * @param string $method
	 * @param array  ...$params
	 *
	 * @return mixed
	 * @throws \ReflectionException
	 */
	public function _remap($method, ...$params)
	{
		// The first param is usually empty, so scrap it.
		if (empty($params[0]))
		{
			array_shift($params);
		}

		return $this->index($params);
	}

	//--------------------------------------------------------------------

	/**
	 * Default command.
	 *
	 * @param array $params
	 *
	 * @return mixed
	 * @throws \ReflectionException
	 */
	public function index(array $params)
	{
		$command = array_shift($params);

		$this->createCommandList();

		if (is_null($command))
		{
			$command = 'list';
		}

		return $this->runCommand($command, $params);
	}

	//--------------------------------------------------------------------

	/**
	 * Actually runs the command.
	 *
	 * @param string $command
	 * @param array  $params
	 *
	 * @return mixed
	 */
	protected function runCommand(string $command, array $params)
	{
		if (! isset($this->commands[$command]))
		{
			CLI::error(lang('CLI.commandNotFound', [$command]));
			CLI::newLine();
			return;
		}

		// The file would have already been loaded during the
		// createCommandList function...
		$className = $this->commands[$command]['class'];
		$class     = new $className($this->logger, $this);

		return $class->run($params);
	}

	//--------------------------------------------------------------------

	/**
	 * Scans all Commands directories and prepares a list
	 * of each command with it's group and file.
	 *
	 * @throws \ReflectionException
	 */
	protected function createCommandList()
	{
		$files = Services::locator()->listFiles('Commands/');

		// If no matching command files were found, bail
		if (empty($files))
		{
			// This should never happen in unit testing.
			// if it does, we have far bigger problems!
			// @codeCoverageIgnoreStart
			return;
			// @codeCoverageIgnoreEnd
		}

		// Loop over each file checking to see if a command with that
		// alias exists in the class. If so, return it. Otherwise, try the next.
		foreach ($files as $file)
		{
			$className = Services::locator()->findQualifiedNameFromPath($file);
			if (empty($className) || ! class_exists($className))
			{
				continue;
			}

			$class = new \ReflectionClass($className);

			if (! $class->isInstantiable() || ! $class->isSubclassOf(BaseCommand::class))
			{
				continue;
			}

			$class = new $className($this->logger, $this);

			// Store it!
			if ($class->group !== null)
			{
				$this->commands[$class->name] = [
					'class'       => $className,
					'file'        => $file,
					'group'       => $class->group,
					'description' => $class->description,
				];
			}

			$class = null;
			unset($class);
		}

		asort($this->commands);
	}

	//--------------------------------------------------------------------

	/**
	 * Allows access to the current commands that have been found.
	 *
	 * @return array
	 */
	public function getCommands(): array
	{
		return $this->commands;
	}

	//--------------------------------------------------------------------
}
