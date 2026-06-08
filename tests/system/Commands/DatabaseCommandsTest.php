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

use CodeIgniter\Database\MigrationRunner;
use CodeIgniter\EnvironmentDetector;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Services;
use PHPUnit\Framework\Attributes\Group;

/**
 * @todo To figure out how to transfer this test to `tests/system/Commands/Database/` without breaking DatabaseLive group.
 *
 * @internal
 */
#[Group('DatabaseLive')]
final class DatabaseCommandsTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use StreamFilterTrait;

    protected function tearDown(): void
    {
        command('migrate:rollback');

        parent::tearDown();
    }

    public function testMigrate(): void
    {
        command('migrate --all');
        $this->assertStringContainsString('Migrations complete.', $this->getStreamFilterBuffer());
        command('migrate:rollback');

        $this->resetStreamFilterBuffer();
        command('migrate -n Tests\\\\Support');
        $this->assertStringContainsString('Migrations complete.', $this->getStreamFilterBuffer());
    }

    public function testMigrateRollbackValidBatchNumber(): void
    {
        command('migrate --all');
        $this->resetStreamFilterBuffer();

        command('migrate:rollback -b 1');
        $this->assertStringContainsString('Done rolling back migrations.', $this->getStreamFilterBuffer());
    }

    public function testMigrateRollbackInvalidBatchNumber(): void
    {
        command('migrate --all');
        $this->resetStreamFilterBuffer();

        command('migrate:rollback -b x');
        $this->assertStringContainsString('Invalid batch number: x', $this->getStreamFilterBuffer());
    }

    public function testMigrateRollback(): void
    {
        command('migrate --all -g tests');
        $this->resetStreamFilterBuffer();

        command('migrate:rollback');
        $this->assertStringContainsString('Done rolling back migrations.', $this->getStreamFilterBuffer());
    }

    public function testMigrateRefresh(): void
    {
        command('migrate --all');
        $this->resetStreamFilterBuffer();

        command('migrate:refresh');
        $this->assertStringContainsString('Migrations complete.', $this->getStreamFilterBuffer());
    }

    public function testMigrateStatus(): void
    {
        command('migrate --all');
        $this->resetStreamFilterBuffer();

        command('migrate:status -g tests');
        $this->assertStringContainsString('Namespace', $this->getStreamFilterBuffer());
        $this->assertStringContainsString('Version', $this->getStreamFilterBuffer());
        $this->assertStringContainsString('Filename', $this->getStreamFilterBuffer());
    }

    public function testMigrateAbortsWhenMigrationsDisabled(): void
    {
        config('Migrations')->enabled = false;
        Services::resetSingle('migrations');

        command('migrate');
        $this->assertStringContainsString('Migrations have been loaded but are disabled', $this->getStreamFilterBuffer());

        config('Migrations')->enabled = true;
        Services::resetSingle('migrations');
    }

    public function testMigrateRollbackAbortsWhenMigrationsDisabled(): void
    {
        config('Migrations')->enabled = false;
        Services::resetSingle('migrations');

        command('migrate:rollback');
        $this->assertStringContainsString('Migrations have been loaded but are disabled', $this->getStreamFilterBuffer());

        config('Migrations')->enabled = true;
        Services::resetSingle('migrations');
    }

    public function testMigrateRollbackAbortsInProductionWithoutForce(): void
    {
        Services::injectMock('environment', new EnvironmentDetector('production'));

        $exitCode = service('commands')->runCommand('migrate:rollback', [], ['no-interaction' => null]);
        $this->assertSame(EXIT_ERROR, $exitCode);

        Services::resetSingle('environment');
    }

    public function testMigrateRefreshAbortsInProductionWithoutForce(): void
    {
        Services::injectMock('environment', new EnvironmentDetector('production'));

        $exitCode = service('commands')->runCommand('migrate:refresh', [], ['no-interaction' => null]);
        $this->assertSame(EXIT_ERROR, $exitCode);

        Services::resetSingle('environment');
    }

    public function testMigrateRefreshForwardsNamespaceGroupAndForceOptions(): void
    {
        command('migrate --all');
        $this->resetStreamFilterBuffer();

        command('migrate:refresh -n Tests\\\\Support -g tests -f');
        $this->assertStringContainsString('Migrations complete.', $this->getStreamFilterBuffer());
    }

    public function testMigrateRefreshForwardsAllNamespacesOption(): void
    {
        command('migrate --all');
        $this->resetStreamFilterBuffer();

        command('migrate:refresh --all');
        $this->assertStringContainsString('Migrations complete.', $this->getStreamFilterBuffer());
    }

    public function testMigrateReportsGeneralFaultWhenLatestFails(): void
    {
        $runner = $this->createMock(MigrationRunner::class);
        $runner->method('latest')->willReturn(false);
        $runner->method('getCliMessages')->willReturn([]);
        Services::injectMock('migrations', $runner);

        command('migrate');
        $this->assertStringContainsString(lang('Migrations.generalFault'), $this->getStreamFilterBuffer());

        Services::resetSingle('migrations');
    }

    public function testMigrateRollbackReportsGeneralFaultWhenRegressFails(): void
    {
        $runner = $this->createMock(MigrationRunner::class);
        $runner->method('getLastBatch')->willReturn(1);
        $runner->method('regress')->willReturn(false);
        $runner->method('getCliMessages')->willReturn([]);
        Services::injectMock('migrations', $runner);

        command('migrate:rollback');
        $this->assertStringContainsString(lang('Migrations.generalFault'), $this->getStreamFilterBuffer());

        Services::resetSingle('migrations');
    }

    public function testMigrateStatusReportsNoneFoundWhenNoMigrationsExist(): void
    {
        // A non-testing environment makes the command ignore the Tests\Support namespace.
        Services::injectMock('environment', new EnvironmentDetector('production'));

        $runner = $this->createMock(MigrationRunner::class);
        $runner->method('findNamespaceMigrations')->willReturn([]);
        Services::injectMock('migrations', $runner);

        command('migrate:status');
        $this->assertStringContainsString(lang('Migrations.noneFound'), $this->getStreamFilterBuffer());

        Services::resetSingle('migrations');
        Services::resetSingle('environment');
    }

    public function testSeed(): void
    {
        command('migrate --all');
        $this->resetStreamFilterBuffer();

        // use '\\\\' to prevent escaping
        command('db:seed Tests\\\\Support\\\\Database\\\\Seeds\\\\CITestSeeder');
        $this->assertStringContainsString('Seeded', $this->getStreamFilterBuffer());
        $this->resetStreamFilterBuffer();

        command('db:seed Foobar.php');
        $this->assertStringContainsString('The specified seeder is not a valid file:', $this->getStreamFilterBuffer());
    }
}
