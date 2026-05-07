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

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Database;
use PHPUnit\Framework\Attributes\Group;

/**
 * @todo To figure out how to transfer this test to `tests/system/Commands/Database/` without breaking DatabaseLive group.
 *
 * @internal
 */
#[Group('DatabaseLive')]
final class MigrationIntegrationTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function setUp(): void
    {
        $this->resetServices();

        parent::setUp();

        service('migrations')->clearHistory();
        $this->dropTestTables();
    }

    protected function tearDown(): void
    {
        service('migrations')->clearHistory();
        $this->dropTestTables();

        $this->resetServices();

        parent::tearDown();
    }

    public function testMigrationWithRollbackHasSameNameFormat(): void
    {
        command('migrate -n Tests\\\\Support');
        $this->assertStringContainsString(
            '(Tests\Support) 20160428212500_Tests\Support\Database\Migrations\Migration_Create_test_tables',
            $this->getStreamFilterBuffer(),
        );

        $this->resetStreamFilterBuffer();
        $this->resetServices();

        command('migrate:rollback');
        $this->assertStringContainsString(
            '(Tests\Support) 20160428212500_Tests\Support\Database\Migrations\Migration_Create_test_tables',
            $this->getStreamFilterBuffer(),
        );
    }

    private function dropTestTables(): void
    {
        $forge = Database::forge();

        foreach ([
            'user',
            'job',
            'misc',
            'team_members',
            'type_test',
            'empty',
            'secondary',
            'stringifypkey',
            'without_auto_increment',
            'ip_table',
            'ci_sessions',
            'migrations_lock',
        ] as $table) {
            $forge->dropTable($table, true);
        }
    }
}
