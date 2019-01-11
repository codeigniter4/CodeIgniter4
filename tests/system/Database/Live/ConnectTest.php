<?php namespace CodeIgniter\Database\Live;

;

use CodeIgniter\Config\Config;
use CodeIgniter\Test\CIDatabaseTestCase;
use Config\Database;

class ConnectTest extends CIDatabaseTestCase
{
	protected $group1;

	protected $group2;

	protected function setUp()
	{
		parent::setUp();

		$config = config('Database');

		$this->group1 = $config->default;
		$this->group2 = $config->default;

		$this->group1['DBDriver'] = 'MySQLi';
		$this->group2['DBDriver'] = 'Postgre';
	}

	public function testConnectWithMultipleCustomGroups()
	{
		// We should have our test database connection already.
		$instances = $this->getPrivateProperty(Database::class, 'instances');
		$this->assertEquals(1, count($instances));

		$db1 = Database::connect($this->group1);
		$db2 = Database::connect($this->group2);

		$this->assertNotSame($db1, $db2);

		$instances = $this->getPrivateProperty(Database::class, 'instances');
		$this->assertEquals(3, count($instances));
	}

	public function testConnectReturnsProvidedConnection()
	{
		// This will be the tests database
		$db = Database::connect();
		$this->assertInstanceOf(\CodeIgniter\Database\SQLite3\Connection::class, $db);

		// Get an instance of the system's default db so we have something to test with.
		$db1 = Database::connect($this->group1);
		$this->assertEquals('MySQLi', $this->getPrivateProperty($db1, 'DBDriver'));

		// If a connection is passed into connect, it should simply be returned to us...
		$db2 = Database::connect($db1);
		$this->assertSame($db1, $db2);
	}

	public function testConnectWorksWithGroupName()
	{
		$db = Database::connect('tests');
		$this->assertInstanceOf(\CodeIgniter\Database\SQLite3\Connection::class, $db);

		$config                      = config('Database');
		$config->default['DBDriver'] = 'MySQLi';
		Config::injectMock('Database', $config);

		$db1 = Database::connect('default');
		$this->assertNotInstanceOf(\CodeIgniter\Database\SQLite3\Connection::class, $db1);
		$this->assertEquals('MySQLi', $this->getPrivateProperty($db1, 'DBDriver'));
	}
}
