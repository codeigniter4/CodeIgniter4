<?php namespace CodeIgniter\Hooks;

class HooksTest extends \CIUnitTestCase
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	protected function setUp()
	{
		Hooks::removeAllListeners();
	}

	//--------------------------------------------------------------------

	public function testListeners()
	{
		$callback1 = function() {};
		$callback2 = function() {};

		Hooks::on('foo', $callback1, HOOKS_PRIORITY_HIGH);
		Hooks::on('foo', $callback2, HOOKS_PRIORITY_NORMAL);

		$this->assertEquals([$callback2, $callback1], Hooks::listeners('foo'));
	}

	//--------------------------------------------------------------------

	public function testHandleEvent()
	{
		$result = null;

		Hooks::on('foo', function($arg) use(&$result) {
			$result = $arg;
		});

		$this->assertTrue(Hooks::trigger('foo', 'bar') );

		$this->assertEquals('bar', $result);
	}

	//--------------------------------------------------------------------

	public function testCancelEvent()
	{
		$result = 0;

		// This should cancel the flow of events, and leave
		// $result = 1.
		Hooks::on('foo', function($arg) use (&$result) {
			$result = 1;
			return false;
		});
		Hooks::on('foo', function($arg) use (&$result) {
			$result = 2;
		});

		$this->assertFalse(Hooks::trigger('foo', 'bar'));
		$this->assertEquals(1, $result);
	}

	//--------------------------------------------------------------------

	public function testPriority()
	{
		$result = 0;

		Hooks::on('foo', function() use (&$result) {
			$result = 1;
			return false;
		}, HOOKS_PRIORITY_NORMAL);
		// Since this has a higher priority, it will
		// run first.
		Hooks::on('foo', function() use (&$result) {
			$result = 2;
			return false;
		}, HOOKS_PRIORITY_HIGH);

		$this->assertFalse(Hooks::trigger('foo', 'bar'));
		$this->assertEquals(2, $result);
	}

	//--------------------------------------------------------------------

	public function testPriorityWithMultiple()
	{
		$result = [];

		Hooks::on('foo', function() use (&$result) {
			$result[] = 'a';
		}, HOOKS_PRIORITY_NORMAL);

		Hooks::on('foo', function() use (&$result) {
			$result[] = 'b';
		}, HOOKS_PRIORITY_LOW);

		Hooks::on('foo', function() use (&$result) {
			$result[] = 'c';
		}, HOOKS_PRIORITY_HIGH);

		Hooks::on('foo', function() use (&$result) {
			$result[] = 'd';
		}, 75);

		Hooks::trigger('foo');
		$this->assertEquals(['c', 'd', 'a', 'b'], $result);
	}

	//--------------------------------------------------------------------

	public function testRemoveListener()
	{
		$result = false;

		$callback = function() use (&$result)
		{
			$result = true;
		};

		Hooks::on('foo', $callback);

		Hooks::trigger('foo');
		$this->assertTrue($result);

		$result = false;
		$this->assertTrue( Hooks::removeListener('foo', $callback) );

		Hooks::trigger('foo');
		$this->assertFalse($result);
	}

	//--------------------------------------------------------------------

	public function testRemoveListenerTwice()
	{
		$result = false;

		$callback = function() use (&$result)
		{
			$result = true;
		};

		Hooks::on('foo', $callback);

		Hooks::trigger('foo');
		$this->assertTrue($result);

		$result = false;
		$this->assertTrue( Hooks::removeListener('foo', $callback) );
		$this->assertFalse( Hooks::removeListener('foo', $callback) );

		Hooks::trigger('foo');
		$this->assertFalse($result);
	}

	//--------------------------------------------------------------------

	public function testRemoveUnknownListener()
	{
		$result = false;

		$callback = function() use (&$result)
		{
			$result = true;
		};

		Hooks::on('foo', $callback);

		Hooks::trigger('foo');
		$this->assertTrue($result);

		$result = false;
		$this->assertFalse( Hooks::removeListener('bar', $callback) );

		Hooks::trigger('foo');
		$this->assertTrue($result);
	}

	//--------------------------------------------------------------------

	public function testRemoveAllListenersWithSingleEvent()
	{
		$result = false;

		$callback = function() use (&$result)
		{
			$result = true;
		};

		Hooks::on('foo', $callback);

		Hooks::removeAllListeners('foo');

		$listeners = Hooks::listeners('foo');

		$this->assertEquals([], $listeners);
	}

	//--------------------------------------------------------------------


	public function testRemoveAllListenersWithMultipleEvents()
	{
		$result = false;

		$callback = function() use (&$result)
		{
			$result = true;
		};

		Hooks::on('foo', $callback);
		Hooks::on('bar', $callback);

		Hooks::removeAllListeners();

		$this->assertEquals([], Hooks::listeners('foo'));
		$this->assertEquals([], Hooks::listeners('bar'));
	}

	//--------------------------------------------------------------------

}
