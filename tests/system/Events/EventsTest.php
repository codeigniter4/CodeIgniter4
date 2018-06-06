<?php namespace CodeIgniter\Events;

use Tests\Support\Events\MockEvents;

class EventsTest extends \CIUnitTestCase
{
	/**
	 * Accessible event manager instance
	 */
	protected $manager;

	public function setUp()
	{
		parent::setUp();

		$this->manager = new MockEvents();

		Events::removeAllListeners();
	}

	//--------------------------------------------------------------------

	public function testInitialize()
	{
		// it should start out empty
		$default = APPPATH . 'Config/Events.php';
		Events::setFile('');
		$this->assertEmpty($this->manager->getEventsFile());

		// make sure we have a default events file
		Events::initialize();
		$this->assertEquals($default, $this->manager->getEventsFile());

		// and we should not be able to change it normally
		Events::initialize('abracadabra');
		$this->assertEquals($default, $this->manager->getEventsFile());

		// but we should be able to change it through the backdoor
		Events::setFile('/peanuts');
		$this->assertEquals('/peanuts', $this->manager->getEventsFile());
	}

	//--------------------------------------------------------------------

	// Not working currently - might want to revisit at some point.
//	public function testPerformance()
//	{
//		$logged = Events::getPerformanceLogs();
//		// there should be a few event activities logged
//		$this->assertGreaterThan(0,count($logged));
//
//		// might want additional tests after some activity, or to inspect what has happened so far
//	}

	//--------------------------------------------------------------------

	public function testListeners()
	{
		$callback1 = function() {

		};
		$callback2 = function() {

		};

		Events::on('foo', $callback1, EVENT_PRIORITY_HIGH);
		Events::on('foo', $callback2, EVENT_PRIORITY_NORMAL);

		$this->assertEquals([$callback2, $callback1], Events::listeners('foo'));
	}

	//--------------------------------------------------------------------

	public function testHandleEvent()
	{
		$result = null;

		Events::on('foo', function($arg) use(&$result) {
			$result = $arg;
		});

		$this->assertTrue(Events::trigger('foo', 'bar'));

		$this->assertEquals('bar', $result);
	}

	//--------------------------------------------------------------------

	public function testCancelEvent()
	{
		$result = 0;

		// This should cancel the flow of events, and leave
		// $result = 1.
		Events::on('foo', function($arg) use (&$result) {
			$result = 1;
			return false;
		});
		Events::on('foo', function($arg) use (&$result) {
			$result = 2;
		});

		$this->assertFalse(Events::trigger('foo', 'bar'));
		$this->assertEquals(1, $result);
	}

	//--------------------------------------------------------------------

	public function testPriority()
	{
		$result = 0;

		Events::on('foo', function() use (&$result) {
			$result = 1;
			return false;
		}, EVENT_PRIORITY_NORMAL);
		// Since this has a higher priority, it will
		// run first.
		Events::on('foo', function() use (&$result) {
			$result = 2;
			return false;
		}, EVENT_PRIORITY_HIGH);

		$this->assertFalse(Events::trigger('foo', 'bar'));
		$this->assertEquals(2, $result);
	}

	//--------------------------------------------------------------------

	public function testPriorityWithMultiple()
	{
		$result = [];

		Events::on('foo', function() use (&$result) {
			$result[] = 'a';
		}, EVENT_PRIORITY_NORMAL);

		Events::on('foo', function() use (&$result) {
			$result[] = 'b';
		}, EVENT_PRIORITY_LOW);

		Events::on('foo', function() use (&$result) {
			$result[] = 'c';
		}, EVENT_PRIORITY_HIGH);

		Events::on('foo', function() use (&$result) {
			$result[] = 'd';
		}, 75);

		Events::trigger('foo');
		$this->assertEquals(['c', 'd', 'a', 'b'], $result);
	}

	//--------------------------------------------------------------------

	public function testRemoveListener()
	{
		$result = false;

		$callback = function() use (&$result) {
			$result = true;
		};

		Events::on('foo', $callback);

		Events::trigger('foo');
		$this->assertTrue($result);

		$result = false;
		$this->assertTrue(Events::removeListener('foo', $callback));

		Events::trigger('foo');
		$this->assertFalse($result);
	}

	//--------------------------------------------------------------------

	public function testRemoveListenerTwice()
	{
		$result = false;

		$callback = function() use (&$result) {
			$result = true;
		};

		Events::on('foo', $callback);

		Events::trigger('foo');
		$this->assertTrue($result);

		$result = false;
		$this->assertTrue(Events::removeListener('foo', $callback));
		$this->assertFalse(Events::removeListener('foo', $callback));

		Events::trigger('foo');
		$this->assertFalse($result);
	}

	//--------------------------------------------------------------------

	public function testRemoveUnknownListener()
	{
		$result = false;

		$callback = function() use (&$result) {
			$result = true;
		};

		Events::on('foo', $callback);

		Events::trigger('foo');
		$this->assertTrue($result);

		$result = false;
		$this->assertFalse(Events::removeListener('bar', $callback));

		Events::trigger('foo');
		$this->assertTrue($result);
	}

	//--------------------------------------------------------------------

	public function testRemoveAllListenersWithSingleEvent()
	{
		$result = false;

		$callback = function() use (&$result) {
			$result = true;
		};

		Events::on('foo', $callback);

		Events::removeAllListeners('foo');

		$listeners = Events::listeners('foo');

		$this->assertEquals([], $listeners);
	}

	//--------------------------------------------------------------------


	public function testRemoveAllListenersWithMultipleEvents()
	{
		$result = false;

		$callback = function() use (&$result) {
			$result = true;
		};

		Events::on('foo', $callback);
		Events::on('bar', $callback);

		Events::removeAllListeners();

		$this->assertEquals([], Events::listeners('foo'));
		$this->assertEquals([], Events::listeners('bar'));
	}

	//--------------------------------------------------------------------

	public function testSimulate()
	{
		$result = 0;

		$callback = function() use (&$result) {
			$result += 2;
		};

		Events::on('foo', $callback);

		Events::simulate(true);
		Events::trigger('foo');

		$this->assertEquals(0, $result);
	}

}
