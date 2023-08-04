<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Throttle;

use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockCache;

/**
 * @internal
 *
 * @group Others
 */
final class ThrottleTest extends CIUnitTestCase
{
    private CacheInterface $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = new MockCache();
    }

    public function testTokenTime(): void
    {
        $throttler = new Throttler($this->cache);

        // tokenTime should be 0 to start
        $this->assertSame(0, $throttler->getTokenTime());

        // set $rate
        $rate = 1;    // allow 1 request per minute

        // When the first check you have a token, so tokenTime should be 0
        $throttler->check('127.0.0.1', $rate, MINUTE);
        $this->assertSame(0, $throttler->getTokenTime());

        // When additional check you don't have one token, so tokenTime should be 1 or greater
        $throttler->check('127.0.0.1', $rate, MINUTE);
        $this->assertGreaterThanOrEqual(1, $throttler->getTokenTime());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5458
     */
    public function testTokenTimeCalculation(): void
    {
        $time = 1_639_441_295;

        $throttler = new Throttler($this->cache);
        $throttler->setTestTime($time);

        $capacity = 2;
        $seconds  = 200;

        // refresh = 200 / 2 = 100 seconds
        // refresh rate = 2 / 200 = 0.01 token per second

        // token should be 2
        $this->assertTrue($throttler->check('test', $capacity, $seconds));
        // token should be 2 - 1 = 1
        $this->assertSame(0, $throttler->getTokenTime(), 'Wrong token time');

        // do nothing for 3 seconds
        $throttler = $throttler->setTestTime($time + 3);

        // token should be 1 + 3 * 0.01 = 1.03
        $this->assertTrue($throttler->check('test', $capacity, $seconds));
        // token should be 1.03 - 1 = 0.03
        $this->assertSame(0, $throttler->getTokenTime(), 'Wrong token time');

        $this->assertFalse($throttler->check('test', $capacity, $seconds));
        // token should still be 0.03 because check failed

        // expect remaining time: (1 - 0.03) * 100 = 97
        $this->assertSame(97, $throttler->getTokenTime(), 'Wrong token time');
    }

    public function testIPSavesBucket(): void
    {
        $throttler = new Throttler($this->cache);

        $this->assertTrue($throttler->check('127.0.0.1', 60, MINUTE));
        $this->assertSame(59, $this->cache->get('throttler_127.0.0.1'));
    }

    public function testRemove(): void
    {
        $throttler = new Throttler($this->cache);

        $this->assertTrue($throttler->check('127.0.0.1', 1, MINUTE));
        $this->assertFalse($throttler->check('127.0.0.1', 1, MINUTE));

        $throttler->remove('127.0.0.1');

        $this->assertNull($this->cache->get('throttler_127.0.0.1'));
        $this->assertTrue($throttler->check('127.0.0.1', 1, MINUTE));
    }

    public function testDecrementsValues(): void
    {
        $throttler = new Throttler($this->cache);

        $throttler->check('127.0.0.1', 60, MINUTE);
        $throttler->check('127.0.0.1', 60, MINUTE);
        $throttler->check('127.0.0.1', 60, MINUTE);

        $this->assertCloseEnough(57, $this->cache->get('throttler_127.0.0.1'));
    }

    public function testReturnsFalseIfBucketEmpty(): void
    {
        $throttler = new Throttler($this->cache);

        $throttler->check('127.0.0.1', 1, MINUTE);

        $this->assertFalse($throttler->check('127.0.0.1', 1, MINUTE));
    }

    public function testCosting(): void
    {
        $throttler = new Throttler($this->cache);

        $rate = 60; // allow 1 per second
        $cost = 10;
        $throttler->check('127.0.0.1', $rate, MINUTE, $cost);
        $this->assertSame($rate - $cost, $this->cache->get('throttler_127.0.0.1'));
    }

    public function testUnderload(): void
    {
        $throttler = new Throttler($this->cache);

        $rate = 120; // allow 2 per second, in theory
        $throttler->check('127.0.0.1', $rate, MINUTE);
        $this->assertSame($rate - 1, $this->cache->get('throttler_127.0.0.1'));

        $throttler->setTestTime(strtotime('+2 seconds')); // should be more tokens available
        $this->assertTrue($throttler->check('127.0.0.1', $rate, MINUTE));
        // but the bucket should not be over-filled
        $this->assertSame($rate - 1, $this->cache->get('throttler_127.0.0.1'));
    }

    public function testOverload(): void
    {
        $throttler = new Throttler($this->cache);

        $rate = 60; // allow 1 per second, in theory
        $cost = 100; // except we blow it
        // but the first request succeeds
        $this->assertTrue($throttler->check('127.0.0.1', $rate, MINUTE, $cost));
        // and a second one doesn't
        $this->assertFalse($throttler->check('127.0.0.1', $rate, MINUTE, $cost));
    }

    public function testFlooding(): void
    {
        $time = 1_639_441_295;

        $throttler = new Throttler($this->cache);
        $throttler->setTestTime($time);

        $rate = 60; // allow 1 per second after the bucket is emptied
        $cost = 1;

        // Blow through the bucket in a natural way., with 1 second "grace"
        for ($i = 0; $i <= $rate; $i++) {
            $throttler->check('127.0.0.1', $rate, MINUTE, $cost);
        }

        // Should be empty now.
        $this->assertFalse($throttler->check('127.0.0.1', $rate, MINUTE, $cost));
        $this->assertSame(0, $this->cache->get('throttler_127.0.0.1'));

        $throttler = $throttler->setTestTime($time + 10);

        $this->assertTrue($throttler->check('127.0.0.1', $rate, MINUTE, 0));
        $this->assertSame(10.0, round($this->cache->get('throttler_127.0.0.1')));
    }

    /**
     * @dataProvider provideTokenTimeCalculationUCs
     */
    public function testTokenTimeCalculationUCs(int $capacity, int $seconds, array $checkInputs): void
    {
        $key       = 'testkey';
        $throttler = new Throttler($this->cache);

        // clear $key before test start
        $throttler->remove($key);

        foreach ($checkInputs as $index => $checkInput) {
            $throttler->setTestTime($checkInput['testTime']);
            $checkResult = $throttler->check($key, $capacity, $seconds, $checkInput['cost']);

            $this->assertSame($checkInput['expectedCheckResult'], $checkResult, "Input#{$index}: Wrong check() result");
            $this->assertSame($checkInput['expectedTokenTime'], $throttler->getTokenTime(), "Input#{$index}: Wrong tokenTime");
        }
    }

    public static function provideTokenTimeCalculationUCs(): iterable
    {
        return [
            '2 capacity / 200 seconds (100s refresh, 0.01 tokens/s) -> 5 checks, 1 cost each' => [
                'capacity'    => 2,
                'seconds'     => 200,
                'checkInputs' => [
                    [   // 2 -> 1
                        'testTime'            => 0,
                        'cost'                => 1,
                        'expectedCheckResult' => true,
                        'expectedTokenTime'   => 0,
                    ],
                    [   // +3s / 1.03 -> 0.03
                        'testTime'            => 3,
                        'cost'                => 1,
                        'expectedCheckResult' => true,
                        'expectedTokenTime'   => 0,
                    ],
                    [   // +0s / 0.03 -> 0.03 / (1 - 0.03) * 100 = 97
                        'testTime'            => 3,
                        'cost'                => 1,
                        'expectedCheckResult' => false,
                        'expectedTokenTime'   => 97,
                    ],
                    [   // +1m 32s / 0.95 -> 0.95 / (1 - 0.95) * 100 = 5
                        'testTime'            => 95,
                        'cost'                => 1,
                        'expectedCheckResult' => false,
                        'expectedTokenTime'   => 5,
                    ],
                    [   // +7s / 1.02 -> 0.02
                        'testTime'            => 102,
                        'cost'                => 1,
                        'expectedCheckResult' => true,
                        'expectedTokenTime'   => 0,
                    ],
                    [   // +13s / 0.15 / (1 - 0.15) * 100 = 85
                        'testTime'            => 115,
                        'cost'                => 1,
                        'expectedCheckResult' => false,
                        'expectedTokenTime'   => 85,
                    ],
                ],
            ],
            '1 capacity / 3600 seconds (3600s refresh, 2.77e-4 tokens/s) -> 2 checks with 1 cost each' => [
                'capacity'    => 1,
                'seconds'     => 3600,
                'checkInputs' => [
                    [   // 1 -> 0
                        'testTime'            => 0,
                        'cost'                => 1,
                        'expectedCheckResult' => true,
                        'expectedTokenTime'   => 0,
                    ],
                    [   // +6m / 0.1 -> 0.1 / (1 - 0.1) * 3600 = 3240
                        'testTime'            => 360,
                        'cost'                => 1,
                        'expectedCheckResult' => false,
                        'expectedTokenTime'   => 3240,
                    ],
                ],
            ],
            '10 capacity / 200 seconds (20s refresh, 0.05 tokens/s) -> 7 checks with different costs (RNG)' => [
                'capacity'    => 10,
                'seconds'     => 200,
                'checkInputs' => [
                    [   // -2t / 10 -> 8
                        'testTime'            => 0,
                        'cost'                => 2,
                        'expectedCheckResult' => true,
                        'expectedTokenTime'   => 0,
                    ],
                    [   // +19s -2t / 8.95 -> 6.95
                        'testTime'            => 19,
                        'cost'                => 2,
                        'expectedCheckResult' => true,
                        'expectedTokenTime'   => 0,
                    ],
                    [   // +16s -3t / 7.75 -> 4.75
                        'testTime'            => 35,
                        'cost'                => 3,
                        'expectedCheckResult' => true,
                        'expectedTokenTime'   => 0,
                    ],
                    [   // +4s -2t / 4.95 -> 2.95
                        'testTime'            => 39,
                        'cost'                => 2,
                        'expectedCheckResult' => true,
                        'expectedTokenTime'   => 0,
                    ],
                    [   // +13s -5t / 3.6 -> -1.4 (blow allowed)
                        'testTime'            => 52,
                        'cost'                => 5,
                        'expectedCheckResult' => true,
                        'expectedTokenTime'   => 0,
                    ],
                    [   // +2s -2t / -1.3 -> -1.3 / (1 - (-1.3)) * 20 = 46
                        'testTime'            => 54,
                        'cost'                => 2,
                        'expectedCheckResult' => false,
                        'expectedTokenTime'   => 46,
                    ],
                    [   // +7s -2t / -0.95 - -0.95 / (1 - (-0.95)) * 20 = 39
                        'testTime'            => 61,
                        'cost'                => 2,
                        'expectedCheckResult' => false,
                        'expectedTokenTime'   => 39,
                    ],
                ],
            ],
        ];
    }
}
