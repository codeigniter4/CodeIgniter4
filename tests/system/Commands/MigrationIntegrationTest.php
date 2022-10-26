<?php

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

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class MigrationIntegrationTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    private string $migrationFileFrom = SUPPORTPATH . 'Database/Migrations/20160428212500_Create_test_tables.php';
    private string $migrationFileTo   = APPPATH . 'Database/Migrations/20160428212500_Create_test_tables.php';

    protected function setUp(): void
    {
        parent::setUp();

        if (! is_file($this->migrationFileFrom)) {
            $this->fail(clean_path($this->migrationFileFrom) . ' is not found.');
        }

        if (is_file($this->migrationFileTo)) {
            @unlink($this->migrationFileTo);
        }

        copy($this->migrationFileFrom, $this->migrationFileTo);

        $contents = file_get_contents($this->migrationFileTo);
        $contents = str_replace('namespace Tests\Support\Database\Migrations;', 'namespace App\Database\Migrations;', $contents);
        file_put_contents($this->migrationFileTo, $contents);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (is_file($this->migrationFileTo)) {
            @unlink($this->migrationFileTo);
        }
    }

    public function testMigrationWithRollbackHasSameNameFormat(): void
    {
        command('migrate -n App');
        $this->assertStringContainsString(
            '(App) 20160428212500_App\Database\Migrations\Migration_Create_test_tables',
            $this->getStreamFilterBuffer()
        );

        $this->resetStreamFilterBuffer();

        command('migrate:rollback -n App');
        $this->assertStringContainsString(
            '(App) 20160428212500_App\Database\Migrations\Migration_Create_test_tables',
            $this->getStreamFilterBuffer()
        );
    }
}
