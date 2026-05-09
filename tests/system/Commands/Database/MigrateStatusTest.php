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

namespace CodeIgniter\Commands\Database;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Database;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class MigrateStatusTest extends CIUnitTestCase
{
    use StreamFilterTrait;
    use DatabaseTestTrait;

    private string $migrationNamespace     = 'Tests\\Support\\MigrationTestMigrations';
    private string $migrationNamespacePath = SUPPORTPATH . 'MigrationTestMigrations/';

    protected function setUp(): void
    {
        $this->resetServices();

        parent::setUp();

        Database::connect()->table('migrations')->emptyTable();
        Database::forge()->dropTable('foo', true);

        service('autoloader')->addNamespace($this->migrationNamespace, $this->migrationNamespacePath);

        putenv('NO_COLOR=1');
        CLI::init();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Database::connect()->table('migrations')->emptyTable();
        Database::forge()->dropTable('foo', true);

        putenv('NO_COLOR');
        CLI::init();

        $this->resetServices();
    }

    public function testMigrateAllWithWithTwoNamespaces(): void
    {
        command('migrate --all');
        $this->resetStreamFilterBuffer();

        command('migrate:status');

        $this->assertMigrationStatusHasBothNamespaceMigrations();
    }

    public function testMigrateWithWithTwoNamespaces(): void
    {
        command('migrate -n Tests\\\\Support\\\\MigrationTestMigrations');
        command('migrate -n Tests\\\\Support');
        $this->resetStreamFilterBuffer();

        command('migrate:status');

        $this->assertMigrationStatusHasBothNamespaceMigrations();
    }

    private function assertMigrationStatusHasBothNamespaceMigrations(): void
    {
        $result       = str_replace(PHP_EOL, "\n", $this->getStreamFilterBuffer());
        $theadPattern = '/^\|[[:space:]]+Namespace[[:space:]]+\|[[:space:]]+Version[[:space:]]+\|[[:space:]]+Filename[[:space:]]+\|[[:space:]]+Group[[:space:]]+\|[[:space:]]+Migrated On[[:space:]]+\|[[:space:]]+Batch[[:space:]]+\|$/m';

        $this->assertMatchesRegularExpression($theadPattern, $result);
        $this->assertStringContainsString($this->migrationNamespace, $result);
        $this->assertStringContainsString('2018-01-24-102301', $result);
        $this->assertStringContainsString('Some_migration', $result);
        $this->assertStringContainsString('Tests\Support', $result);
        $this->assertStringContainsString('20160428212500', $result);
        $this->assertStringContainsString('Create_test_tables', $result);
    }
}
