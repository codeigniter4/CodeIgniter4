<?php
namespace CodeIgniter\Database;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Config;
use CodeIgniter\Test\ReflectionHelper;

class DatabaseConfig extends \CodeIgniter\Test\CIUnitTestCase
{
	use ReflectionHelper;

	protected $group = [
		'DSN'      => '',
		'hostname' => 'localhost',
		'username' => 'first',
		'password' => 'last',
		'database' => 'dbname',
		'DBDriver' => 'MySQLi',
		'DBPrefix' => 'test_',
		'pConnect' => true,
		'DBDebug'  => (ENVIRONMENT !== 'production'),
		'cacheOn'  => false,
		'cacheDir' => 'my/cacheDir',
		'charset'  => 'utf8',
		'DBCollat' => 'utf8_general_ci',
		'swapPre'  => '',
		'encrypt'  => false,
		'compress' => false,
		'strictOn' => true,
		'failover' => [],
		'port'     => 3306,
	];

	protected $dsnGroup = [
		'DSN'      => 'MySQLi://user:pass@localhost:3306/dbname?DBPrefix=test_&pConnect=true&charset=latin1&DBCollat=latin1_swedish_ci',
		'hostname' => '',
		'username' => '',
		'password' => '',
		'database' => '',
		'DBDriver' => 'SQLite3',
		'DBPrefix' => 't_',
		'pConnect' => false,
		'DBDebug'  => (ENVIRONMENT !== 'production'),
		'cacheOn'  => false,
		'cacheDir' => 'my/cacheDir',
		'charset'  => 'utf8',
		'DBCollat' => 'utf8_general_ci',
		'swapPre'  => '',
		'encrypt'  => false,
		'compress' => false,
		'strictOn' => true,
		'failover' => [],
		'port'     => 3306,
	];

	protected $dsnGroupPostgre = [
		'DSN'      => 'Postgre://user:pass@localhost:5432/dbname?DBPrefix=test_&connect_timeout=5&sslmode=1',
		'hostname' => '',
		'username' => '',
		'password' => '',
		'database' => '',
		'DBDriver' => 'SQLite3',
		'DBPrefix' => 't_',
		'pConnect' => false,
		'DBDebug'  => (ENVIRONMENT !== 'production'),
		'cacheOn'  => false,
		'cacheDir' => 'my/cacheDir',
		'charset'  => 'utf8',
		'DBCollat' => 'utf8_general_ci',
		'swapPre'  => '',
		'encrypt'  => false,
		'compress' => false,
		'strictOn' => true,
		'failover' => [],
		'port'     => 5432,
	];

	protected $dsnGroupPostgreNative = [
		'DSN'      => 'pgsql:host=localhost;port=5432;dbname=database_name',
		'hostname' => '',
		'username' => '',
		'password' => '',
		'database' => '',
		'DBDriver' => 'Postgre',
		'DBPrefix' => 't_',
		'pConnect' => false,
		'DBDebug'  => (ENVIRONMENT !== 'production'),
		'cacheOn'  => false,
		'cacheDir' => 'my/cacheDir',
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

	public function testConnectionGroup()
	{
		$conn = Config::connect($this->group, false);
		$this->assertInstanceOf(BaseConnection::class, $conn);

		$this->assertEquals($this->group['DSN'], $this->getPrivateProperty($conn, 'DSN'));
		$this->assertEquals($this->group['hostname'], $this->getPrivateProperty($conn, 'hostname'));
		$this->assertEquals($this->group['username'], $this->getPrivateProperty($conn, 'username'));
		$this->assertEquals($this->group['password'], $this->getPrivateProperty($conn, 'password'));
		$this->assertEquals($this->group['database'], $this->getPrivateProperty($conn, 'database'));
		$this->assertEquals($this->group['port'], $this->getPrivateProperty($conn, 'port'));
		$this->assertEquals($this->group['DBDriver'], $this->getPrivateProperty($conn, 'DBDriver'));
		$this->assertEquals($this->group['DBPrefix'], $this->getPrivateProperty($conn, 'DBPrefix'));
		$this->assertEquals($this->group['pConnect'], $this->getPrivateProperty($conn, 'pConnect'));
		$this->assertEquals($this->group['charset'], $this->getPrivateProperty($conn, 'charset'));
		$this->assertEquals($this->group['DBCollat'], $this->getPrivateProperty($conn, 'DBCollat'));
	}

	public function testConnectionGroupWithDSN()
	{
		$conn = Config::connect($this->dsnGroup, false);
		$this->assertInstanceOf(BaseConnection::class, $conn);

		$this->assertEquals('', $this->getPrivateProperty($conn, 'DSN'));
		$this->assertEquals('localhost', $this->getPrivateProperty($conn, 'hostname'));
		$this->assertEquals('user', $this->getPrivateProperty($conn, 'username'));
		$this->assertEquals('pass', $this->getPrivateProperty($conn, 'password'));
		$this->assertEquals('dbname', $this->getPrivateProperty($conn, 'database'));
		$this->assertEquals('3306', $this->getPrivateProperty($conn, 'port'));
		$this->assertEquals('MySQLi', $this->getPrivateProperty($conn, 'DBDriver'));
		$this->assertEquals('test_', $this->getPrivateProperty($conn, 'DBPrefix'));
		$this->assertEquals(true, $this->getPrivateProperty($conn, 'pConnect'));
		$this->assertEquals('latin1', $this->getPrivateProperty($conn, 'charset'));
		$this->assertEquals('latin1_swedish_ci', $this->getPrivateProperty($conn, 'DBCollat'));
		$this->assertEquals(true, $this->getPrivateProperty($conn, 'strictOn'));
		$this->assertEquals([], $this->getPrivateProperty($conn, 'failover'));
	}

	public function testConnectionGroupWithDSNPostgre()
	{
		$conn = Config::connect($this->dsnGroupPostgre, false);
		$this->assertInstanceOf(BaseConnection::class, $conn);

		$this->assertEquals('', $this->getPrivateProperty($conn, 'DSN'));
		$this->assertEquals('localhost', $this->getPrivateProperty($conn, 'hostname'));
		$this->assertEquals('user', $this->getPrivateProperty($conn, 'username'));
		$this->assertEquals('pass', $this->getPrivateProperty($conn, 'password'));
		$this->assertEquals('dbname', $this->getPrivateProperty($conn, 'database'));
		$this->assertEquals('5432', $this->getPrivateProperty($conn, 'port'));
		$this->assertEquals('Postgre', $this->getPrivateProperty($conn, 'DBDriver'));
		$this->assertEquals('test_', $this->getPrivateProperty($conn, 'DBPrefix'));
		$this->assertEquals(false, $this->getPrivateProperty($conn, 'pConnect'));
		$this->assertEquals('utf8', $this->getPrivateProperty($conn, 'charset'));
		$this->assertEquals('utf8_general_ci', $this->getPrivateProperty($conn, 'DBCollat'));
		$this->assertEquals(true, $this->getPrivateProperty($conn, 'strictOn'));
		$this->assertEquals([], $this->getPrivateProperty($conn, 'failover'));
		$this->assertEquals('5', $this->getPrivateProperty($conn, 'connect_timeout'));
		$this->assertEquals('1', $this->getPrivateProperty($conn, 'sslmode'));

		$method = $this->getPrivateMethodInvoker($conn, 'buildDSN');
		$method();

		$expected = "host=localhost port=5432 user=user password='pass' dbname=dbname connect_timeout='5' sslmode='1'";
		$this->assertEquals($expected, $this->getPrivateProperty($conn, 'DSN'));
	}

	public function testConnectionGroupWithDSNPostgreNative()
	{
		$conn = Config::connect($this->dsnGroupPostgreNative, false);
		$this->assertInstanceOf(BaseConnection::class, $conn);

		$this->assertEquals('pgsql:host=localhost;port=5432;dbname=database_name', $this->getPrivateProperty($conn, 'DSN'));
		$this->assertEquals('', $this->getPrivateProperty($conn, 'hostname'));
		$this->assertEquals('', $this->getPrivateProperty($conn, 'username'));
		$this->assertEquals('', $this->getPrivateProperty($conn, 'password'));
		$this->assertEquals('', $this->getPrivateProperty($conn, 'database'));
		$this->assertEquals('5432', $this->getPrivateProperty($conn, 'port'));
		$this->assertEquals('Postgre', $this->getPrivateProperty($conn, 'DBDriver'));
		$this->assertEquals('t_', $this->getPrivateProperty($conn, 'DBPrefix'));
		$this->assertEquals(false, $this->getPrivateProperty($conn, 'pConnect'));
		$this->assertEquals('utf8', $this->getPrivateProperty($conn, 'charset'));
		$this->assertEquals('utf8_general_ci', $this->getPrivateProperty($conn, 'DBCollat'));
		$this->assertEquals(true, $this->getPrivateProperty($conn, 'strictOn'));
		$this->assertEquals([], $this->getPrivateProperty($conn, 'failover'));
	}

}
