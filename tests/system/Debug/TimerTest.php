<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Debug;

use ArgumentCountError;
use CodeIgniter\Test\CIUnitTestCase;
use ErrorException;
use RuntimeException;

/**
 * @internal
 */
final class TimerTest extends CIUnitTestCase
{
    /**
     * We do most of our tests in this one method. While I usually frown
     * on this, it's handy here so that we don't stall the tests any
     * longer then needed.
     *
     * @timeLimit 1.5
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

    /**
     * @timeLimit 1.5
     */
    public function testAutoCalcsTimerEnd()
    {
        $timer = new Timer();

        $timer->start('test1');
        sleep(1);

        $timers = $timer->getTimers();

        $this->assertArrayHasKey('duration', $timers['test1'], 'No duration was calculated.');
        $this->assertGreaterThanOrEqual(1.0, $timers['test1']['duration']);
    }

    /**
     * @timeLimit 1.5
     */
    public function testElapsedTimeGivesSameResultAsTimersArray()
    {
        $timer = new Timer();

        $timer->start('test1');
        sleep(1);
        $timer->stop('test1');

        $timers = $timer->getTimers();

        $expected = $timers['test1']['duration'];

        $this->assertSame($expected, $timer->getElapsedTime('test1'));
    }

    public function testThrowsExceptionStoppingNonTimer()
    {
        $this->expectException('RunTimeException');

        $timer = new Timer();

        $timer->stop('test1');
    }

    public function testLongExecutionTime()
    {
        $timer = new Timer();
        $timer->start('longjohn', strtotime('-110 minutes'));
        $this->assertCloseEnough(110 * 60, $timer->getElapsedTime('longjohn'));
    }

    public function testLongExecutionTimeThroughCommonFunc()
    {
        $timer = new Timer();
        $timer->start('longjohn', strtotime('-11 minutes'));
        $this->assertCloseEnough(11 * 60, $timer->getElapsedTime('longjohn'));
    }

    /**
     * @timeLimit 1.5
     */
    public function testCommonStartStop()
    {
        timer('test1');
        sleep(1);
        timer('test1');

        $this->assertGreaterThanOrEqual(1.0, timer()->getElapsedTime('test1'));
    }

    public function testReturnsNullGettingElapsedTimeOfNonTimer()
    {
        $timer = new Timer();

        $this->assertNull($timer->getElapsedTime('test1'));
    }

    public function testRecordFunctionNoReturn()
    {
        $timer       = new Timer();
        $returnValue = $timer->record('longjohn', static function () { usleep(100000); });

        $this->assertGreaterThanOrEqual(0.1, $timer->getElapsedTime('longjohn'));
        $this->assertNull($returnValue);
    }

    public function testRecordFunctionWithReturn()
    {
        $timer       = new Timer();
        $returnValue = $timer->record('longjohn', static function () {
            usleep(100000);

            return 'test';
        });

        $this->assertGreaterThanOrEqual(0.1, $timer->getElapsedTime('longjohn'));
        $this->assertSame('test', $returnValue);
    }

    public function testRecordArrowFunction()
    {
        $timer       = new Timer();
        $returnValue = $timer->record('longjohn', static fn () => strlen('CI4'));

        $this->assertLessThan(0.1, $timer->getElapsedTime('longjohn'));
        $this->assertSame(3, $returnValue);
    }

    public function testRecordThrowsException()
    {
        $this->expectException(RuntimeException::class);

        $timer = new Timer();
        $timer->record('ex', static function () { throw new RuntimeException(); });
    }

    public function testRecordThrowsErrorOnCallableWithParams()
    {
        if (version_compare(PHP_VERSION, '8.0.0') >= 0) {
            $this->expectException(ArgumentCountError::class);
        } else {
            $this->expectException(ErrorException::class);
        }

        $timer = new Timer();
        $timer->record('error', 'strlen');
    }

    public function testCommonNoNameExpectTimer()
    {
        $returnValue = timer();

        $this->assertInstanceOf(Timer::class, $returnValue);
    }

    public function testCommonWithNameExpectTimer()
    {
        $returnValue = timer('test');

        $this->assertInstanceOf(Timer::class, $returnValue);
        $this->assertTrue($returnValue->has('test'));
    }

    public function testCommonNoNameCallableExpectTimer()
    {
        $returnValue = timer(null, static fn () => strlen('CI4'));

        $this->assertInstanceOf(Timer::class, $returnValue);
    }

    public function testCommonCallableExpectNoReturn()
    {
        $returnValue = timer('common', static function () { usleep(100000); });

        $this->assertNotInstanceOf(Timer::class, $returnValue);
        $this->assertNull($returnValue);
        $this->assertGreaterThanOrEqual(0.1, timer()->getElapsedTime('common'));
    }

    public function testCommonCallableExpectWithReturn()
    {
        $returnValue = timer('common', static fn () => strlen('CI4'));

        $this->assertNotInstanceOf(Timer::class, $returnValue);
        $this->assertSame(3, $returnValue);
        $this->assertLessThanOrEqual(0.1, timer()->getElapsedTime('common'));
    }
}
