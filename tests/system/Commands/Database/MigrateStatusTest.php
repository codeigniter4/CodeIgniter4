<?php

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
use CodeIgniter\Test\StreamFilterTrait;
use Config\Database;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class MigrateStatusTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    private string $migrationFileFrom = SUPPORTPATH . 'MigrationTestMigrations/Database/Migrations/2018-01-24-102301_Some_migration.php';
    private string $migrationFileTo   = APPPATH . 'Database/Migrations/2018-01-24-102301_Some_migration.php';

    protected function setUp(): void
    {
        $forge = Database::forge();
        $forge->dropTable('foo', true);

        parent::setUp();

        if (! is_file($this->migrationFileFrom)) {
            $this->fail(clean_path($this->migrationFileFrom) . ' is not found.');
        }

        if (is_file($this->migrationFileTo)) {
            @unlink($this->migrationFileTo);
        }

        copy($this->migrationFileFrom, $this->migrationFileTo);

        $contents = file_get_contents($this->migrationFileTo);
        $contents = str_replace(
            'namespace Tests\Support\MigrationTestMigrations\Database\Migrations;',
            'namespace App\Database\Migrations;',
            $contents
        );
        file_put_contents($this->migrationFileTo, $contents);

        putenv('NO_COLOR=1');
        CLI::init();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $db = db_connect();
        $db->table('migrations')->emptyTable();

        if (is_file($this->migrationFileTo)) {
            @unlink($this->migrationFileTo);
        }

        putenv('NO_COLOR');
        CLI::init();
    }

    public function testMigrateAllWithWithTwoNamespaces(): void
    {
        command('migrate --all');
        $this->resetStreamFilterBuffer();

        command('migrate:status');

        $result   = str_replace(PHP_EOL, "\n", $this->getStreamFilterBuffer());
        $result   = preg_replace('/\d{4}-\d\d-\d\d \d\d:\d\d:\d\d/', 'YYYY-MM-DD HH:MM:SS', $result);
        $expected = <<<'EOL'
            +---------------+-------------------+--------------------+-------+---------------------+-------+
            | Namespace     | Version           | Filename           | Group | Migrated On         | Batch |
            +---------------+-------------------+--------------------+-------+---------------------+-------+
            | App           | 2018-01-24-102301 | Some_migration     | tests | YYYY-MM-DD HH:MM:SS | 1     |
            | Tests\Support | 20160428212500    | Create_test_tables | tests | YYYY-MM-DD HH:MM:SS | 1     |
            +---------------+-------------------+--------------------+-------+---------------------+-------+


            EOL;
        $this->assertSame($expected, $result);
    }

    public function testMigrateWithWithTwoNamespaces(): void
    {
        command('migrate -n App');
        command('migrate -n Tests\\\\Support');
        $this->resetStreamFilterBuffer();

        command('migrate:status');

        $result   = str_replace(PHP_EOL, "\n", $this->getStreamFilterBuffer());
        $result   = preg_replace('/\d{4}-\d\d-\d\d \d\d:\d\d:\d\d/', 'YYYY-MM-DD HH:MM:SS', $result);
        $expected = <<<'EOL'
            +---------------+-------------------+--------------------+-------+---------------------+-------+
            | Namespace     | Version           | Filename           | Group | Migrated On         | Batch |
            +---------------+-------------------+--------------------+-------+---------------------+-------+
            | App           | 2018-01-24-102301 | Some_migration     | tests | YYYY-MM-DD HH:MM:SS | 1     |
            | Tests\Support | 20160428212500    | Create_test_tables | tests | YYYY-MM-DD HH:MM:SS | 2     |
            +---------------+-------------------+--------------------+-------+---------------------+-------+


            EOL;
        $this->assertSame($expected, $result);
    }
}
