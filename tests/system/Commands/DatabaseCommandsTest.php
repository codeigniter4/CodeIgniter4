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
final class DatabaseCommandsTest extends CIUnitTestCase
{
    private $streamFilter;

    protected function setUp(): void
    {
        CITestStreamFilter::$buffer = '';

        $this->streamFilter = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter = stream_filter_append(STDERR, 'CITestStreamFilter');

        parent::setUp();
    }

    protected function tearDown(): void
    {
        command('migrate:rollback');
        stream_filter_remove($this->streamFilter);

        parent::tearDown();
    }

    protected function getBuffer(): string
    {
        return CITestStreamFilter::$buffer;
    }

    protected function clearBuffer(): void
    {
        CITestStreamFilter::$buffer = '';
    }

    public function testMigrate()
    {
        command('migrate --all');
        $this->assertStringContainsString('Migrations complete.', $this->getBuffer());
        command('migrate:rollback');

        $this->clearBuffer();
        command('migrate -n Tests\\\\Support');
        $this->assertStringContainsString('Migrations complete.', $this->getBuffer());
    }

    public function testMigrateRollback()
    {
        command('migrate --all -g tests');
        $this->clearBuffer();

        command('migrate:rollback -g tests');
        $this->assertStringContainsString('Done rolling back migrations.', $this->getBuffer());
    }

    public function testMigrateRefresh()
    {
        command('migrate --all');
        $this->clearBuffer();

        command('migrate:refresh');
        $this->assertStringContainsString('Migrations complete.', $this->getBuffer());
    }

    public function testMigrateStatus()
    {
        command('migrate --all');
        $this->clearBuffer();

        command('migrate:status -g tests');
        $this->assertStringContainsString('Namespace', $this->getBuffer());
        $this->assertStringContainsString('Version', $this->getBuffer());
        $this->assertStringContainsString('Filename', $this->getBuffer());
    }

    public function testSeed()
    {
        command('migrate --all');
        $this->clearBuffer();

        // use '\\\\' to prevent escaping
        command('db:seed Tests\\\\Support\\\\Database\\\\Seeds\\\\CITestSeeder');
        $this->assertStringContainsString('Seeded', $this->getBuffer());
        $this->clearBuffer();

        command('db:seed Foobar.php');
        $this->assertStringContainsString('The specified seeder is not a valid file:', $this->getBuffer());
    }
}
