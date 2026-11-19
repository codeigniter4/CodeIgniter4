<?php

declare(strict_types=1);

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
use Override;
use PHPUnit\Framework\Attributes\Group;

/**
 * DatabaseTestCaseMigrationOnce1Test and DatabaseTestCaseMigrationOnce2Test
 * show $migrateOnce applies per test case file.
 *
 * @internal
 */
#[Group('DatabaseLive')]
final class DatabaseTestCaseMigrationOnce2Test extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrateOnce = true;
    protected $refresh     = true;
    protected $namespace   = [
        'Tests\Support\MigrationTestMigrations',
    ];

    #[Override]
    protected function setUp(): void
    {
        $forge = Database::forge();
        $forge->dropTable('foo', true);

        $this->setUpMethods[] = 'setUpAddNamespace';

        parent::setUp();
    }

    protected function setUpAddNamespace(): void
    {
        service('autoloader')->addNamespace(
            'Tests\Support\MigrationTestMigrations',
            SUPPORTPATH . 'MigrationTestMigrations',
        );
    }

    #[Override]
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
