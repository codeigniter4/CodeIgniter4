<?php namespace CodeIgniter\Test;

use CodeIgniter\ConfigException;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\MigrationRunner;
use Config\Services;

class CIDatabaseTestCase extends CIUnitTestCase
{
	/**
	 * Should the db be refreshed before
	 * each test?
	 *
	 * @var bool
	 */
	protected $refresh = true;

	/**
	 * The name of the fixture used for all tests
	 * within this test case.
	 *
	 * @var string
	 */
	protected $seed = '';

	/**
	 * The path to where we can find the migrations
	 * and seeds directories. Allows overriding
	 * the default application directories.
	 *
	 * @var string
	 */
	protected $basePath;

	/**
	 * The name of the database group to connect to.
	 * If not present, will use the defaultGroup.
	 *
	 * @var string
	 */
	protected $DBGroup;

	/**
	 * Our database connection.
	 * 
	 * @var BaseConnection
	 */
	protected $db;

	/**
	 * Migration Runner instance.
	 * 
	 * @var MigrationRunner|mixed
	 */
	protected $migrations;

	/**
	 * Seeder instance 
	 *
	 * @var \CodeIgniter\Database\Seeder
	 */
	protected $seeder;

	//--------------------------------------------------------------------

	public function __construct()
	{
	    parent::__construct();

		$this->db = \Config\Database::connect($this->DBGroup);

		// Ensure that we can run migrations
		$config = new \Config\Migrations();
		$config->enabled = true;

		$this->migrations = Services::migrations($this->db);
		$this->migrations->setSilent(true);

		$this->seeder = \Config\Database::seeder($this->DBGroup);
	}
	
	//--------------------------------------------------------------------

	/**
	 * Ensures that the database is cleaned up to a known state
	 * before each test runs.
	 *
	 * @throws ConfigException
	 */
	public function setUp()
	{
		if ($this->refresh === true)
		{
			if (! empty($this->basePath))
			{
				$this->migrations->setPath(rtrim($this->basePath, '/').'/migrations');
			}

			$this->migrations->version(0);
			$this->migrations->latest();
		}

		if (! empty($this->seed))
		{
			if (! empty($this->basePath))
			{
				$this->seeder->setPath(rtrim($this->basePath, '/').'/seeds');
			}
			
			$this->seed($this->seed);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Seeds that database with a specific seeder.
	 *
	 * @param string $name
	 */
	public function seed(string $name)
	{
        return $this->seeder->call($name);
	}

	//--------------------------------------------------------------------


}