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

namespace CodeIgniter\Database;

use CodeIgniter\Database\Postgre\Connection as PostgreConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ReflectionHelper;
use CodeIgniter\Test\TestLogger;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;

/**
 * @internal
 */
#[Group('Others')]
final class ConfigTest extends CIUnitTestCase
{
    use ReflectionHelper;

    /**
     * @var array<string, mixed>
     */
    private array $group = [
        'DSN'      => '',
        'hostname' => 'localhost',
        'username' => 'first',
        'password' => 'last',
        'database' => 'dbname',
        'DBDriver' => 'MySQLi',
        'DBPrefix' => 'test_',
        'pConnect' => true,
        'DBDebug'  => true,
        'charset'  => 'utf8mb4',
        'DBCollat' => 'utf8mb4_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => true,
        'failover' => [],
        'port'     => 3306,
    ];

    /**
     * @var array<string, mixed>
     */
    private array $dsnGroup = [
        'DSN'      => 'MySQLi://user:pass@localhost:3306/dbname?DBPrefix=test_&pConnect=true&charset=latin1&DBCollat=latin1_swedish_ci',
        'hostname' => '',
        'username' => '',
        'password' => '',
        'database' => '',
        'DBDriver' => 'SQLite3',
        'DBPrefix' => 't_',
        'pConnect' => false,
        'DBDebug'  => true,
        'charset'  => 'utf8mb4',
        'DBCollat' => 'utf8mb4_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => true,
        'failover' => [],
        'port'     => 3306,
    ];

    /**
     * @var array<string, mixed>
     */
    private array $dsnGroupPostgre = [
        'DSN'      => 'Postgre://user:pass@localhost:5432/dbname?DBPrefix=test_&connect_timeout=5&sslmode=1',
        'hostname' => '',
        'username' => '',
        'password' => '',
        'database' => '',
        'DBDriver' => 'SQLite3',
        'DBPrefix' => 't_',
        'pConnect' => false,
        'DBDebug'  => true,
        'charset'  => 'utf8mb4',
        'DBCollat' => 'utf8mb4_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => true,
        'failover' => [],
        'port'     => 5432,
    ];

    /**
     * @var array<string, mixed>
     */
    private array $dsnGroupPostgreNative = [
        'DSN'      => 'pgsql:host=localhost;port=5432;dbname=database_name',
        'hostname' => '',
        'username' => '',
        'password' => '',
        'database' => '',
        'DBDriver' => 'Postgre',
        'DBPrefix' => 't_',
        'pConnect' => false,
        'DBDebug'  => true,
        'charset'  => 'utf8mb4',
        'DBCollat' => 'utf8mb4_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => true,
        'failover' => [],
        'port'     => 5432,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setPrivateProperty(TestLogger::class, 'op_logs', []);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->setPrivateProperty(Config::class, 'instances', []);
        $this->setPrivateProperty(TestLogger::class, 'op_logs', []);
    }

    public function testConnectionGroup(): void
    {
        $conn = Config::connect($this->group, false);
        $this->assertInstanceOf(BaseConnection::class, $conn);

        $this->assertSame($this->group['DSN'], $this->getPrivateProperty($conn, 'DSN'));
        $this->assertSame($this->group['hostname'], $this->getPrivateProperty($conn, 'hostname'));
        $this->assertSame($this->group['username'], $this->getPrivateProperty($conn, 'username'));
        $this->assertSame($this->group['password'], $this->getPrivateProperty($conn, 'password'));
        $this->assertSame($this->group['database'], $this->getPrivateProperty($conn, 'database'));
        $this->assertSame($this->group['port'], $this->getPrivateProperty($conn, 'port'));
        $this->assertSame($this->group['DBDriver'], $this->getPrivateProperty($conn, 'DBDriver'));
        $this->assertSame($this->group['DBPrefix'], $this->getPrivateProperty($conn, 'DBPrefix'));
        $this->assertSame($this->group['pConnect'], $this->getPrivateProperty($conn, 'pConnect'));
        $this->assertSame($this->group['charset'], $this->getPrivateProperty($conn, 'charset'));
        $this->assertSame($this->group['DBCollat'], $this->getPrivateProperty($conn, 'DBCollat'));
    }

    public function testConnectionGroupWithDSN(): void
    {
        $conn = Config::connect($this->dsnGroup, false);
        $this->assertInstanceOf(BaseConnection::class, $conn);

        $this->assertSame('', $this->getPrivateProperty($conn, 'DSN'));
        $this->assertSame('localhost', $this->getPrivateProperty($conn, 'hostname'));
        $this->assertSame('user', $this->getPrivateProperty($conn, 'username'));
        $this->assertSame('pass', $this->getPrivateProperty($conn, 'password'));
        $this->assertSame('dbname', $this->getPrivateProperty($conn, 'database'));
        $this->assertSame('3306', $this->getPrivateProperty($conn, 'port'));
        $this->assertSame('MySQLi', $this->getPrivateProperty($conn, 'DBDriver'));
        $this->assertSame('test_', $this->getPrivateProperty($conn, 'DBPrefix'));
        $this->assertTrue($this->getPrivateProperty($conn, 'pConnect'));
        $this->assertSame('latin1', $this->getPrivateProperty($conn, 'charset'));
        $this->assertSame('latin1_swedish_ci', $this->getPrivateProperty($conn, 'DBCollat'));
        $this->assertTrue($this->getPrivateProperty($conn, 'strictOn'));
        $this->assertSame([], $this->getPrivateProperty($conn, 'failover'));
    }

    public function testConnectionGroupWithDSNPostgre(): void
    {
        $conn = Config::connect($this->dsnGroupPostgre, false);
        $this->assertInstanceOf(PostgreConnection::class, $conn);

        $this->assertSame('', $this->getPrivateProperty($conn, 'DSN'));
        $this->assertSame('localhost', $this->getPrivateProperty($conn, 'hostname'));
        $this->assertSame('user', $this->getPrivateProperty($conn, 'username'));
        $this->assertSame('pass', $this->getPrivateProperty($conn, 'password'));
        $this->assertSame('dbname', $this->getPrivateProperty($conn, 'database'));
        $this->assertSame('5432', $this->getPrivateProperty($conn, 'port'));
        $this->assertSame('Postgre', $this->getPrivateProperty($conn, 'DBDriver'));
        $this->assertSame('test_', $this->getPrivateProperty($conn, 'DBPrefix'));
        $this->assertFalse($this->getPrivateProperty($conn, 'pConnect'));
        $this->assertSame('utf8mb4', $this->getPrivateProperty($conn, 'charset'));
        $this->assertSame('utf8mb4_general_ci', $this->getPrivateProperty($conn, 'DBCollat'));
        $this->assertTrue($this->getPrivateProperty($conn, 'strictOn'));
        $this->assertSame([], $this->getPrivateProperty($conn, 'failover'));
        $this->assertSame('5', $this->getPrivateProperty($conn, 'connect_timeout'));
        $this->assertSame('1', $this->getPrivateProperty($conn, 'sslmode'));

        $method = self::getPrivateMethodInvoker($conn, 'buildDSN');
        $method();

        $expected = "host=localhost port=5432 user=user password='pass' dbname=dbname connect_timeout='5' sslmode='1'";
        $this->assertSame($expected, $this->getPrivateProperty($conn, 'DSN'));
    }

    public function testConnectionGroupWithDSNPostgreNative(): void
    {
        $conn = Config::connect($this->dsnGroupPostgreNative, false);
        $this->assertInstanceOf(BaseConnection::class, $conn);

        $this->assertSame('pgsql:host=localhost;port=5432;dbname=database_name', $this->getPrivateProperty($conn, 'DSN'));
        $this->assertSame('', $this->getPrivateProperty($conn, 'hostname'));
        $this->assertSame('', $this->getPrivateProperty($conn, 'username'));
        $this->assertSame('', $this->getPrivateProperty($conn, 'password'));
        $this->assertSame('', $this->getPrivateProperty($conn, 'database'));
        $this->assertSame(5432, $this->getPrivateProperty($conn, 'port'));
        $this->assertSame('Postgre', $this->getPrivateProperty($conn, 'DBDriver'));
        $this->assertSame('t_', $this->getPrivateProperty($conn, 'DBPrefix'));
        $this->assertFalse($this->getPrivateProperty($conn, 'pConnect'));
        $this->assertSame('utf8mb4', $this->getPrivateProperty($conn, 'charset'));
        $this->assertSame('utf8mb4_general_ci', $this->getPrivateProperty($conn, 'DBCollat'));
        $this->assertTrue($this->getPrivateProperty($conn, 'strictOn'));
        $this->assertSame([], $this->getPrivateProperty($conn, 'failover'));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/7550
     */
    #[DataProvider('provideConvertDSN')]
    public function testConvertDSN(string $input, string $expected): void
    {
        // @TODO This format is for PDO_PGSQL.
        //      https://www.php.net/manual/en/ref.pdo-pgsql.connection.php
        //      Should deprecate?
        $this->dsnGroupPostgreNative['DSN'] = $input;
        $conn                               = Config::connect($this->dsnGroupPostgreNative, false);
        $this->assertInstanceOf(PostgreConnection::class, $conn);

        $method = self::getPrivateMethodInvoker($conn, 'convertDSN');
        $method();

        $this->assertSame($expected, $this->getPrivateProperty($conn, 'DSN'));
    }

    /**
     * @return iterable<int, array{string, string}>
     */
    public static function provideConvertDSN(): iterable
    {
        yield from [
            [
                'pgsql:host=localhost;port=5432;dbname=database_name;user=username;password=password',
                'host=localhost port=5432 dbname=database_name user=username password=password',
            ],
            [
                'pgsql:host=localhost;port=5432;dbname=database_name;user=username;password=we;port=we',
                'host=localhost port=5432 dbname=database_name user=username password=we;port=we',
            ],
            [
                'pgsql:host=localhost;port=5432;dbname=database_name',
                'host=localhost port=5432 dbname=database_name',
            ],
            [
                "pgsql:host=localhost;port=5432;dbname=database_name;options='--client_encoding=UTF8'",
                "host=localhost port=5432 dbname=database_name options='--client_encoding=UTF8'",
            ],
            [
                'pgsql:host=localhost;port=5432;dbname=database_name;something=stupid',
                'host=localhost port=5432 dbname=database_name;something=stupid',
            ],
        ];
    }

    public function testCleanupForWorkerModeRollsBackUncommittedTransactions(): void
    {
        $depth      = 3;
        $connection = $this->createConnectionMock($depth);
        $connection->expects($this->exactly($depth))
            ->method('transRollback')
            ->willReturnCallback(static function () use (&$depth): bool {
                $depth--;

                return true;
            });

        $this->setPrivateProperty(Config::class, 'instances', ['tests' => $connection]);

        Config::cleanupForWorkerMode();

        $this->assertSame(0, $depth);
        $this->assertFalse(TestLogger::didLog('error', 'Transaction cleanup aborted.', false));
    }

    public function testCleanupForWorkerModeStopsWhenRollbackFails(): void
    {
        $depth      = 2;
        $connection = $this->createConnectionMock($depth);
        $connection->expects($this->once())
            ->method('transRollback')
            ->willReturn(false);
        $connection->expects($this->once())
            ->method('resetTransStatus');

        $this->setPrivateProperty(Config::class, 'instances', ['tests' => $connection]);

        Config::cleanupForWorkerMode();

        $this->assertSame(2, $depth);
        $this->assertLogged('error', "Database: Failed to roll back an uncommitted transaction in database group 'tests'. Transaction cleanup aborted.");
    }

    public function testCleanupForWorkerModeStopsWhenRollbackMakesNoProgress(): void
    {
        $depth      = 2;
        $connection = $this->createConnectionMock($depth);
        $connection->expects($this->once())
            ->method('transRollback')
            ->willReturn(true);
        $connection->expects($this->once())
            ->method('resetTransStatus');

        $this->setPrivateProperty(Config::class, 'instances', ['tests' => $connection]);

        Config::cleanupForWorkerMode();

        $this->assertSame(2, $depth);
        $this->assertLogged('error', "Database: Failed to roll back an uncommitted transaction in database group 'tests'. Transaction cleanup aborted.");
    }

    public function testCleanupForWorkerModeStopsWhenRollbackThrows(): void
    {
        $depth      = 2;
        $connection = $this->createConnectionMock($depth);
        $connection->expects($this->once())
            ->method('transRollback')
            ->willThrowException(new RuntimeException('MySQL server has gone away'));
        $connection->expects($this->once())
            ->method('resetTransStatus');

        $this->setPrivateProperty(Config::class, 'instances', ['tests' => $connection]);

        Config::cleanupForWorkerMode();

        $this->assertSame(2, $depth);
        $this->assertLogged('error', "Database: Failed to roll back an uncommitted transaction in database group 'tests'. Transaction cleanup aborted: MySQL server has gone away");
    }

    /**
     * @param int $depth Current transaction depth reported by the connection.
     *
     * @return BaseConnection&MockObject
     */
    private function createConnectionMock(int &$depth): BaseConnection
    {
        $connection = $this->createMock(BaseConnection::class);
        $connection->method('__get')->willReturnCallback(static function (string $key) use (&$depth): ?int {
            return $key === 'transDepth' ? $depth : null;
        });
        $connection->method('resetTransStatus')->willReturnSelf();

        return $connection;
    }
}
