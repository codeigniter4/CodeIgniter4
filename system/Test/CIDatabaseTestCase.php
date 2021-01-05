<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\MigrationRunner;
use CodeIgniter\Database\Seeder;
use CodeIgniter\Exceptions\ConfigException;
use Config\Database;
use Config\Migrations;
use Config\Services;

/**
 * CIDatabaseTestCase
 */
abstract class CIDatabaseTestCase extends CIUnitTestCase
{
	/**
	 * Should run db migration?
	 *
	 * @var boolean
	 */
	protected $migrate = true;

	/**
	 * Should run db migration only once?
	 *
	 * @var boolean
	 */
	protected $migrateOnce = false;

	/**
	 * Is db migration done once or more than once?
	 *
	 * @var boolean
	 */
	private static $doneMigration = false;

	/**
	 * Should run seeding only once?
	 *
	 * @var boolean
	 */
	protected $seedOnce = false;

	/**
	 * Is seeding done once or more than once?
	 *
	 * @var boolean
	 */
	private static $doneSeed = false;

	/**
	 * Should the db be refreshed before test?
	 *
	 * @var boolean
	 */
	protected $refresh = true;

	/**
	 * The seed file(s) used for all tests within this test case.
	 * Should be fully-namespaced or relative to $basePath
	 *
	 * @var string|array
	 */
	protected $seed = '';

	/**
	 * The path to the seeds directory.
	 * Allows overriding the default application directories.
	 *
	 * @var string
	 */
	protected $basePath = SUPPORTPATH . 'Database';

	/**
	 * The namespace(s) to help us find the migration classes.
	 * Empty is equivalent to running `spark migrate -all`.
	 * Note that running "all" runs migrations in date order,
	 * but specifying namespaces runs them in namespace order (then date)
	 *
	 * @var string|array|null
	 */
	protected $namespace = 'Tests\Support';

	/**
	 * The name of the database group to connect to.
	 * If not present, will use the defaultGroup.
	 *
	 * @var string
	 */
	protected $DBGroup = 'tests';

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
	 * @var Seeder
	 */
	protected $seeder;

	/**
	 * Stores information needed to remove any
	 * rows inserted via $this->hasInDatabase();
	 *
	 * @var array
	 */
	protected $insertCache = [];

	//--------------------------------------------------------------------

	/**
	 * Load any database test dependencies.
	 */
	public function loadDependencies()
	{
		if ($this->db === null)
		{
			$this->db = Database::connect($this->DBGroup);
			$this->db->initialize();
		}

		if ($this->migrations === null)
		{
			// Ensure that we can run migrations
			$config          = new Migrations();
			$config->enabled = true;

			$this->migrations = Services::migrations($config, $this->db);
			$this->migrations->setSilent(false);
		}

		if ($this->seeder === null)
		{
			$this->seeder = Database::seeder($this->DBGroup);
			$this->seeder->setSilent(true);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Ensures that the database is cleaned up to a known state
	 * before each test runs.
	 *
	 * @throws ConfigException
	 */
	protected function setUp(): void
	{
		parent::setUp();

		$this->loadDependencies();

		$this->setUpMigrate();
		$this->setUpSeed();
	}

	//--------------------------------------------------------------------

	/**
	 * Migrate on setUp
	 */
	protected function setUpMigrate()
	{
		if ($this->migrateOnce === false || self::$doneMigration === false)
		{
			if ($this->refresh === true)
			{
				$this->regressDatabase();

				// Reset counts on faked items
				Fabricator::resetCounts();
			}

			$this->migrateDatabase();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Seed on setUp
	 */
	protected function setUpSeed()
	{
		if ($this->seedOnce === false || self::$doneSeed === false)
		{
			$this->runSeeds();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Takes care of any required cleanup after the test, like
	 * removing any rows inserted via $this->hasInDatabase()
	 */
	protected function tearDown(): void
	{
		parent::tearDown();

		if (! empty($this->insertCache))
		{
			foreach ($this->insertCache as $row)
			{
				$this->db->table($row[0])
						->where($row[1])
						->delete();
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Run seeds as defined by the class
	 */
	protected function runSeeds()
	{
		if (! empty($this->seed))
		{
			if (! empty($this->basePath))
			{
				$this->seeder->setPath(rtrim($this->basePath, '/') . '/Seeds');
			}

			$seeds = is_array($this->seed) ? $this->seed : [$this->seed];
			foreach ($seeds as $seed)
			{
				$this->seed($seed);
			}
		}

		self::$doneSeed = true;
	}

	//--------------------------------------------------------------------

	/**
	 * Regress migrations as defined by the class
	 */
	protected function regressDatabase()
	{
		if ($this->migrate === false)
		{
			return;
		}

		// If no namespace was specified then rollback all
		if (empty($this->namespace))
		{
			$this->migrations->setNamespace(null);
			$this->migrations->regress(0, 'tests');
		}

		// Regress each specified namespace
		else
		{
			$namespaces = is_array($this->namespace) ? $this->namespace : [$this->namespace];

			foreach ($namespaces as $namespace)
			{
				$this->migrations->setNamespace($namespace);
				$this->migrations->regress(0, 'tests');
			}
		}
	}

	/**
	 * Run migrations as defined by the class
	 */
	protected function migrateDatabase()
	{
		if ($this->migrate === false)
		{
			return;
		}

		// If no namespace was specified then migrate all
		if (empty($this->namespace))
		{
			$this->migrations->setNamespace(null);
			$this->migrations->latest('tests');
			self::$doneMigration = true;
		}
		// Run migrations for each specified namespace
		else
		{
			$namespaces = is_array($this->namespace) ? $this->namespace : [$this->namespace];

			foreach ($namespaces as $namespace)
			{
				$this->migrations->setNamespace($namespace);
				$this->migrations->latest('tests');
				self::$doneMigration = true;
			}
		}
	}

	/**
	 * Seeds that database with a specific seeder.
	 *
	 * @param string $name
	 *
	 * @return void
	 */
	public function seed(string $name)
	{
		$this->seeder->call($name);
	}

	//--------------------------------------------------------------------
	// Database Test Helpers
	//--------------------------------------------------------------------

	/**
	 * Asserts that records that match the conditions in $where do
	 * not exist in the database.
	 *
	 * @param string $table
	 * @param array  $where
	 *
	 * @return void
	 */
	public function dontSeeInDatabase(string $table, array $where)
	{
		$count = $this->db->table($table)
				->where($where)
				->countAllResults();

		$this->assertTrue($count === 0, 'Row was found in database');
	}

	//--------------------------------------------------------------------

	/**
	 * Asserts that records that match the conditions in $where DO
	 * exist in the database.
	 *
	 * @param string $table
	 * @param array  $where
	 *
	 * @return void
	 * @throws DatabaseException
	 */
	public function seeInDatabase(string $table, array $where)
	{
		$count = $this->db->table($table)
				->where($where)
				->countAllResults();

		$this->assertTrue($count > 0, 'Row not found in database: ' . $this->db->showLastQuery());
	}

	//--------------------------------------------------------------------

	/**
	 * Fetches a single column from a database row with criteria
	 * matching $where.
	 *
	 * @param string $table
	 * @param string $column
	 * @param array  $where
	 *
	 * @return boolean
	 * @throws DatabaseException
	 */
	public function grabFromDatabase(string $table, string $column, array $where)
	{
		$query = $this->db->table($table)
				->select($column)
				->where($where)
				->get();

		$query = $query->getRow();

		return $query->$column ?? false;
	}

	//--------------------------------------------------------------------

	/**
	 * Inserts a row into to the database. This row will be removed
	 * after the test has run.
	 *
	 * @param string $table
	 * @param array  $data
	 *
	 * @return boolean
	 */
	public function hasInDatabase(string $table, array $data)
	{
		$this->insertCache[] = [
			$table,
			$data,
		];

		return $this->db->table($table)
					->insert($data);
	}

	//--------------------------------------------------------------------

	/**
	 * Asserts that the number of rows in the database that match $where
	 * is equal to $expected.
	 *
	 * @param integer $expected
	 * @param string  $table
	 * @param array   $where
	 *
	 * @return void
	 * @throws DatabaseException
	 */
	public function seeNumRecords(int $expected, string $table, array $where)
	{
		$count = $this->db->table($table)
				->where($where)
				->countAllResults();

		$this->assertEquals($expected, $count, 'Wrong number of matching rows in database.');
	}

	//--------------------------------------------------------------------

	/**
	 * Reset $doneMigration and $doneSeed
	 *
	 * @afterClass
	 */
	public static function resetMigrationSeedCount()
	{
		self::$doneMigration = false;
		self::$doneSeed      = false;
	}
}
