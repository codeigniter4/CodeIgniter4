<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ReflectionHelper;

/**
 * @internal
 *
 * @group Others
 */
final class ConfigTest extends CIUnitTestCase
{
    use ReflectionHelper;

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
        'charset'  => 'utf8',
        'DBCollat' => 'utf8_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => true,
        'failover' => [],
        'port'     => 3306,
    ];
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
        'charset'  => 'utf8',
        'DBCollat' => 'utf8_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => true,
        'failover' => [],
        'port'     => 3306,
    ];
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
        'charset'  => 'utf8',
        'DBCollat' => 'utf8_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => true,
        'failover' => [],
        'port'     => 5432,
    ];
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
        'charset'  => 'utf8',
        'DBCollat' => 'utf8_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => true,
        'failover' => [],
        'port'     => 5432,
    ];

    protected function tearDown(): void
    {
        $this->setPrivateProperty(Config::class, 'instances', []);
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
        $this->assertInstanceOf(BaseConnection::class, $conn);

        $this->assertSame('', $this->getPrivateProperty($conn, 'DSN'));
        $this->assertSame('localhost', $this->getPrivateProperty($conn, 'hostname'));
        $this->assertSame('user', $this->getPrivateProperty($conn, 'username'));
        $this->assertSame('pass', $this->getPrivateProperty($conn, 'password'));
        $this->assertSame('dbname', $this->getPrivateProperty($conn, 'database'));
        $this->assertSame('5432', $this->getPrivateProperty($conn, 'port'));
        $this->assertSame('Postgre', $this->getPrivateProperty($conn, 'DBDriver'));
        $this->assertSame('test_', $this->getPrivateProperty($conn, 'DBPrefix'));
        $this->assertFalse($this->getPrivateProperty($conn, 'pConnect'));
        $this->assertSame('utf8', $this->getPrivateProperty($conn, 'charset'));
        $this->assertSame('utf8_general_ci', $this->getPrivateProperty($conn, 'DBCollat'));
        $this->assertTrue($this->getPrivateProperty($conn, 'strictOn'));
        $this->assertSame([], $this->getPrivateProperty($conn, 'failover'));
        $this->assertSame('5', $this->getPrivateProperty($conn, 'connect_timeout'));
        $this->assertSame('1', $this->getPrivateProperty($conn, 'sslmode'));

        $method = $this->getPrivateMethodInvoker($conn, 'buildDSN');
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
        $this->assertSame('utf8', $this->getPrivateProperty($conn, 'charset'));
        $this->assertSame('utf8_general_ci', $this->getPrivateProperty($conn, 'DBCollat'));
        $this->assertTrue($this->getPrivateProperty($conn, 'strictOn'));
        $this->assertSame([], $this->getPrivateProperty($conn, 'failover'));
    }

    /**
     * @dataProvider provideConvertDSN
     *
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/7550
     */
    public function testConvertDSN(string $input, string $expected): void
    {
        $this->dsnGroupPostgreNative['DSN'] = $input;
        $conn                               = Config::connect($this->dsnGroupPostgreNative, false);
        $this->assertInstanceOf(BaseConnection::class, $conn);

        $method = $this->getPrivateMethodInvoker($conn, 'convertDSN');
        $method();

        $this->assertSame($expected, $this->getPrivateProperty($conn, 'DSN'));
    }

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
}
