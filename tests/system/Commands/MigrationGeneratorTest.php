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
use CodeIgniter\Test\Filters\CITestStreamFilter;

/**
 * @internal
 */
final class MigrationGeneratorTest extends CIUnitTestCase
{
    private $streamFilter;

    protected function setUp(): void
    {
        CITestStreamFilter::$buffer = '';

        $this->streamFilter = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter = stream_filter_append(STDERR, 'CITestStreamFilter');
    }

    protected function tearDown(): void
    {
        stream_filter_remove($this->streamFilter);

        $result = str_replace(["\033[0;32m", "\033[0m", "\n"], '', CITestStreamFilter::$buffer);
        $file   = str_replace('APPPATH' . DIRECTORY_SEPARATOR, APPPATH, trim(substr($result, 14)));
        if (is_file($file)) {
            unlink($file);
        }
    }

    public function testGenerateMigration()
    {
        command('make:migration database');
        $this->assertStringContainsString('_Database.php', CITestStreamFilter::$buffer);
    }

    public function testGenerateMigrationWithOptionSession()
    {
        command('make:migration -session');
        $this->assertStringContainsString('_CreateCiSessionsTable.php', CITestStreamFilter::$buffer);
    }

    public function testGenerateMigrationWithOptionTable()
    {
        command('make:migration -session -table logger');
        $this->assertStringContainsString('_CreateLoggerTable.php', CITestStreamFilter::$buffer);
    }

    public function testGenerateMigrationWithOptionSuffix()
    {
        command('make:migration database -suffix');
        $this->assertStringContainsString('_DatabaseMigration.php', CITestStreamFilter::$buffer);
    }
}
