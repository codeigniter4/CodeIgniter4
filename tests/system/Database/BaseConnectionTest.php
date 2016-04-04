<?php namespace CodeIgniter\Database;

use CodeIgniter\Database\MockConnection;

class BaseConnectionTest extends \CIUnitTestCase
{
	protected $options = [
		'DSN'          => '',
		'hostname'     => 'localhost',
		'username'     => 'first',
		'password'     => 'last',
		'database'     => 'dbname',
		'DBDriver'     => 'MockDriver',
		'DBPrefix'     => 'test_',
		'pConnect'     => true,
		'DBDebug'     => (ENVIRONMENT !== 'production'),
		'cacheOn'     => false,
		'cacheDir'     => 'my/cacheDir',
		'charset'      => 'utf8',
		'DBCollat'     => 'utf8_general_ci',
		'swapPre'      => '',
		'encrypt'      => false,
		'compress'     => false,
		'strictOn'     => true,
		'failover'     => [],
		'saveQueries' => true,
	];

	protected $failoverOptions = [
		'DSN'          => '',
		'hostname'     => 'localhost',
		'username'     => 'failover',
		'password'     => 'one',
		'database'     => 'failover',
		'DBDriver'     => 'MockDriver',
		'DBPrefix'     => 'test_',
		'pConnect'     => true,
		'DBDebug'     => (ENVIRONMENT !== 'production'),
		'cacheOn'     => false,
		'cacheDir'     => 'my/cacheDir',
		'charset'      => 'utf8',
		'DBCollat'     => 'utf8_general_ci',
		'swapPre'      => '',
		'encrypt'      => false,
		'compress'     => false,
		'strictOn'     => true,
		'failover'     => [],
		'saveQueries' => true,
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
		$this->assertSame(true, $db->pConnect);
		$this->assertSame(true, $db->DBDebug);
		$this->assertSame(false, $db->cacheOn);
		$this->assertSame('my/cacheDir', $db->cacheDir);
		$this->assertSame('utf8', $db->charset);
		$this->assertSame('utf8_general_ci', $db->DBCollat);
		$this->assertSame('', $db->swapPre);
		$this->assertSame(false, $db->encrypt);
		$this->assertSame(false, $db->compress);
		$this->assertSame(true, $db->strictOn);
		$this->assertSame([], $db->failover);
		$this->assertSame(true, $db->saveQueries);
	}
	
	//--------------------------------------------------------------------
	
	public function testConnectionThrowExceptionWhenCannotConnect() 
	{
	    $db = new MockConnection($this->options);
		
		$this->setExpectedException('CodeIgniter\DatabaseException', 'Unable to connect to the database.');
		
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
	 * @throws \CodeIgniter\DatabaseException
	 * @group single
	 */
	public function testCanConnectToFailoverWhenNoConnectionAvailable()
	{
		$options = $this->options;
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

		$this->assertTrue($db->getConnectStart() > $start);
		$this->assertTrue($db->getConnectDuration() > 0);
	}

	//--------------------------------------------------------------------


}
