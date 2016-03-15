<?php namespace CodeIgniter\Debug;

class TimerTest extends \CIUnitTestCase
{

	public function setUp() { }

	//--------------------------------------------------------------------

	public function tearDown() { }

	//--------------------------------------------------------------------

	/**
	 * We do most of our tests in this one method. While I usually frown
	 * on this, it's handy here so that we don't stall the tests any
	 * longer then needed.
	 */
	public function testStoresTimers()
	{
		$timer = new Timer();

		$timer->start('test1');
		sleep(1);
		$timer->stop('test1');

		$timers = $timer->getTimers();

		$this->assertTrue(count($timers) === 1, "No timers were stored.");
		$this->assertArrayHasKey('test1', $timers, 'No "test1" array found.');
		$this->assertArrayHasKey('start', $timers['test1'], 'No "start" value found.');
		$this->assertArrayHasKey('end', $timers['test1'], 'No "end" value found.');

		// Since the timer has been stopped - it will have a value. In this
		// case it should be over 1 second.
		$this->assertArrayHasKey('duration', $timers['test1'], "No duration was calculated.");
		$this->assertGreaterThanOrEqual(1.0, $timers['test1']['duration']);
	}

	//--------------------------------------------------------------------

	public function testAutoCalcsTimerEnd()
	{
		$timer = new Timer();

		$timer->start('test1');
		sleep(1);

		$timers = $timer->getTimers();

		$this->assertArrayHasKey('duration', $timers['test1'], "No duration was calculated.");
		$this->assertGreaterThanOrEqual(1.0, $timers['test1']['duration']);
	}

	//--------------------------------------------------------------------

	public function testElapsedTimeGivesSameResultAsTimersArray()
	{
		$timer = new Timer();

		$timer->start('test1');
		sleep(1);
		$timer->stop('test1');

		$timers = $timer->getTimers();

		$expected = $timers['test1']['duration'];

		$this->assertEquals($expected, $timer->getElapsedTime('test1'));
	}

	//--------------------------------------------------------------------

	/**
	 * @expectedException RunTimeException
	 */
	public function testThrowsExceptionStoppingNonTimer()
	{
		$timer = new Timer();

		$timer->stop('test1');
	}

	//--------------------------------------------------------------------


}
