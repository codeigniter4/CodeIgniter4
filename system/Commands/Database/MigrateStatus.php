<?php namespace CodeIgniter\Commands\Database;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2018 British Columbia Institute of Technology
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
 * @package      CodeIgniter
 * @author       CodeIgniter Dev Team
 * @copyright    2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license      https://opensource.org/licenses/MIT	MIT License
 * @link         https://codeigniter.com
 * @since        Version 3.0.0
 * @filesource
 */
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;
use Config\Autoload;

/**
 * Displays a list of all migrations and whether they've been run or not.
 *
 * @package CodeIgniter\Commands
 */
class MigrateStatus extends BaseCommand
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
	protected $name = 'migrate:status';

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Displays a list of all migrations and whether they\'ve been run or not.';

	/**
	 * the Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'migrate:status [Options]';

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
		'-g' => 'Set database group',
	];

	protected $ignoredNamespaces = [
		'CodeIgniter',
		'Config',
		'Tests\Support'
	];

	/**
	 * Displays a list of all migrations and whether they've been run or not.
	 *
	 * @param array $params
	 */
	public function run(array $params = [])
	{
		$runner = Services::migrations();

		if ( ! is_null(CLI::getOption('g')))
		{
			$runner->setGroup(CLI::getOption('g'));
		}

		// Get all namespaces form  PSR4 paths.
		$config = new Autoload();
		$namespaces = $config->psr4;

		// Loop for all $namespaces
		foreach ($namespaces as $namespace => $path)
		{
			if (in_array($namespace, $this->ignoredNamespaces))
			{
				continue;
			}

			$runner->setNamespace($namespace);
			$migrations = $runner->findMigrations();
			$history = $runner->getHistory();

			CLI::write($namespace);

			if (empty($migrations))
			{
				CLI::error(lang('Migrations.noneFound'));
				continue;
			}

			ksort($migrations);

			$max = 0;
			foreach ($migrations as $version => $migration)
			{
				$file = substr($migration->name, strpos($migration->name, $version . '_'));
				$migrations[$version]->name = $file;

				$max = max($max, strlen($file));
			}

			CLI::write('  '. str_pad(lang('Migrations.filename'), $max + 4) . lang('Migrations.on'), 'yellow');


			foreach ($migrations as $version => $migration)
			{
				$date = '';
				foreach ($history as $row)
				{
					if ($row['version'] != $version)
					{
						continue;
					}

					$date = date("Y-m-d H:i:s", $row['time']);
				}
				CLI::write(str_pad('  '.$migration->name, $max + 6) . ($date ? $date : '---'));
			}
		}
	}

}
