<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Live;

use CodeIgniter\Config\Factories;
use CodeIgniter\Database\SQLite3\Connection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class ConnectTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    private $group1;
    private $group2;
    private $tests;

    protected function setUp(): void
    {
        parent::setUp();

        $config = config('Database');

        $this->group1 = $config->default;
        $this->group2 = $config->default;
        $this->tests  = $config->tests;

        $this->group1['DBDriver'] = 'MySQLi';
        $this->group2['DBDriver'] = 'Postgre';
    }

    public function testConnectWithMultipleCustomGroups()
    {
        // We should have our test database connection already.
        $instances = $this->getPrivateProperty(Database::class, 'instances');
        $this->assertCount(1, $instances);

        $db1 = Database::connect($this->group1);
        $db2 = Database::connect($this->group2);

        $this->assertNotSame($db1, $db2);

        $instances = $this->getPrivateProperty(Database::class, 'instances');
        $this->assertCount(3, $instances);
    }

    public function testConnectReturnsProvidedConnection()
    {
        $config = config('Database');

        // This will be the tests database
        $db = Database::connect();
        $this->assertSame($config->tests['DBDriver'], $this->getPrivateProperty($db, 'DBDriver'));

        // Get an instance of the system's default db so we have something to test with.
        $db1 = Database::connect($this->group1);
        $this->assertSame('MySQLi', $this->getPrivateProperty($db1, 'DBDriver'));

        // If a connection is passed into connect, it should simply be returned to us...
        $db2 = Database::connect($db1);
        $this->assertSame($db1, $db2);
    }

    public function testConnectWorksWithGroupName()
    {
        $config = config('Database');

        $db = Database::connect('tests');
        $this->assertSame($config->tests['DBDriver'], $this->getPrivateProperty($db, 'DBDriver'));

        $config                      = config('Database');
        $config->default['DBDriver'] = 'MySQLi';
        Factories::injectMock('config', 'Database', $config);

        $db1 = Database::connect('default');
        $this->assertNotInstanceOf(Connection::class, $db1);
        $this->assertSame('MySQLi', $this->getPrivateProperty($db1, 'DBDriver'));
    }

    public function testConnectWithFailover()
    {
        $this->tests['failover'][] = $this->tests;
        unset($this->tests['failover'][0]['failover']);

        // Change main's DBPrefix
        $this->tests['DBPrefix'] = 'main_';

        if ($this->tests['DBDriver'] === 'SQLite3') {
            // Change main's database path to fail to connect
            $this->tests['database'] = '/does/not/exists/test.db';
        }

        $this->tests['username'] = 'wrong';

        $db1 = Database::connect($this->tests);

        $this->assertSame($this->tests['failover'][0]['DBPrefix'], $this->getPrivateProperty($db1, 'DBPrefix'));

        $this->assertGreaterThanOrEqual(0, count($db1->listTables()));
    }
}
