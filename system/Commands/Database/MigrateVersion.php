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
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Commands\Database;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

/**
 * Migrates the DB to a specific version.
 *
 * @package CodeIgniter\Commands
 */
class MigrateVersion extends BaseCommand
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
	protected $name = 'migrate:version';

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Migrates the database up or down to get to the specified version.';

	/**
	 * the Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'migrate:version [version_number] [Options]';

	/**
	 * the Command's Arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'version_number' => 'The version number to migrate',
	];

	/**
	 * the Command's Options
	 *
	 * @var array
	 */
	protected $options = [
		'-n' => 'Set migration namespace',
		'-g' => 'Set database group',
	];

	/**
	 * Migrates the database up or down to get to the specified version.
	 *
	 * @param array $params
	 */
	public function run(array $params = [])
	{
		$runner = Services::migrations();

		// Get the version number
		$version = array_shift($params);

		if (is_null($version))
		{
			$version = CLI::prompt(lang('Migrations.version'));
		}

		if (is_null($version))
		{
			CLI::error(lang('Migrations.invalidVersion'));
			exit();
		}

		CLI::write(sprintf(lang('Migrations.toVersionPH'), $version), 'yellow');

		$namespace = $params['-n'] ?? CLI::getOption('n');
		$group     = $params['-g'] ?? CLI::getOption('g');

		try
		{
			$runner->version($version, $namespace, $group);
			CLI::write('Done');
		}
		catch (\Exception $e)
		{
			$this->showError($e);
		}
	}

}
