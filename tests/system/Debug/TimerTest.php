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
 *
 * @group Others
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
    public function testStoresTimers(): void
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
    public function testAutoCalcsTimerEnd(): void
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
    public function testElapsedTimeGivesSameResultAsTimersArray(): void
    {
        $timer = new Timer();

        $timer->start('test1');
        sleep(1);
        $timer->stop('test1');

        $timers = $timer->getTimers();

        $expected = $timers['test1']['duration'];

        $this->assertSame($expected, $timer->getElapsedTime('test1'));
    }

    public function testThrowsExceptionStoppingNonTimer(): void
    {
        $this->expectException('RunTimeException');

        $timer = new Timer();

        $timer->stop('test1');
    }

    /**
     * This test might fail if your timezone has Daylight Saving Time.
     * See https://github.com/codeigniter4/CodeIgniter4/issues/6823
     */
    public function testLongExecutionTime(): void
    {
        $timer = new Timer();
        $timer->start('longjohn', strtotime('-110 minutes'));
        $this->assertCloseEnough(110 * 60, $timer->getElapsedTime('longjohn'));
    }

    public function testLongExecutionTimeThroughCommonFunc(): void
    {
        $timer = new Timer();
        $timer->start('longjohn', strtotime('-11 minutes'));
        $this->assertCloseEnough(11 * 60, $timer->getElapsedTime('longjohn'));
    }

    /**
     * @timeLimit 1.5
     */
    public function testCommonStartStop(): void
    {
        timer('test1');
        sleep(1);
        timer('test1');

        $this->assertGreaterThanOrEqual(1.0, timer()->getElapsedTime('test1'));
    }

    public function testReturnsNullGettingElapsedTimeOfNonTimer(): void
    {
        $timer = new Timer();

        $this->assertNull($timer->getElapsedTime('test1'));
    }

    public function testRecordFunctionNoReturn(): void
    {
        $timer       = new Timer();
        $returnValue = $timer->record('longjohn', static function (): void { usleep(100000); });

        $this->assertGreaterThanOrEqual(0.1, $timer->getElapsedTime('longjohn'));
        $this->assertNull($returnValue);
    }

    public function testRecordFunctionWithReturn(): void
    {
        $timer       = new Timer();
        $returnValue = $timer->record('longjohn', static function () {
            usleep(100000);

            return 'test';
        });

        $this->assertGreaterThanOrEqual(0.1, $timer->getElapsedTime('longjohn'));
        $this->assertSame('test', $returnValue);
    }

    public function testRecordArrowFunction(): void
    {
        $timer       = new Timer();
        $returnValue = $timer->record('longjohn', static fn () => strlen('CI4'));

        $this->assertLessThan(0.1, $timer->getElapsedTime('longjohn'));
        $this->assertSame(3, $returnValue);
    }

    public function testRecordThrowsException(): void
    {
        $this->expectException(RuntimeException::class);

        $timer = new Timer();
        $timer->record('ex', static function (): void { throw new RuntimeException(); });
    }

    public function testRecordThrowsErrorOnCallableWithParams(): void
    {
        if (PHP_VERSION_ID >= 80000) {
            $this->expectException(ArgumentCountError::class);
        } else {
            $this->expectException(ErrorException::class);
        }

        $timer = new Timer();
        $timer->record('error', 'strlen');
    }

    public function testCommonNoNameExpectTimer(): void
    {
        $returnValue = timer();

        $this->assertInstanceOf(Timer::class, $returnValue);
    }

    public function testCommonWithNameExpectTimer(): void
    {
        $returnValue = timer('test');

        $this->assertInstanceOf(Timer::class, $returnValue);
        $this->assertTrue($returnValue->has('test'));
    }

    public function testCommonNoNameCallableExpectTimer(): void
    {
        $returnValue = timer(null, static fn () => strlen('CI4'));

        $this->assertInstanceOf(Timer::class, $returnValue);
    }

    public function testCommonCallableExpectNoReturn(): void
    {
        $returnValue = timer('common', static function (): void { usleep(100000); });

        $this->assertNotInstanceOf(Timer::class, $returnValue);
        $this->assertNull($returnValue);
        $this->assertGreaterThanOrEqual(0.1, timer()->getElapsedTime('common'));
    }

    public function testCommonCallableExpectWithReturn(): void
    {
        $returnValue = timer('common', static fn () => strlen('CI4'));

        $this->assertNotInstanceOf(Timer::class, $returnValue);
        $this->assertSame(3, $returnValue);
        $this->assertLessThanOrEqual(0.1, timer()->getElapsedTime('common'));
    }
}
