<?php namespace CodeIgniter\Commands;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
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
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Seeder;
use CodeIgniter\Services;
use Config\Database;

/**
 * Class MigrationsCommand.
 *
 * Migrations controller.
 */
class MigrationsCommand extends \CodeIgniter\Controller
{
	/**
	 * Migration runner.
	 *
	 * @var \CodeIgniter\Database\MigrationRunner
	 */
	protected $runner;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->runner = Services::migrations();
	}

	//--------------------------------------------------------------------

	/**
	 * Provides a list of available commands.
	 */
	public function index()
	{
		CLI::write('Migration Commands',  'white');
		CLI::write(CLI::color('latest',   'yellow'). lang('Migrations.migHelpLatest'));
		CLI::write(CLI::color('current',  'yellow'). lang('Migrations.migHelpCurrent'));
		CLI::write(CLI::color('version [v]',  'yellow'). lang('Migrations.migHelpVersion'));
		CLI::write(CLI::color('rollback', 'yellow'). lang('Migrations.migHelpRollback'));
		CLI::write(CLI::color('refresh',  'yellow'). lang('Migrations.migHelpRefresh'));
		CLI::write(CLI::color('seed [name]',  'yellow'). lang('Migrations.migHelpSeed'));
	}

	//--------------------------------------------------------------------


	/**
	 * Ensures that all migrations have been run.
	 */
	public function latest()
	{
		CLI::write(lang('Migrations.migToLatest'), 'yellow');

		try {
			$this->runner->latest();
		}
		catch (\Exception $e)
		{
			$this->showError($e);
		}

		CLI::write('Done');
	}

	//--------------------------------------------------------------------

	/**
	 * Migrates the database up or down to get to the specified version.
	 *
	 * @param int $version
	 */
	public function version(int $version = null)
	{
		if (is_null($version))
		{
			$version = CLI::prompt(lang('Migrations.version'));
		}

		if (is_null($version))
		{
			CLI::error(lang('Migrations.invalidVersion'));
			exit();
		}

		CLI::write(sprintf(lang('Migrations.migToVersionPH'), $version), 'yellow');

		try {
			$this->runner->version($version);
		}
		catch (\Exception $e)
		{
			$this->showError($e);
		}

		CLI::write('Done');
	}

	//--------------------------------------------------------------------

	/**
	 * Migrates us up or down to the version specified as $currentVersion
	 * in the migrations config file.
	 */
	public function current()
	{
		CLI::write(lang('Migrations.migToVersion'), 'yellow');

		try {
			$this->runner->current();
		}
		catch (\Exception $e)
		{
			$this->showError($e);
		}

		CLI::write('Done');
	}

	//--------------------------------------------------------------------

	/**
	 * Runs all of the migrations in reverse order, until they have
	 * all been un-applied.
	 */
	public function rollback()
	{
		CLI::write(lang('Migrations.migRollingBack'), 'yellow');

		try {
			$this->runner->version(0);
		}
		catch (\Exception $e)
		{
			$this->showError($e);
		}

		CLI::write('Done');
	}

	//--------------------------------------------------------------------

	/**
	 * Does a rollback followed by a latest to refresh the current state
	 * of the database.
	 */
	public function refresh()
	{
		$this->rollback();
		$this->latest();
	}

	//--------------------------------------------------------------------

	/**
	 * Displays a list of all migrations and whether they've been run or not.
	 */
	public function status()
	{
		$migrations = $this->runner->findMigrations();
		$history    = $this->runner->getHistory();

		if (empty($migrations))
		{
			return CLI::error(lang('Migrations.migNoneFound'));
		}

		$max = 0;

		foreach ($migrations as $version => $file)
		{
			$file = substr($file, strpos($file, $version.'_'));
			$migrations[$version] = $file;

			$max = max($max, strlen($file));
		}

		CLI::write(str_pad(lang('Migrations.filename'), $max+4).lang('Migrations.migOn'), 'yellow');

		foreach ($migrations as $version => $file)
		{
			$date = '';
			foreach ($history as $row)
			{
				if ($row['version'] != $version) continue;

				$date = $row['time'];
			}

			CLI::write(str_pad($file, $max+4). ($date ? $date : '---'));
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Runs the specified Seeder file to populate the database
	 * with some data.
	 *
	 * @param string $seedName
	 */
	public function seed(string $seedName = null)
	{
		$seeder = new Seeder(new \Config\Database());

		if (empty($seedName))
		{
			$seedName = CLI::prompt(lang('Migrations.migSeeder'));
		}

		if (empty($seedName))
		{
			CLI::error(lang('Migrations.migMissingSeeder'));
			return;
		}

		try
		{
			$seeder->call($seedName);
		}
		catch (\Exception $e)
		{
			$this->showError($e);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Displays a caught exception.
	 *
	 * @param \Exception $e
	 */
	protected function showError(\Exception $e)
	{
		CLI::error($e->getMessage());
		CLI::write($e->getFile().' - '.$e->getLine(), 'white');
	}

	//--------------------------------------------------------------------

}
