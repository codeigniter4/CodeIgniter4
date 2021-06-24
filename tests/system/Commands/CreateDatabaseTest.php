<?php

namespace CodeIgniter\Commands;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\SQLite3\Connection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use Config\Database;

/**
 * @internal
 */
final class CreateDatabaseTest extends CIUnitTestCase
{
    protected $streamFilter;

    /**
     * @var BaseConnection
     */
    protected $connection;

    protected function setUp(): void
    {
        CITestStreamFilter::$buffer = '';

        $this->streamFilter = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter = stream_filter_append(STDERR, 'CITestStreamFilter');
        $this->connection   = Database::connect();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        stream_filter_remove($this->streamFilter);

        if ($this->connection instanceof Connection) {
            $file = WRITEPATH . 'foobar.db';

            if (file_exists($file)) {
                unlink($file);
            }
        } else {
            Database::forge()->dropDatabase('foobar');
        }

        parent::tearDown();
    }

    protected function getBuffer()
    {
        return CITestStreamFilter::$buffer;
    }

    public function testCreateDatabase()
    {
        command('db:create foobar');
        $this->assertStringContainsString('successfully created.', $this->getBuffer());
    }

    public function testSqliteDatabaseDuplicated()
    {
        if (! $this->connection instanceof Connection) {
            $this->markTestSkipped('Needs to run on SQLite3.');
        }

        command('db:create foobar');
        CITestStreamFilter::$buffer = '';

        command('db:create foobar --ext db');
        $this->assertStringContainsString('already exists.', $this->getBuffer());
    }

    public function testOtherDriverDuplicatedDatabase()
    {
        if ($this->connection instanceof Connection) {
            $this->markTestSkipped('Needs to run on non-SQLite3 drivers.');
        }

        command('db:create foobar');
        CITestStreamFilter::$buffer = '';

        command('db:create foobar');
        $this->assertStringContainsString('Unable to create the specified database.', $this->getBuffer());
    }
}
