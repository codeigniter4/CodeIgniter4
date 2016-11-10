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

}
