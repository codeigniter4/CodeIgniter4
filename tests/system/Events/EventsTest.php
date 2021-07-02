<?php

namespace CodeIgniter\Events;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockEvents;
use Config\Modules;

/**
 * @internal
 */
final class EventsTest extends CIUnitTestCase
{
    /**
     * Accessible event manager instance
     *
     * @var MockEvents
     */
    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = new MockEvents();

        Events::removeAllListeners();
    }

    protected function tearDown(): void
    {
        Events::simulate(false);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testInitialize()
    {
        /**
         * @var Modules
         */
        $config          = config('Modules');
        $config->aliases = [];

        // it should start out empty
        MockEvents::setFiles([]);
        $this->assertEmpty($this->manager->getFiles());

        // make sure we have a default events file
        $default = [APPPATH . 'Config' . DIRECTORY_SEPARATOR . 'Events.php'];
        $this->manager->unInitialize();
        MockEvents::initialize();
        $this->assertSame($default, $this->manager->getFiles());

        // but we should be able to change it through the backdoor
        MockEvents::setFiles(['/peanuts']);
        $this->assertSame(['/peanuts'], $this->manager->getFiles());

        // re-initializing should have no effect
        MockEvents::initialize();
        $this->assertSame(['/peanuts'], $this->manager->getFiles());
    }

    public function testPerformance()
    {
        $result = null;
        Events::on('foo', static function ($arg) use (&$result) {
            $result = $arg;
        });
        Events::trigger('foo', 'bar');

        $logged = Events::getPerformanceLogs();
        // there should be some event activity logged
        $this->assertGreaterThan(0, count($logged));
    }

    public function testListeners()
    {
        $callback1 = static function () {
        };
        $callback2 = static function () {
        };

        Events::on('foo', $callback1, EVENT_PRIORITY_HIGH);
        Events::on('foo', $callback2, EVENT_PRIORITY_NORMAL);

        $this->assertSame([$callback1, $callback2], Events::listeners('foo'));
    }

    public function testHandleEvent()
    {
        $result = null;

        Events::on('foo', static function ($arg) use (&$result) {
            $result = $arg;
        });

        $this->assertTrue(Events::trigger('foo', 'bar'));

        $this->assertSame('bar', $result);
    }

    public function testCancelEvent()
    {
        $result = 0;

        // This should cancel the flow of events, and leave
        // $result = 1.
        Events::on('foo', static function ($arg) use (&$result) {
            $result = 1;

            return false;
        });
        Events::on('foo', static function ($arg) use (&$result) {
            $result = 2;
        });

        $this->assertFalse(Events::trigger('foo', 'bar'));
        $this->assertSame(1, $result);
    }

    public function testPriority()
    {
        $result = 0;

        Events::on('foo', static function () use (&$result) {
            $result = 1;

            return false;
        }, EVENT_PRIORITY_NORMAL);
        // Since this has a higher priority, it will
        // run first.
        Events::on('foo', static function () use (&$result) {
            $result = 2;

            return false;
        }, EVENT_PRIORITY_HIGH);

        $this->assertFalse(Events::trigger('foo', 'bar'));
        $this->assertSame(2, $result);
    }

    public function testPriorityWithMultiple()
    {
        $result = [];

        Events::on('foo', static function () use (&$result) {
            $result[] = 'a';
        }, EVENT_PRIORITY_NORMAL);

        Events::on('foo', static function () use (&$result) {
            $result[] = 'b';
        }, EVENT_PRIORITY_LOW);

        Events::on('foo', static function () use (&$result) {
            $result[] = 'c';
        }, EVENT_PRIORITY_HIGH);

        Events::on('foo', static function () use (&$result) {
            $result[] = 'd';
        }, 75);

        Events::trigger('foo');
        $this->assertSame(['c', 'd', 'a', 'b'], $result);
    }

    public function testRemoveListener()
    {
        $result = false;

        $callback = static function () use (&$result) {
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

    public function testRemoveListenerTwice()
    {
        $result = false;

        $callback = static function () use (&$result) {
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

    public function testRemoveUnknownListener()
    {
        $result = false;

        $callback = static function () use (&$result) {
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

    public function testRemoveAllListenersWithSingleEvent()
    {
        $result = false;

        $callback = static function () use (&$result) {
            $result = true;
        };

        Events::on('foo', $callback);

        Events::removeAllListeners('foo');

        $listeners = Events::listeners('foo');

        $this->assertSame([], $listeners);
    }

    public function testRemoveAllListenersWithMultipleEvents()
    {
        $result = false;

        $callback = static function () use (&$result) {
            $result = true;
        };

        Events::on('foo', $callback);
        Events::on('bar', $callback);

        Events::removeAllListeners();

        $this->assertSame([], Events::listeners('foo'));
        $this->assertSame([], Events::listeners('bar'));
    }

    // Basically if it doesn't crash this should be good...
    public function testHandleEventCallableInternalFunc()
    {
        Events::on('foo', 'strlen');

        $this->assertTrue(Events::trigger('foo', 'bar'));
    }

    public function testHandleEventCallableClass()
    {
        $box = new class() {
            public $logged;

            public function hold(string $value)
            {
                $this->logged = $value;
            }
        };

        Events::on('foo', [$box, 'hold']);

        $this->assertTrue(Events::trigger('foo', 'bar'));

        $this->assertSame('bar', $box->logged);
    }

    public function testSimulate()
    {
        $result = 0;

        $callback = static function () use (&$result) {
            $result += 2;
        };

        Events::on('foo', $callback);

        Events::simulate(true);
        Events::trigger('foo');

        $this->assertSame(0, $result);
    }
}
