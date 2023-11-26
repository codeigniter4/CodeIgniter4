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
 * @internal
 *
 * @group Others
 */
final class MigrationGeneratorTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function tearDown(): void
    {
        $result = str_replace(["\033[0;32m", "\033[0m", "\n"], '', $this->getStreamFilterBuffer());
        $file   = str_replace('APPPATH' . DIRECTORY_SEPARATOR, APPPATH, trim(substr($result, 14)));
        if (is_file($file)) {
            unlink($file);
        }
    }

    public function testGenerateMigration(): void
    {
        command('make:migration database');
        $this->assertStringContainsString('_Database.php', $this->getStreamFilterBuffer());
    }

    public function testGenerateMigrationWithOptionSession(): void
    {
        command('make:migration -session');
        $this->assertStringContainsString('_CreateCiSessionsTable.php', $this->getStreamFilterBuffer());
    }

    public function testGenerateMigrationWithOptionTable(): void
    {
        command('make:migration -session -table logger');
        $this->assertStringContainsString('_CreateLoggerTable.php', $this->getStreamFilterBuffer());
    }

    public function testGenerateMigrationWithOptionSuffix(): void
    {
        command('make:migration database -suffix');
        $this->assertStringContainsString('_DatabaseMigration.php', $this->getStreamFilterBuffer());
    }
}
