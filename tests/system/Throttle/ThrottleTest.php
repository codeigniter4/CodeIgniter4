<?php namespace CodeIgniter\Throttle;

use Tests\Support\Cache\Handlers\MockHandler;

class ThrottleTest extends \CIUnitTestCase
{

	protected function setUp()
	{
		parent::setUp();

		$this->cache = new MockHandler();
	}

	public function testTokenTime()
	{
		$throttler = new Throttler($this->cache);

		// token time should be zero to start
		$this->assertEquals(0, $throttler->getTokenTime());

		// as soon as we try a rate check, token time affected
		$rate = 1; // allow 1 per minute
		$cost = 1;

		// after using one slot, still good
		$throttler->check('127.0.0.1', $rate, MINUTE, $cost);
		$this->assertEquals(0, $throttler->getTokenTime());

		// after consuming a second, we have to wait
		$throttler->check('127.0.0.1', $rate, MINUTE, $cost);
		$this->assertEquals(1, $throttler->getTokenTime());
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

		$throttler->setTestTime(strtotime('+2 seconds')); // should be more tokens available
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

		$rate = 60; // allow 1 per second after the bucket is emptied
		$cost = 1;

		// Blow through the bucket in a natural way., with 1 second "grace"
		for ($i = 0; $i <= $rate; $i++)
		{
			$throttler->check('127.0.0.1', $rate, MINUTE, $cost);
		}

		// Should be empty now.
		$this->assertFalse($throttler->check('127.0.0.1', $rate, MINUTE, $cost));
		$this->assertEquals(0, $this->cache->get('throttler_127.0.0.1'));

		$throttler = $throttler->setTestTime(strtotime('+10 seconds'));

		$this->assertTrue($throttler->check('127.0.0.1', $rate, MINUTE, 0));
		$this->assertEquals(10, round($this->cache->get('throttler_127.0.0.1')));
	}

}
