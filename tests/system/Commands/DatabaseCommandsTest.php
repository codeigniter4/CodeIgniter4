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
final class DatabaseCommandsTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function tearDown(): void
    {
        command('migrate:rollback');

        parent::tearDown();
    }

    protected function getBuffer(): string
    {
        return $this->getStreamFilterBuffer();
    }

    protected function clearBuffer(): void
    {
        $this->resetStreamFilterBuffer();
    }

    public function testMigrate(): void
    {
        command('migrate --all');
        $this->assertStringContainsString('Migrations complete.', $this->getBuffer());
        command('migrate:rollback');

        $this->clearBuffer();
        command('migrate -n Tests\\\\Support');
        $this->assertStringContainsString('Migrations complete.', $this->getBuffer());
    }

    public function testMigrateRollback(): void
    {
        command('migrate --all -g tests');
        $this->clearBuffer();

        command('migrate:rollback -g tests');
        $this->assertStringContainsString('Done rolling back migrations.', $this->getBuffer());
    }

    public function testMigrateRefresh(): void
    {
        command('migrate --all');
        $this->clearBuffer();

        command('migrate:refresh');
        $this->assertStringContainsString('Migrations complete.', $this->getBuffer());
    }

    public function testMigrateStatus(): void
    {
        command('migrate --all');
        $this->clearBuffer();

        command('migrate:status -g tests');
        $this->assertStringContainsString('Namespace', $this->getBuffer());
        $this->assertStringContainsString('Version', $this->getBuffer());
        $this->assertStringContainsString('Filename', $this->getBuffer());
    }

    public function testSeed(): void
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
