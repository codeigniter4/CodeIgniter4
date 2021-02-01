<?php namespace CodeIgniter\Database\DatabaseTestCase;

use CodeIgniter\Test\CIDatabaseTestCase;
use Config\Services;

/**
 * DatabaseTestCaseMigrationOnce1Test and DatabaseTestCaseMigrationOnce2Test
 * show $migrateOnce applies per test case file.
 *
 * @group DatabaseLive
 */
class DatabaseTestCaseMigrationOnce2Test extends CIDatabaseTestCase
{
	/**
	 * Should run db migration only once?
	 *
	 * @var boolean
	 */
	protected $migrateOnce = true;

	/**
	 * Should the db be refreshed before test?
	 *
	 * @var boolean
	 */
	protected $refresh = true;

	/**
	 * The namespace(s) to help us find the migration classes.
	 * Empty is equivalent to running `spark migrate -all`.
	 * Note that running "all" runs migrations in date order,
	 * but specifying namespaces runs them in namespace order (then date)
	 *
	 * @var string|array|null
	 */
	protected $namespace = [
		'Tests\Support\MigrationTestMigrations',
	];

	public function setUp(): void
	{
		Services::autoloader()->addNamespace('Tests\Support\MigrationTestMigrations', SUPPORTPATH . 'MigrationTestMigrations');

		parent::setUp();
	}

	public function testMigrationDone()
	{
		$this->seeInDatabase('foo', ['key' => 'foobar']);
	}
}
