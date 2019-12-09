<?php
namespace CodeIgniter\Debug;

class TimerTest extends \CIUnitTestCase
{

	protected function setUp(): void
	{
	}

	//--------------------------------------------------------------------

	public function tearDown(): void
	{
	}

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

		$this->assertCount(1, $timers, 'No timers were stored.');
		$this->assertArrayHasKey('test1', $timers, 'No "test1" array found.');
		$this->assertArrayHasKey('start', $timers['test1'], 'No "start" value found.');
		$this->assertArrayHasKey('end', $timers['test1'], 'No "end" value found.');

		// Since the timer has been stopped - it will have a value. In this
		// case it should be over 1 second.
		$this->assertArrayHasKey('duration', $timers['test1'], 'No duration was calculated.');
		$this->assertGreaterThanOrEqual(1.0, $timers['test1']['duration']);
	}

	//--------------------------------------------------------------------

	public function testAutoCalcsTimerEnd()
	{
		$timer = new Timer();

		$timer->start('test1');
		sleep(1);

		$timers = $timer->getTimers();

		$this->assertArrayHasKey('duration', $timers['test1'], 'No duration was calculated.');
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

	public function testLongExecutionTime()
	{
		$timer = new Timer();
		$timer->start('longjohn', strtotime('-11 minutes'));
		$this->assertCloseEnough(11 * 60, $timer->getElapsedTime('longjohn'));
	}

	//--------------------------------------------------------------------

	public function testLongExecutionTimeThroughCommonFunc()
	{
		$timer = new Timer();
		$timer->start('longjohn', strtotime('-11 minutes'));
		$this->assertCloseEnough(11 * 60, $timer->getElapsedTime('longjohn'));
	}

	//--------------------------------------------------------------------

	public function testCommonStartStop()
	{
		timer('test1');
		sleep(1);
		timer('test1');

		$this->assertGreaterThanOrEqual(1.0, timer()->getElapsedTime('test1'));
	}

	//--------------------------------------------------------------------

	public function testReturnsNullGettingElapsedTimeOfNonTimer()
	{
		$timer = new Timer();

		$this->assertNull($timer->getElapsedTime('test1'));
	}

	//--------------------------------------------------------------------
}
