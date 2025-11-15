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

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\OCI8\Connection as OCI8Connection;
use CodeIgniter\Database\SQLite3\Connection as SQLite3Connection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Database;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class CreateDatabaseTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    private BaseConnection $connection;

    protected function setUp(): void
    {
        $this->connection = Database::connect();

        parent::setUp();

        $this->dropDatabase();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->dropDatabase();
    }

    private function dropDatabase(): void
    {
        if ($this->connection instanceof SQLite3Connection) {
            $file = WRITEPATH . 'database.db';

            if (is_file($file)) {
                unlink($file);
            }
        } elseif (Database::utils('tests')->databaseExists('database')) {
            Database::forge()->dropDatabase('database');
        }
    }

    protected function getBuffer(): string
    {
        return $this->getStreamFilterBuffer();
    }

    public function testCreateDatabase(): void
    {
        if ($this->connection instanceof OCI8Connection) {
            $this->markTestSkipped('Needs to run on non-OCI8 drivers.');
        }

        command('db:create database');
        $this->assertStringContainsString('successfully created.', $this->getBuffer());
    }

    public function testSqliteDatabaseDuplicated(): void
    {
        if (! $this->connection instanceof SQLite3Connection) {
            $this->markTestSkipped('Needs to run on SQLite3.');
        }

        command('db:create database');
        $this->resetStreamFilterBuffer();

        command('db:create database --ext db');
        $this->assertStringContainsString('already exists.', $this->getBuffer());
    }

    public function testOtherDriverDuplicatedDatabase(): void
    {
        if ($this->connection instanceof SQLite3Connection || $this->connection instanceof OCI8Connection) {
            $this->markTestSkipped('Needs to run on non-SQLite3 and non-OCI8 drivers.');
        }

        command('db:create database');
        $this->resetStreamFilterBuffer();

        command('db:create database');
        $this->assertStringContainsString('Unable to create the specified database.', $this->getBuffer());
    }
}
