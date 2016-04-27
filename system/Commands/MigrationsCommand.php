<?php namespace CodeIgniter\Commands;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Seeder;
use CodeIgniter\Services;
use Config\Database;

class MigrationsCommand extends \CodeIgniter\Controller
{
	/**
	 * @var \CodeIgniter\Database\MigrationRunner
	 */
	protected $runner;

	//--------------------------------------------------------------------

	public function __construct()
	{
	    $this->runner = Services::migrations();
	}

	//--------------------------------------------------------------------

	/**
	 * Ensures that all migrations have been run.
	 */
	public function latest()
	{
		CLI::write('Migrating to latest version...', 'yellow');

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
			$version = CLI::prompt('Version');
		}

		if (is_null($version))
		{
			CLI::error('Invalid version number provided.');
			exit();
		}

		CLI::write("Migrating to version {$version}...", 'yellow');

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
		CLI::write("Migrating to current version...", 'yellow');

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
		CLI::write("Rolling back all migrations...", 'yellow');

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
			return CLI::error('No migrations were found.');
		}

		$max = 0;

		foreach ($migrations as $version => $file)
		{
			$file = substr($file, strpos($file, $version.'_'));
			$migrations[$version] = $file;

			$max = max($max, strlen($file));
		}

		CLI::write(str_pad('Filename', $max+4).'Migrated On', 'yellow');

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
			$seedName = CLI::prompt('Seeder name');
		}

		if (empty($seedName))
		{
			CLI::error('You must provide a seeder name.');
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
