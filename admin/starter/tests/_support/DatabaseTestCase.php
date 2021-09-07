<?php

namespace Tests\Support;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class DatabaseTestCase extends CIUnitTestCase
{
    use DatabaseTestTrait;

    /**
     * Should the database be refreshed before each test?
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
    protected $seed = 'Tests\Support\Database\Seeds\ExampleSeeder';

    /**
     * The path to the seeds directory.
     * Allows overriding the default application directories.
     *
     * @var string
     */
    protected $basePath = SUPPORTPATH . 'Database/';

    /**
     * The namespace(s) to help us find the migration classes.
     * Empty is equivalent to running `spark migrate -all`.
     * Note that running "all" runs migrations in date order,
     * but specifying namespaces runs them in namespace order (then date)
     *
     * @var array|string|null
     */
    protected $namespace = 'Tests\Support';

    protected function setUp(): void
    {
        parent::setUp();

        // Extra code to run before each test
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Extra code to run after each test
    }
}
