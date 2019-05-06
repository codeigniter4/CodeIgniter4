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

namespace CodeIgniter\Test;

use Config\Database;
use Config\Migrations;
use Config\Services;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\MigrationRunner;
use CodeIgniter\Exceptions\ConfigException;

/**
 * CIDatabaseTestCase
 */
class CIDatabaseTestCase extends CIUnitTestCase
{

	/**
	 * Should the db be refreshed before
	 * each test?
	 *
	 * @var boolean
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
	protected $basePath = TESTPATH . '_support/Database';

	/**
	 * The namespace to help us fird the migration classes.
	 *
	 * @var string
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
	 * @var \CodeIgniter\Database\Seeder
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
	protected function setUp()
	{
		parent::setUp();

		$this->loadDependencies();

		if ($this->refresh === true)
		{
			if (! empty($this->namespace))
			{
				$this->migrations->setNamespace($this->namespace);
			}

			// Delete all of the tables to ensure we're at a clean start.
			$tables = $this->db->listTables();

			if (is_array($tables))
			{
				$forge = Database::forge('tests');

				foreach ($tables as $table)
				{
					if ($table === $this->db->DBPrefix . 'migrations')
					{
						continue;
					}

					$forge->dropTable($table, true);
				}
			}

			$this->migrations->version(0, null, 'tests');
			$this->migrations->latest(null, 'tests');
		}

		if (! empty($this->seed))
		{
			if (! empty($this->basePath))
			{
				$this->seeder->setPath(rtrim($this->basePath, '/') . '/seeds');
			}

			$this->seed($this->seed);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Takes care of any required cleanup after the test, like
	 * removing any rows inserted via $this->hasInDatabase()
	 */
	public function tearDown()
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
	 * @return boolean
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
	 * @return boolean
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
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
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
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
	 * @return boolean
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function seeNumRecords(int $expected, string $table, array $where)
	{
		$count = $this->db->table($table)
				->where($where)
				->countAllResults();

		$this->assertEquals($expected, $count, 'Wrong number of matching rows in database.');
	}

	//--------------------------------------------------------------------
}
