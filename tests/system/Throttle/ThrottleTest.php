<?php namespace CodeIgniter\Throttle;

use CodeIgniter\Cache\Handlers\MockHandler;

class ThrottleTest extends \CIUnitTestCase
{

	public function setUp()
	{
		parent::setUp();

		$this->cache = new MockHandler();
	}

	public function testIPSavesBucket()
	{
		$throttler = new Throttler($this->cache);

		$this->assertTrue($throttler->check('127.0.0.1', 60, MINUTE));
		$this->assertEquals(59, $this->cache->get('throttler_127.0.0.1'));
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

		$this->assertEquals(57, $this->cache->get('throttler_127.0.0.1'));
	}

	public function testReturnsFalseIfBucketEmpty()
	{
		$throttler = new Throttler($this->cache);

		$throttler->check('127.0.0.1', 1, MINUTE);

		$this->assertFalse($throttler->check('127.0.0.1', 1, MINUTE));
		$this->assertEquals(1, $throttler->getTokenTime());
	}

	public function testCosting()
	{
		$throttler = new Throttler($this->cache);

		$rate = 60; // allow 1 per second
		$cost = 10;
		$throttler->check('127.0.0.1', $rate, MINUTE, $cost);
		$this->assertEquals($rate - $cost, $this->cache->get('throttler_127.0.0.1'));
	}

	public function testUnderload()
	{
		$throttler = new Throttler($this->cache);

		$rate = 120; // allow 2 per second, in theory
		$throttler->check('127.0.0.1', $rate, MINUTE);
		$this->assertEquals($rate - 1, $this->cache->get('throttler_127.0.0.1'));

		sleep(2); // should be more tokens available
		$this->assertTrue($throttler->check('127.0.0.1', $rate, MINUTE));
		// but the bucket should not be over-filled
		$this->assertEquals($rate - 1, $this->cache->get('throttler_127.0.0.1'));
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
		$throttler = new Throttler($this->cache);

		$rate = 60; // allow 1 per second, in theory
		$cost = 40; // except we blow it
		// first request passes
		$this->assertTrue($throttler->check('127.0.0.1', $rate, MINUTE, $cost));
		// and there should only be 20 units left
		$this->assertEquals($rate - $cost, $this->cache->get('throttler_127.0.0.1'));
		// a second request will be allowed, over-consuming the bucket
		$this->assertTrue($throttler->check('127.0.0.1', $rate, MINUTE, $cost));
		// but a third request isn't allowed through
		$this->assertFalse($throttler->check('127.0.0.1', $rate, MINUTE, $cost));
		// but if we sleep for a bit
		sleep(2);
		// then it will succeed
		$this->assertTrue($throttler->check('127.0.0.1', $rate, MINUTE, $cost));
	}

}
