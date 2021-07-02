<?php

namespace CodeIgniter\Database\DatabaseTestCase;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use Config\Services;

/**
 * DatabaseTestCaseMigrationOnce1Test and DatabaseTestCaseMigrationOnce2Test
 * show $migrateOnce applies per test case file.
 *
 * @group DatabaseLive
 *
 * @internal
 */
final class DatabaseTestCaseMigrationOnce1Test extends CIUnitTestCase
{
    use DatabaseTestTrait;

    /**
     * Should run db migration only once?
     *
     * @var bool
     */
    protected $migrateOnce = true;

    /**
     * Should the db be refreshed before test?
     *
     * @var bool
     */
    protected $refresh = true;

    /**
     * The namespace(s) to help us find the migration classes.
     * Empty is equivalent to running `spark migrate -all`.
     * Note that running "all" runs migrations in date order,
     * but specifying namespaces runs them in namespace order (then date)
     *
     * @var array|string|null
     */
    protected $namespace = [
        'Tests\Support\MigrationTestMigrations',
    ];

    protected function setUp(): void
    {
        Services::autoloader()->addNamespace('Tests\Support\MigrationTestMigrations', SUPPORTPATH . 'MigrationTestMigrations');

        parent::setUp();
    }

    public function testMigrationDone()
    {
        $this->seeInDatabase('foo', ['key' => 'foobar']);

        // Drop table to make sure there is no foo table when
        // DatabaseTestCaseMigrationOnce2Test runs
        $this->dropTableFoo();
    }

    private function dropTableFoo()
    {
        $forge = Database::forge();
        $forge->dropTable('foo', true);
    }
}
