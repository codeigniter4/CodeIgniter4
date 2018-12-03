<?php namespace CodeIgniter\Session;

use Config\Logger;
use Tests\Support\Log\TestLogger;
use Tests\Support\Session\MockSession;
use CodeIgniter\Session\Handlers\FileHandler;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState         disabled
 */
class SessionTest extends \CIUnitTestCase
{
	protected function setUp()
	{
		parent::setUp();

		$_COOKIE  = [];
		$_SESSION = [];
	}

	public function tearDown()
	{
	}

	protected function getInstance($options = [])
	{
		$defaults = [
			'sessionDriver'            => 'CodeIgniter\Session\Handlers\FileHandler',
			'sessionCookieName'        => 'ci_session',
			'sessionExpiration'        => 7200,
			'sessionSavePath'          => null,
			'sessionMatchIP'           => false,
			'sessionTimeToUpdate'      => 300,
			'sessionRegenerateDestroy' => false,
			'cookieDomain'             => '',
			'cookiePrefix'             => '',
			'cookiePath'               => '/',
			'cookieSecure'             => false,
		];

		$config = array_merge($defaults, $options);
		$config = (object) $config;

		$session = new MockSession(new FileHandler($config, '127.0.0.1'), $config);
		$session->setLogger(new TestLogger(new Logger()));

		return $session;
	}

	public function testSessionSetsRegenerateTime()
	{
		$session = $this->getInstance();
		$session->start();

		$this->assertTrue(isset($_SESSION['__ci_last_regenerate']) && ! empty($_SESSION['__ci_last_regenerate']));
	}

	public function testWillRegenerateSessionAutomatically()
	{
		$session = $this->getInstance();

		$time                             = time() - 400;
		$_SESSION['__ci_last_regenerate'] = $time;
		$session->start();

		$this->assertTrue($session->didRegenerate);
		$this->assertGreaterThan($time + 90, $_SESSION['__ci_last_regenerate']);
	}

	public function testCanSetSingleValue()
	{
		$session = $this->getInstance();
		$session->start();

		$session->set('foo', 'bar');

		$this->assertEquals('bar', $_SESSION['foo']);
	}

	public function testCanSetArray()
	{
		$session = $this->getInstance();
		$session->start();

		$session->set([
			'foo' => 'bar',
			'bar' => 'baz',
		]);

		$this->assertEquals('bar', $_SESSION['foo']);
		$this->assertEquals('baz', $_SESSION['bar']);
		$this->assertArrayNotHasKey('__ci_vars', $_SESSION);
	}

	// Reference: https://github.com/codeigniter4/CodeIgniter4/issues/1492
	public function testCanSerializeArray()
	{
		$session = $this->getInstance();
		$session->start();

		$locations = [
			'AB' => 'Alberta',
			'BC' => 'British Columbia',
			'SK' => 'Saskatchewan',
		];
		$session->set(['_ci_old_input' => ['location' => $locations]]);

		$this->assertEquals($locations, $session->get('_ci_old_input')['location']);
	}

	public function testGetSimpleKey()
	{
		$session = $this->getInstance();
		$session->start();

		$session->set('foo', 'bar');

		$this->assertEquals('bar', $session->get('foo'));
	}

	public function testGetReturnsNullWhenNotFound()
	{
		$_SESSION = [];

		$session = $this->getInstance();
		$session->start();

		$this->assertNull($session->get('foo'));
	}

	public function testGetReturnsAllWithNoKeys()
	{
		$_SESSION = [
			'foo' => 'bar',
			'bar' => 'baz',
		];

		$session = $this->getInstance();
		$session->start();

		$result = $session->get();

		$this->assertTrue(array_key_exists('foo', $result));
		$this->assertTrue(array_key_exists('bar', $result));
	}

	public function testGetAsProperty()
	{
		$session = $this->getInstance();
		$session->start();

		$session->set('foo', 'bar');

		$this->assertEquals('bar', $session->foo);
	}

	public function testGetAsNormal()
	{
		$session = $this->getInstance();
		$session->start();

		$session->set('foo', 'bar');

		$this->assertEquals('bar', $_SESSION['foo']);
	}

	public function testHasReturnsTrueOnSuccess()
	{
		$session = $this->getInstance();
		$session->start();

		$_SESSION['foo'] = 'bar';

		$this->assertTrue($session->has('foo'));
	}

	public function testHasReturnsFalseOnNotFound()
	{
		$session = $this->getInstance();
		$session->start();

		$_SESSION['foo'] = 'bar';

		$this->assertFalse($session->has('bar'));
	}

	public function testPushNewValueIntoArraySessionValue()
	{
		$session = $this->getInstance();
		$session->start();

		$session->set('hobbies', ['cooking' => 'baking']);
		$session->push('hobbies', ['sport' => 'tennis']);

		$this->assertEquals([
			'cooking' => 'baking',
			'sport'   => 'tennis',
		],
			$session->get('hobbies')
		);
	}

	public function testRemoveActuallyRemoves()
	{
		$session = $this->getInstance();
		$session->start();

		$_SESSION['foo'] = 'bar';
		$session->remove('foo');

		$this->assertArrayNotHasKey('foo', $_SESSION);
		$this->assertFalse($session->has('foo'));
	}

	public function testHasReturnsCanRemoveArray()
	{
		$session = $this->getInstance();
		$session->start();

		$_SESSION = [
			'foo' => 'bar',
			'bar' => 'baz',
		];

		$this->assertTrue($session->has('foo'));

		$session->remove(['foo', 'bar']);

		$this->assertArrayNotHasKey('foo', $_SESSION);
		$this->assertArrayNotHasKey('bar', $_SESSION);
	}

	public function testSetMagicMethod()
	{
		$session = $this->getInstance();
		$session->start();

		$session->foo = 'bar';

		$this->assertArrayHasKey('foo', $_SESSION);
		$this->assertEquals('bar', $_SESSION['foo']);
	}

	public function testCanFlashData()
	{
		$session = $this->getInstance();
		$session->start();

		$session->setFlashdata('foo', 'bar');

		$this->assertTrue($session->has('foo'));
		$this->assertEquals('new', $_SESSION['__ci_vars']['foo']);

		// Should reset the 'new' to 'old'
		$session->start();

		$this->assertTrue($session->has('foo'));
		$this->assertEquals('old', $_SESSION['__ci_vars']['foo']);

		// Should no longer be available
		$session->start();

		$this->assertFalse($session->has('foo'));
	}

	public function testCanFlashArray()
	{
		$session = $this->getInstance();
		$session->start();

		$session->setFlashdata([
			'foo' => 'bar',
			'bar' => 'baz',
		]);

		$this->assertTrue($session->has('foo'));
		$this->assertEquals('new', $_SESSION['__ci_vars']['foo']);
		$this->assertTrue($session->has('bar'));
		$this->assertEquals('new', $_SESSION['__ci_vars']['bar']);
	}

	public function testKeepFlashData()
	{
		$session = $this->getInstance();
		$session->start();

		$session->setFlashdata('foo', 'bar');

		$this->assertTrue($session->has('foo'));
		$this->assertEquals('new', $_SESSION['__ci_vars']['foo']);

		// Should reset the 'new' to 'old'
		$session->start();

		$this->assertTrue($session->has('foo'));
		$this->assertEquals('old', $_SESSION['__ci_vars']['foo']);

		$session->keepFlashdata('foo');

		$this->assertEquals('new', $_SESSION['__ci_vars']['foo']);

		// Should no longer be available
		$session->start();

		$this->assertTrue($session->has('foo'));
		$this->assertEquals('old', $_SESSION['__ci_vars']['foo']);
	}

	public function testUnmarkFlashDataRemovesData()
	{
		$session = $this->getInstance();
		$session->start();

		$session->setFlashdata('foo', 'bar');
		$session->set('bar', 'baz');

		$this->assertTrue($session->has('foo'));
		$this->assertArrayHasKey('foo', $_SESSION['__ci_vars']);

		$session->unmarkFlashdata('foo');

		// Should still be here
		$this->assertTrue($session->has('foo'));
		// but no longer marked as flash
		$this->assertFalse(isset($_SESSION['__ci_vars']['foo']));
	}

	public function testGetFlashKeysOnlyReturnsFlashKeys()
	{
		$session = $this->getInstance();
		$session->start();

		$session->setFlashdata('foo', 'bar');
		$session->set('bar', 'baz');

		$keys = $session->getFlashKeys();

		$this->assertContains('foo', $keys);
		$this->assertNotContains('bar', $keys);
	}

	public function testSetTempDataWorks()
	{
		$session = $this->getInstance();
		$session->start();

		$session->setTempdata('foo', 'bar', 300);
		$this->assertGreaterThanOrEqual($_SESSION['__ci_vars']['foo'], time() + 300);
	}

	public function testSetTempDataArrayMultiTTL()
	{
		$session = $this->getInstance();
		$session->start();

		$time = time();

		$session->setTempdata([
			'foo' => 300,
			'bar' => 400,
			'baz' => 100,
		]);

		$this->assertLessThanOrEqual($_SESSION['__ci_vars']['foo'], $time + 300);
		$this->assertLessThanOrEqual($_SESSION['__ci_vars']['bar'], $time + 400);
		$this->assertLessThanOrEqual($_SESSION['__ci_vars']['baz'], $time + 100);
	}

	public function testSetTempDataArraySingleTTL()
	{
		$session = $this->getInstance();
		$session->start();

		$time = time();

		$session->setTempdata(['foo', 'bar', 'baz'], null, 200);

		$this->assertLessThanOrEqual($_SESSION['__ci_vars']['foo'], $time + 200);
		$this->assertLessThanOrEqual($_SESSION['__ci_vars']['bar'], $time + 200);
		$this->assertLessThanOrEqual($_SESSION['__ci_vars']['baz'], $time + 200);
	}

	/**
	 * @group single
	 */
	public function testGetTestDataReturnsAll()
	{
		$session = $this->getInstance();
		$session->start();

		$data = [
			'foo' => 'bar',
			'bar' => 'baz',
		];

		$session->setTempdata($data);
		$session->set('baz', 'ballywhoo');

		$this->assertEquals($data, $session->getTempdata());
	}

	public function testGetTestDataReturnsSingle()
	{
		$session = $this->getInstance();
		$session->start();

		$data = [
			'foo' => 'bar',
			'bar' => 'baz',
		];

		$session->setTempdata($data);

		$this->assertEquals('bar', $session->getTempdata('foo'));
	}

	public function testRemoveTempDataActuallyDeletes()
	{
		$session = $this->getInstance();
		$session->start();

		$data = [
			'foo' => 'bar',
			'bar' => 'baz',
		];

		$session->setTempdata($data);
		$session->removeTempdata('foo');

		$this->assertEquals(['bar' => 'baz'], $session->getTempdata());
	}

	public function testUnMarkTempDataSingle()
	{
		$session = $this->getInstance();
		$session->start();

		$data = [
			'foo' => 'bar',
			'bar' => 'baz',
		];

		$session->setTempdata($data);
		$session->unmarkTempdata('foo');

		$this->assertEquals(['bar' => 'baz'], $session->getTempdata());
	}

	public function testUnMarkTempDataArray()
	{
		$session = $this->getInstance();
		$session->start();

		$data = [
			'foo' => 'bar',
			'bar' => 'baz',
		];

		$session->setTempdata($data);
		$session->unmarkTempdata(['foo', 'bar']);

		$this->assertEquals([], $session->getTempdata());
	}

	public function testGetTempdataKeys()
	{
		$session = $this->getInstance();
		$session->start();

		$data = [
			'foo' => 'bar',
			'bar' => 'baz',
		];

		$session->setTempdata($data);
		$session->set('baz', 'ballywhoo');

		$this->assertEquals(['foo', 'bar'], $session->getTempKeys());
	}
}
