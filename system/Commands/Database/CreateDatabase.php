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

namespace CodeIgniter\Commands\Database;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Config\Config;
use CodeIgniter\Database\SQLite3\Connection;
use Config\Database;
use Throwable;

/**
 * Creates a new database.
 */
class CreateDatabase extends BaseCommand
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
	protected $name = 'db:create';

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Create a new database schema.';

	/**
	 * the Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'db:create <db_name> [options]';

	/**
	 * The Command's arguments
	 *
	 * @var array<string, string>
	 */
	protected $arguments = [
		'db_name' => 'The database name to use',
	];

	/**
	 * The Command's options
	 *
	 * @var array<string, string>
	 */
	protected $options = [
		'--ext' => 'File extension of the database file for SQLite3. Can be `db` or `sqlite`. Defaults to `db`.',
	];

	/**
	 * Creates a new database.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function run(array $params)
	{
		$name = array_shift($params);

		if (empty($name))
		{
			$name = CLI::prompt('Database name', null, 'required'); // @codeCoverageIgnore
		}

		$db = Database::connect();

		try
		{
			// Special SQLite3 handling
			if ($db instanceof Connection)
			{
				$config = config('Database');
				$group  = ENVIRONMENT === 'testing' ? 'tests' : $config->defaultGroup;
				$ext    = $params['ext'] ?? CLI::getOption('ext') ?? 'db';

				if (! in_array($ext, ['db', 'sqlite'], true))
				{
					$ext = CLI::prompt('Please choose a valid file extension', ['db', 'sqlite']); // @codeCoverageIgnore
				}

				if (strpos($name, ':memory:') === false)
				{
					$name = str_replace(['.db', '.sqlite'], '', $name) . ".{$ext}";
				}

				$config->{$group}['DBDriver'] = 'SQLite3';
				$config->{$group}['database'] = $name;

				if (strpos($name, ':memory:') === false)
				{
					$dbName = strpos($name, DIRECTORY_SEPARATOR) === false ? WRITEPATH . $name : $name;

					if (is_file($dbName))
					{
						CLI::error("Database \"{$dbName}\" already exists.", 'light_gray', 'red');
						CLI::newLine();

						return;
					}

					unset($dbName);
				}

				// Connect to new SQLite3 to create new database,
				// then reset the altered Config\Database instance
				$db = Database::connect(null, false);
				$db->connect();
				Config::reset();

				if (! is_file($db->getDatabase()) && strpos($name, ':memory:') === false)
				{
					// @codeCoverageIgnoreStart
					CLI::error('Database creation failed.', 'light_gray', 'red');
					CLI::newLine();

					return;
					// @codeCoverageIgnoreEnd
				}
			}
			else
			{
				if (! Database::forge()->createDatabase($name))
				{
					// @codeCoverageIgnoreStart
					CLI::error('Database creation failed.', 'light_gray', 'red');
					CLI::newLine();

					return;
					// @codeCoverageIgnoreEnd
				}
			}

			CLI::write("Database \"{$name}\" successfully created.", 'green');
			CLI::newLine();
		}
		catch (Throwable $e)
		{
			$this->showError($e);
		}
	}
}
