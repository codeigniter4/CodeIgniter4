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

use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\OCI8\Connection as OCI8Connection;
use CodeIgniter\Database\SQLite3\Connection as SQLite3Connection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockInputOutput;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Database;
use PHPUnit\Framework\Attributes\Group;

/**
 * @todo To figure out how to transfer this test to `tests/system/Commands/Database/` without breaking DatabaseLive group.
 *
 * @internal
 */
#[Group('DatabaseLive')]
final class CreateDatabaseTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    private BaseConnection $connection;

    protected function setUp(): void
    {
        $this->connection = Database::connect(null, false);

        parent::setUp();

        CLI::resetLastWrite();
        $this->dropDatabase();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        CLI::reset();
        $this->dropDatabase();
    }

    private function dropDatabase(): void
    {
        if ($this->connection instanceof SQLite3Connection) {
            $file = WRITEPATH . 'database.db';

            $this->closeDatabaseConnections();

            if (is_file($file)) {
                unlink($file);
            }

            return;
        }

        if (Database::utils('tests')->databaseExists('database')) {
            Database::forge()->dropDatabase('database');
        }

        $this->closeDatabaseConnections();
    }

    private function closeDatabaseConnections(): void
    {
        $this->connection->close();

        foreach (Database::getConnections() as $connection) {
            $connection->close();
        }

        $this->setPrivateProperty(Database::class, 'instances', []);
    }

    private function getUndecoratedBuffer(): string
    {
        return preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()) ?? '';
    }

    public function testCreateDatabase(): void
    {
        if ($this->connection instanceof OCI8Connection) {
            $this->markTestSkipped('Needs to run on non-OCI8 drivers.');
        }

        $name = $this->connection instanceof SQLite3Connection ? 'database.db' : 'database';

        command('db:create database');
        $this->assertSame(
            <<<EOT

                Database "{$name}" successfully created.

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }

    public function testSqliteDatabaseDuplicated(): void
    {
        if (! $this->connection instanceof SQLite3Connection) {
            $this->markTestSkipped('Needs to run on SQLite3.');
        }

        command('db:create database');
        $this->resetStreamFilterBuffer();
        CLI::resetLastWrite();

        $database = WRITEPATH . 'database.db';

        command('db:create database --ext db');
        $this->assertSame(
            <<<EOT

                Database "{$database}" already exists.

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }

    public function testOtherDriverDuplicatedDatabase(): void
    {
        if ($this->connection instanceof SQLite3Connection || $this->connection instanceof OCI8Connection) {
            $this->markTestSkipped('Needs to run on non-SQLite3 and non-OCI8 drivers.');
        }

        command('db:create database');
        $this->resetStreamFilterBuffer();

        command('db:create database');
        $this->assertStringContainsString('Unable to create the specified database.', $this->getUndecoratedBuffer());
    }

    public function testOtherDriverCreationFailsWithoutDebug(): void
    {
        if ($this->connection instanceof SQLite3Connection || $this->connection instanceof OCI8Connection) {
            $this->markTestSkipped('Needs to run on non-SQLite3 and non-OCI8 drivers.');
        }

        command('db:create database');
        $this->resetStreamFilterBuffer();

        $this->connection = Database::connect();
        $this->setPrivateProperty($this->connection, 'DBDebug', false);

        CLI::resetLastWrite();
        command('db:create database');
        $this->assertSame(
            <<<'EOT'

                Database creation failed.

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }

    public function testSqlitePromptsForInvalidExtension(): void
    {
        if (! $this->connection instanceof SQLite3Connection) {
            $this->markTestSkipped('Needs to run on SQLite3.');
        }

        $io = new MockInputOutput();
        $io->setInputs(['db']);
        CLI::setInputOutput($io);

        command('db:create database --ext txt');

        $this->assertSame(
            <<<'EOT'
                Please choose a valid file extension [db, sqlite]: db
                Database "database.db" successfully created.

                EOT,
            preg_replace('/\e\[[^m]+m/', '', $io->getOutput()) ?? '',
        );
    }

    public function testSqliteRejectsInvalidExtensionWhenNonInteractive(): void
    {
        if (! $this->connection instanceof SQLite3Connection) {
            $this->markTestSkipped('Needs to run on SQLite3.');
        }

        command('db:create database --ext txt --no-interaction');
        $this->assertSame(
            <<<'EOT'

                Invalid file extension "txt". Use either `db` or `sqlite`.

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }
}
