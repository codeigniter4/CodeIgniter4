<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

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
final class DatabaseTestCaseMigrationOnce2Test extends CIUnitTestCase
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
        $forge = Database::forge();
        $forge->dropTable('foo', true);

        $this->setUpMethods[] = 'setUpAddNamespace';

        parent::setUp();
    }

    protected function setUpAddNamespace(): void
    {
        Services::autoloader()->addNamespace(
            'Tests\Support\MigrationTestMigrations',
            SUPPORTPATH . 'MigrationTestMigrations'
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->regressDatabase();
    }

    public function testMigrationDone(): void
    {
        $this->seeInDatabase('foo', ['key' => 'foobar']);
    }
}
