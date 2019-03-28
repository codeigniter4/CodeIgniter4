<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
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
 * @copyright  2014-2019 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 3.0.0
 * @filesource
 */

namespace CodeIgniter\Commands\Database;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

/**
 * Creates a new migration file.
 *
 * @package CodeIgniter\Commands
 */
class MigrateLatest extends BaseCommand
{
	/**
	 * The group the command is lumped under
	 * when listing commands.
	 *
	 * @var string
	 */
	protected $group = 'Database';

	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name = 'migrate:latest';

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Migrates the database to the latest schema.';

	/**
	 * the Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'migrate:latest [options]';

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
		'-n'   => 'Set migration namespace',
		'-g'   => 'Set database group',
		'-all' => 'Set latest for all namespace, will ignore (-n) option',
	];

	/**
	 * Ensures that all migrations have been run.
	 *
	 * @param array $params
	 */
	public function run(array $params = [])
	{
		$runner = Services::migrations();

		CLI::write(lang('Migrations.toLatest'), 'yellow');

		$namespace = $params['-n'] ?? CLI::getOption('n');
		$group     = $params['-g'] ?? CLI::getOption('g');

		try
		{
			if ($this->isAllNamespace($params))
			{
				$runner->latestAll($group);
			}
			else
			{
				$runner->latest($namespace, $group);
			}
			$messages = $runner->getCliMessages();
			foreach ($messages as $message)
			{
				CLI::write($message);
			}

			CLI::write('Done');
		}
		catch (\Exception $e)
		{
			$this->showError($e);
		}
	}

	/**
	 * To migrate all namespaces to the latest migration
	 *
	 * Demo:
	 *  1. command line: php spark migrate:latest -all
	 *  2. command file: $this->call('migrate:latest', ['-g' => 'test','-all']);
	 *
	 * @param  array $params
	 * @return boolean
	 */
	private function isAllNamespace(array $params)
	{
		if (array_search('-all', $params) !== false)
		{
			return true;
		}

		return ! is_null(CLI::getOption('all'));
	}

}
