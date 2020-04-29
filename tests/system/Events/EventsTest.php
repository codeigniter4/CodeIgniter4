<?php
namespace CodeIgniter\Events;

use CodeIgniter\Config\Config;
use CodeIgniter\Test\Mock\MockEvents;

class EventsTest extends \CodeIgniter\Test\CIUnitTestCase
{

	/**
	 * Accessible event manager instance
	 */
	protected $manager;

	protected function setUp(): void
	{
		parent::setUp();

		$this->manager = new MockEvents();

		Events::removeAllListeners();
	}

	//--------------------------------------------------------------------

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testInitialize()
	{
		$config                  = config('Modules');
		$config->activeExplorers = [];
		Config::injectMock('Modules', $config);

		// it should start out empty
		$default = [APPPATH . 'Config/Events.php'];
		$this->manager->setFiles([]);
		$this->assertEmpty($this->manager->getFiles());

		// make sure we have a default events file
		$this->manager->unInitialize();
		$this->manager::initialize();
		$this->assertEquals($default, $this->manager->getFiles());

		// but we should be able to change it through the backdoor
		$this->manager::setFiles(['/peanuts']);
		$this->assertEquals(['/peanuts'], $this->manager->getFiles());

		// re-initializing should have no effect
		Events::initialize();
		$this->assertEquals(['/peanuts'], $this->manager->getFiles());
	}

	//--------------------------------------------------------------------

	public function testPerformance()
	{
		$result = null;
		Events::on('foo', function ($arg) use (&$result) {
			$result = $arg;
		});
		Events::trigger('foo', 'bar');

		$logged = Events::getPerformanceLogs();
		// there should be some event activity logged
		$this->assertGreaterThan(0, count($logged));
	}

	//--------------------------------------------------------------------

	public function testListeners()
	{
		$callback1 = function () {
		};
		$callback2 = function () {
		};

		Events::on('foo', $callback1, EVENT_PRIORITY_HIGH);
		Events::on('foo', $callback2, EVENT_PRIORITY_NORMAL);

		$this->assertEquals([$callback2, $callback1], Events::listeners('foo'));
	}

	//--------------------------------------------------------------------

	public function testHandleEvent()
	{
		$result = null;

		Events::on('foo', function ($arg) use (&$result) {
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
		Events::on('foo', function ($arg) use (&$result) {
			$result = 1;
			return false;
		});
		Events::on('foo', function ($arg) use (&$result) {
			$result = 2;
		});

		$this->assertFalse(Events::trigger('foo', 'bar'));
		$this->assertEquals(1, $result);
	}

	//--------------------------------------------------------------------

	public function testPriority()
	{
		$result = 0;

		Events::on('foo', function () use (&$result) {
			$result = 1;
			return false;
		}, EVENT_PRIORITY_NORMAL);
		// Since this has a higher priority, it will
		// run first.
		Events::on('foo', function () use (&$result) {
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

		Events::on('foo', function () use (&$result) {
			$result[] = 'a';
		}, EVENT_PRIORITY_NORMAL);

		Events::on('foo', function () use (&$result) {
			$result[] = 'b';
		}, EVENT_PRIORITY_LOW);

		Events::on('foo', function () use (&$result) {
			$result[] = 'c';
		}, EVENT_PRIORITY_HIGH);

		Events::on('foo', function () use (&$result) {
			$result[] = 'd';
		}, 75);

		Events::trigger('foo');
		$this->assertEquals(['c', 'd', 'a', 'b'], $result);
	}

	//--------------------------------------------------------------------

	public function testRemoveListener()
	{
		$result = false;

		$callback = function () use (&$result) {
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

		$callback = function () use (&$result) {
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

		$callback = function () use (&$result) {
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

		$callback = function () use (&$result) {
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

		$callback = function () use (&$result) {
			$result = true;
		};

		Events::on('foo', $callback);
		Events::on('bar', $callback);

		Events::removeAllListeners();

		$this->assertEquals([], Events::listeners('foo'));
		$this->assertEquals([], Events::listeners('bar'));
	}

	//--------------------------------------------------------------------

	// Basically if it doesn't crash this should be good...
	public function testHandleEventCallableInternalFunc()
	{
		$result = null;

		Events::on('foo', 'strlen');

		$this->assertTrue(Events::trigger('foo', 'bar'));
	}

	//--------------------------------------------------------------------

	public function testHandleEventCallableClass()
	{
		$result = null;

		$box = new class() {
			public $logged;

			public function hold(string $value)
			{
				$this->logged = $value;
			}
		};

		Events::on('foo', [$box, 'hold']);

		$this->assertTrue(Events::trigger('foo', 'bar'));

		$this->assertEquals('bar', $box->logged);
	}

	//--------------------------------------------------------------------

	public function testSimulate()
	{
		$result = 0;

		$callback = function () use (&$result) {
			$result += 2;
		};

		Events::on('foo', $callback);

		Events::simulate(true);
		Events::trigger('foo');

		$this->assertEquals(0, $result);
	}
}
