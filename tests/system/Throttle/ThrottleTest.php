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
 */
final class ThrottleTest extends CIUnitTestCase
{
    private CacheInterface $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = new MockCache();
    }

    public function testTokenTime()
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
    public function testTokenTimeCalculation()
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

    public function testIPSavesBucket()
    {
        $throttler = new Throttler($this->cache);

        $this->assertTrue($throttler->check('127.0.0.1', 60, MINUTE));
        $this->assertSame(59, $this->cache->get('throttler_127.0.0.1'));
    }

    public function testRemove()
    {
        $throttler = new Throttler($this->cache);

        $this->assertTrue($throttler->check('127.0.0.1', 1, MINUTE));
        $this->assertFalse($throttler->check('127.0.0.1', 1, MINUTE));

        $throttler->remove('127.0.0.1');

        $this->assertNull($this->cache->get('throttler_127.0.0.1'));
        $this->assertTrue($throttler->check('127.0.0.1', 1, MINUTE));
    }

    /**
     * @group single
     */
    public function testDecrementsValues()
    {
        $throttler = new Throttler($this->cache);

        $throttler->check('127.0.0.1', 60, MINUTE);
        $throttler->check('127.0.0.1', 60, MINUTE);
        $throttler->check('127.0.0.1', 60, MINUTE);

        $this->assertCloseEnough(57, $this->cache->get('throttler_127.0.0.1'));
    }

    public function testReturnsFalseIfBucketEmpty()
    {
        $throttler = new Throttler($this->cache);

        $throttler->check('127.0.0.1', 1, MINUTE);

        $this->assertFalse($throttler->check('127.0.0.1', 1, MINUTE));
    }

    public function testCosting()
    {
        $throttler = new Throttler($this->cache);

        $rate = 60; // allow 1 per second
        $cost = 10;
        $throttler->check('127.0.0.1', $rate, MINUTE, $cost);
        $this->assertSame($rate - $cost, $this->cache->get('throttler_127.0.0.1'));
    }

    public function testUnderload()
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

    public function testOverload()
    {
        $throttler = new Throttler($this->cache);

        $rate = 60; // allow 1 per second, in theory
        $cost = 100; // except we blow it
        // but the first request succeeds
        $this->assertTrue($throttler->check('127.0.0.1', $rate, MINUTE, $cost));
        // and a second one doesn't
        $this->assertFalse($throttler->check('127.0.0.1', $rate, MINUTE, $cost));
    }

    public function testFlooding()
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
}
