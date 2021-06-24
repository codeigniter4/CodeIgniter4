<?php

namespace CodeIgniter\Database;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Services;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class DatabaseTestCaseTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected static $loaded = false;

    /**
     * Should the db be refreshed before
     * each test?
     *
     * @var bool
     */
    protected $refresh = true;

    /**
     * The seed file(s) used for all tests within this test case.
     * Should be fully-namespaced or relative to $basePath
     *
     * @var array|string
     */
    protected $seed = [
        'Tests\Support\Database\Seeds\CITestSeeder',
        'Tests\Support\Database\Seeds\AnotherSeeder',
    ];

    /**
     * The namespace(s) to help us find the migration classes.
     * Empty is equivalent to running `spark migrate -all`.
     * Note that running "all" runs migrations in date order,
     * but specifying namespaces runs them in namespace order (then date)
     *
     * @var array|string|null
     */
    protected $namespace = [
        'Tests\Support',
        'Tests\Support\MigrationTestMigrations',
    ];

    protected function setUp(): void
    {
        if (! self::$loaded) {
            Services::autoloader()->addNamespace('Tests\Support\MigrationTestMigrations', SUPPORTPATH . 'MigrationTestMigrations');
            self::$loaded = true;
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
