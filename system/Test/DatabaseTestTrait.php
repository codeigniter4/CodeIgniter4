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

use CodeIgniter\Database\BaseBuilder;
use Config\Database;
use Config\Migrations;
use Config\Services;

/**
 * DatabaseTestTrait
 *
 * Provides functionality for refreshing/seeding
 * the database during testing.
 *
 * @mixin CIUnitTestCase
 */
trait DatabaseTestTrait
{
	/**
	 * Is db migration done once or more than once?
	 *
	 * @var boolean
	 */
	private static $doneMigration = false;

	/**
	 * Is seeding done once or more than once?
	 *
	 * @var boolean
	 */
	private static $doneSeed = false;

	//--------------------------------------------------------------------
	// Staging
	//--------------------------------------------------------------------

	/**
	 * Runs the trait set up methods.
	 */
	protected function setUpDatabase()
	{
		$this->loadDependencies();
		$this->setUpMigrate();
		$this->setUpSeed();
	}

	/**
	 * Runs the trait set up methods.
	 */
	protected function tearDownDatabase()
	{
		$this->clearInsertCache();
	}

	//--------------------------------------------------------------------
	// Support
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

	/**
	 * Loads the Builder class appropriate for the current database.
	 *
	 * @param string $tableName
	 *
	 * @return BaseBuilder
	 */
	public function loadBuilder(string $tableName)
	{
		$builderClass = str_replace('Connection', 'Builder', get_class($this->db));

		return new $builderClass($tableName, $this->db);
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

	//--------------------------------------------------------------------
	// Database
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

	/**
	 * Removes any rows inserted via $this->hasInDatabase()
	 */
	protected function clearInsertCache()
	{
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
}
