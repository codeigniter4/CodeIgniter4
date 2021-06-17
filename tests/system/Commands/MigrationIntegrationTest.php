<?php

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;

/**
 * @internal
 */
final class MigrationIntegrationTest extends CIUnitTestCase
{
    private $streamFilter;

    private $migrationFileFrom = SUPPORTPATH . 'Database/Migrations/20160428212500_Create_test_tables.php';

    private $migrationFileTo = APPPATH . 'Database/Migrations/20160428212500_Create_test_tables.php';

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

        CITestStreamFilter::$buffer = '';

        $this->streamFilter = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter = stream_filter_append(STDERR, 'CITestStreamFilter');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (is_file($this->migrationFileTo)) {
            @unlink($this->migrationFileTo);
        }

        stream_filter_remove($this->streamFilter);
    }

    /**
     * @runTestsInSeparateProcesses
     */
    public function testMigrationWithRollbackHasSameNameFormat(): void
    {
        command('migrate -n App');
        $this->assertStringContainsString(
            '(App) 20160428212500_App\Database\Migrations\Migration_Create_test_tables',
            CITestStreamFilter::$buffer
        );

        CITestStreamFilter::$buffer = '';

        command('migrate:rollback -n App');
        $this->assertStringContainsString(
            '(App) 20160428212500_App\Database\Migrations\Migration_Create_test_tables',
            CITestStreamFilter::$buffer
        );
    }
}
