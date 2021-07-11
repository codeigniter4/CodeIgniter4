<?php

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;

/**
 * @internal
 */
final class SessionsCommandsTest extends CIUnitTestCase
{
    private $streamFilter;

    protected function setUp(): void
    {
        parent::setUp();

        CITestStreamFilter::$buffer = '';

        $this->streamFilter = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter = stream_filter_append(STDERR, 'CITestStreamFilter');
    }

    protected function tearDown(): void
    {
        stream_filter_remove($this->streamFilter);

        $result = str_replace(["\033[0;32m", "\033[0m", "\n"], '', CITestStreamFilter::$buffer);
        $file   = str_replace('APPPATH' . DIRECTORY_SEPARATOR, APPPATH, trim(substr($result, 14)));
        if (file_exists($file)) {
            unlink($file);
        }
    }

    public function testCreateMigrationCommand()
    {
        command('session:migration');

        // make sure we end up with a migration class in the right place
        // or at least that we claim to have done so
        // separate assertions avoid console color codes
        $this->assertStringContainsString('_CreateCiSessionsTable.php', CITestStreamFilter::$buffer);
    }

    public function testOverriddenCreateMigrationCommand()
    {
        command('session:migration -t mygoodies');

        // make sure we end up with a migration class in the right place
        $this->assertStringContainsString('_CreateMygoodiesTable.php', CITestStreamFilter::$buffer);
    }

    public function testCannotWriteFileOnCreateMigrationCommand()
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->markTestSkipped('chmod does not work as expected on Windows');
        }

        chmod(APPPATH . 'Database/Migrations', 0444);

        command('session:migration');
        $this->assertStringContainsString('Error while creating file:', CITestStreamFilter::$buffer);

        chmod(APPPATH . 'Database/Migrations', 0755);
    }
}
