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

namespace CodeIgniter\Database\Live;

use CodeIgniter\Config\Factories;
use CodeIgniter\Database\SQLite3\Connection;
use CodeIgniter\Exceptions\RuntimeException;
use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('DatabaseLive')]
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

    public function testConnectWithMultipleCustomGroups(): void
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

    public function testConnectReturnsProvidedConnection(): void
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

    public function testConnectWorksWithGroupName(): void
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

    public function testConnectWithFailover(): void
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

    public function testNonSharedInstanceDoesNotAffectSharedInstances(): void
    {
        $firstSharedDb      = Database::connect('tests');
        $originalDebugValue = (bool) self::getPrivateProperty($firstSharedDb, 'DBDebug');

        $nonSharedDb = Database::connect('tests', false);
        self::setPrivateProperty($nonSharedDb, 'DBDebug', ! $originalDebugValue);

        $secondSharedDb = Database::connect('tests');

        $this->assertSame($firstSharedDb, $secondSharedDb);
        $this->assertNotSame($firstSharedDb, $nonSharedDb);

        $this->assertSame($originalDebugValue, self::getPrivateProperty($firstSharedDb, 'DBDebug'));
        $this->assertSame($originalDebugValue, self::getPrivateProperty($secondSharedDb, 'DBDebug'));
        $this->assertSame(! $originalDebugValue, self::getPrivateProperty($nonSharedDb, 'DBDebug'));
    }

    public function testTimezoneSetWithSpecificOffset(): void
    {
        $config             = $this->tests;
        $config['timezone'] = '+05:30';
        $driver             = $config['DBDriver'];

        if (in_array($driver, ['SQLite3', 'SQLSRV'], true)) {
            $this->markTestSkipped("Driver {$driver} does not support session timezone");
        }

        $db = Database::connect($config, false);

        $timezone = $this->getDatabaseTimezone($db, $driver);

        $this->assertSame('+05:30', $timezone);
    }

    public function testTimezoneSetWithNamedTimezone(): void
    {
        $config             = $this->tests;
        $config['timezone'] = 'America/New_York';
        $driver             = $config['DBDriver'];

        if (in_array($driver, ['SQLite3', 'SQLSRV'], true)) {
            $this->markTestSkipped("Driver {$driver} does not support session timezone");
        }

        $db = Database::connect($config, false);

        $timezone = $this->getDatabaseTimezone($db, $driver);

        // Named timezones are converted to offsets
        // America/New_York is either -05:00 (EST) or -04:00 (EDT)
        $this->assertContains($timezone, ['-05:00', '-04:00']);
    }

    public function testTimezoneAutoSyncWithAppTimezone(): void
    {
        $config             = $this->tests;
        $config['timezone'] = true;
        $driver             = $config['DBDriver'];

        if (in_array($driver, ['SQLite3', 'SQLSRV'], true)) {
            $this->markTestSkipped("Driver {$driver} does not support session timezone");
        }

        $db = Database::connect($config, false);

        $timezone = $this->getDatabaseTimezone($db, $driver);

        $appConfig      = config('App');
        $appTimezone    = $appConfig->appTimezone ?? 'UTC';
        $expectedOffset = $this->convertTimezoneToOffset($appTimezone);

        $this->assertSame($expectedOffset, $timezone);
    }

    /**
     * Helper method to get database timezone based on driver
     *
     * @param mixed $db
     */
    private function getDatabaseTimezone($db, string $driver): string
    {
        switch ($driver) {
            case 'MySQLi':
                $result = $db->query('SELECT @@session.time_zone as tz')->getRow();

                return $result->tz;

            case 'Postgre':
                $result = $db->query('SHOW TIME ZONE')->getRow();

                // PostgreSQL returns the timezone name, but we set it as offset
                return $result->timezone ?? $result->TimeZone;

            case 'OCI8':
                $result = $db->query('SELECT SESSIONTIMEZONE as tz FROM DUAL')->getRow();

                return $result->tz ?? $result->TZ;

            default:
                throw new RuntimeException("Unsupported driver: {$driver}");
        }
    }

    /**
     * Helper method to convert timezone to offset (mirrors BaseConnection logic)
     */
    private function convertTimezoneToOffset(string $timezone): string
    {
        if (preg_match('/^[+-]\d{2}:\d{2}$/', $timezone)) {
            return $timezone;
        }

        $time   = Time::now($timezone);
        $offset = $time->getOffset();

        $hours   = (int) ($offset / 3600);
        $minutes = abs((int) (($offset % 3600) / 60));

        return sprintf('%+03d:%02d', $hours, $minutes);
    }
}
