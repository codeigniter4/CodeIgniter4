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

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Database as DatabaseFactory;
use CodeIgniter\Database\OCI8\Connection as OCI8Connection;
use CodeIgniter\Database\SQLite3\Connection as SQLite3Connection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use Config\Database;

/**
 * @internal
 */
final class CreateDatabaseTest extends CIUnitTestCase
{
    private $streamFilter;
    private BaseConnection $connection;

    protected function setUp(): void
    {
        CITestStreamFilter::$buffer = '';

        $this->streamFilter = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter = stream_filter_append(STDERR, 'CITestStreamFilter');
        $this->connection   = Database::connect();

        parent::setUp();

        if ($this->connection instanceof SQLite3Connection) {
            $file = WRITEPATH . 'foobar.db';
            if (is_file($file)) {
                unlink($file);
            }
        } else {
            $util = (new DatabaseFactory())->loadUtils($this->connection);

            if ($util->databaseExists('foobar')) {
                Database::forge()->dropDatabase('foobar');
            }
        }
    }

    protected function tearDown(): void
    {
        stream_filter_remove($this->streamFilter);

        parent::tearDown();
    }

    protected function getBuffer()
    {
        return CITestStreamFilter::$buffer;
    }

    public function testCreateDatabase()
    {
        if ($this->connection instanceof OCI8Connection) {
            $this->markTestSkipped('Needs to run on non-OCI8 drivers.');
        }

        command('db:create foobar');
        $this->assertStringContainsString('successfully created.', $this->getBuffer());
    }

    public function testSqliteDatabaseDuplicated()
    {
        if (! $this->connection instanceof SQLite3Connection) {
            $this->markTestSkipped('Needs to run on SQLite3.');
        }

        command('db:create foobar');
        CITestStreamFilter::$buffer = '';

        command('db:create foobar --ext db');
        $this->assertStringContainsString('already exists.', $this->getBuffer());
    }

    public function testOtherDriverDuplicatedDatabase()
    {
        if ($this->connection instanceof SQLite3Connection || $this->connection instanceof OCI8Connection) {
            $this->markTestSkipped('Needs to run on non-SQLite3 and non-OCI8 drivers.');
        }

        command('db:create foobar');
        CITestStreamFilter::$buffer = '';

        command('db:create foobar');
        $this->assertStringContainsString('Unable to create the specified database.', $this->getBuffer());
    }
}
