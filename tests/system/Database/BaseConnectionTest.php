<?php namespace CodeIgniter\Database;

use CodeIgniter\Test\Mock\MockConnection;

class BaseConnectionTest extends \CodeIgniter\Test\CIUnitTestCase
{
	protected $options = [
		'DSN'      => '',
		'hostname' => 'localhost',
		'username' => 'first',
		'password' => 'last',
		'database' => 'dbname',
		'DBDriver' => 'MockDriver',
		'DBPrefix' => 'test_',
		'pConnect' => true,
		'DBDebug'  => (ENVIRONMENT !== 'production'),
		'charset'  => 'utf8',
		'DBCollat' => 'utf8_general_ci',
		'swapPre'  => '',
		'encrypt'  => false,
		'compress' => false,
		'strictOn' => true,
		'failover' => [],
	];

	protected $failoverOptions = [
		'DSN'      => '',
		'hostname' => 'localhost',
		'username' => 'failover',
		'password' => 'one',
		'database' => 'failover',
		'DBDriver' => 'MockDriver',
		'DBPrefix' => 'test_',
		'pConnect' => true,
		'DBDebug'  => (ENVIRONMENT !== 'production'),
		'charset'  => 'utf8',
		'DBCollat' => 'utf8_general_ci',
		'swapPre'  => '',
		'encrypt'  => false,
		'compress' => false,
		'strictOn' => true,
		'failover' => [],
	];

	//--------------------------------------------------------------------

	public function testSavesConfigOptions()
	{
		$db = new MockConnection($this->options);

		$this->assertSame('localhost', $db->hostname);
		$this->assertSame('first', $db->username);
		$this->assertSame('last', $db->password);
		$this->assertSame('dbname', $db->database);
		$this->assertSame('MockDriver', $db->DBDriver);
		$this->assertTrue($db->pConnect);
		$this->assertTrue($db->DBDebug);
		$this->assertSame('utf8', $db->charset);
		$this->assertSame('utf8_general_ci', $db->DBCollat);
		$this->assertSame('', $db->swapPre);
		$this->assertFalse($db->encrypt);
		$this->assertFalse($db->compress);
		$this->assertTrue($db->strictOn);
		$this->assertSame([], $db->failover);
	}

	//--------------------------------------------------------------------

	public function testConnectionThrowExceptionWhenCannotConnect()
	{
		$db = new MockConnection($this->options);

		$this->expectException('\CodeIgniter\Database\Exceptions\DatabaseException');
		$this->expectExceptionMessage('Unable to connect to the database.');

		$db->shouldReturn('connect', false)
			->initialize();
	}

	//--------------------------------------------------------------------

	public function testCanConnectAndStoreConnection()
	{
		$db = new MockConnection($this->options);

		$db->shouldReturn('connect', 123)
			->initialize();

		$this->assertSame(123, $db->getConnection());
	}

	//--------------------------------------------------------------------

	/**
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 * @group  single
	 */
	public function testCanConnectToFailoverWhenNoConnectionAvailable()
	{
		$options             = $this->options;
		$options['failover'] = [$this->failoverOptions];

		$db = new MockConnection($options);

		$db->shouldReturn('connect', [false, 345])
		   ->initialize();

		$this->assertSame(345, $db->getConnection());
		$this->assertSame('failover', $db->username);
	}

	//--------------------------------------------------------------------

	public function testStoresConnectionTimings()
	{
		$start = microtime(true);

		$db = new MockConnection($this->options);

		$db->initialize();

		$this->assertGreaterThan($start, $db->getConnectStart());
		$this->assertGreaterThan(0.0, $db->getConnectDuration());
	}

	//--------------------------------------------------------------------

	public function testMagicIssetTrue()
	{
		$db = new MockConnection($this->options);

		$this->assertTrue(isset($db->charset));
	}

	//--------------------------------------------------------------------

	public function testMagicIssetFalse()
	{
		$db = new MockConnection($this->options);

		$this->assertFalse(isset($db->foobar));
	}

	//--------------------------------------------------------------------

	public function testMagicGet()
	{
		$db = new MockConnection($this->options);

		$this->assertEquals('utf8', $db->charset);
	}

	//--------------------------------------------------------------------

	public function testMagicGetMissing()
	{
		$db = new MockConnection($this->options);

		$this->assertNull($db->foobar);
	}
}
