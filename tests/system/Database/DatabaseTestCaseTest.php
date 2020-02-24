<?php namespace CodeIgniter\Database;

use Config\Services;
use CodeIgniter\Test\CIDatabaseTestCase;

class DatabaseTestCaseTest extends CIDatabaseTestCase
{
	protected $loaded = false;

	/**
	 * Should the db be refreshed before
	 * each test?
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
	protected $seed = [
		'Tests\Support\Database\Seeds\CITestSeeder',
		'Tests\Support\Database\Seeds\AnotherSeeder'
	];

	/**
	 * The namespace(s) to help us find the migration classes.
	 * Empty is equivalent to running `spark migrate -all`.
	 * Note that running "all" runs migrations in date order,
	 * but specifying namespaces runs them in namespace order (then date)
	 *
	 * @var string|array|null
	 */
	protected $namespace = [
		'Tests\Support',
		'Tests\Support\MigrationTestMigrations',
	];

	public function setUp(): void
	{
		if (! $this->loaded)
		{
			Services::autoloader()->addNamespace('Tests\Support\MigrationTestMigrations', SUPPORTPATH . 'MigrationTestMigrations');
			$this->loaded = true;
		}

		parent::setUp();
	}

	public function testMultipleSeeders()
	{
		$this->seeInDatabase('user', ['name' => 'Jerome Lohan']);
	}

	public function testMultipleMigrationNamespaces()
	{
		$this->seeInDatabase('foo', ['key' => 'foobar']);
	}
}
