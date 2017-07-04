<?php namespace CodeIgniter\Cache\Handlers;

class RedisHandlerTest extends \CIUnitTestCase
{
	private $redisHandler;
	private static $key1 = 'key1';
	private static $key2 = 'key2';
	private static $key3 = 'key3';
	private static function getKeyArray()
	{
		return [
			self::$key1, self::$key2, self::$key3
		];
	}

	private static $dummy = 'dymmy';
	private $config;

	public function setUp()
	{
		$this->config = new \Config\Cache();

		$this->redisHandler = new RedisHandler($this->config->redis);
		if (!$this->redisHandler->isSupported()) {
			$this->markTestSkipped('Not support redis');
		}

		$this->redisHandler->initialize();
	}

	public function tearDown()
	{
		foreach (self::getKeyArray() as $key) {
			$this->redisHandler->delete($key);
		}
	}

	public function testNew()
	{
		$this->assertInstanceOf(RedisHandler::class, $this->redisHandler);
	}

	public function testDestruct()
	{
		$this->redisHandler = new RedisHandler($this->config->redis);
		$this->redisHandler->initialize();

		$this->assertInstanceOf(RedisHandler::class, $this->redisHandler);
	}


	public function testGet()
	{
		$this->redisHandler->save(self::$key1, 'value', 1);

		$this->assertSame('value', $this->redisHandler->get(self::$key1));
		$this->assertFalse($this->redisHandler->get(self::$dummy));

		\CodeIgniter\CLI\CLI::wait(2);
		$this->assertFalse($this->redisHandler->get(self::$key1));
	}

	public function testSave()
	{
		$this->assertTrue($this->redisHandler->save(self::$key1, 'value'));
	}

	public function testDelete()
	{
		$this->redisHandler->save(self::$key1, 'value');

		$this->assertTrue($this->redisHandler->delete(self::$key1));
		$this->assertFalse($this->redisHandler->delete(self::$dummy));
	}

	//FIXME: I don't like all Hash logic very much. It's wasting memory.
	//public function testIncrement()
	//{
	//}

	//public function testDecrement()
	//{
	//}

	public function testClean()
	{
		$this->redisHandler->save(self::$key1, 1);
		$this->redisHandler->save(self::$key2, 'value');

		$this->assertTrue($this->redisHandler->clean());
	}

	public function testGetCacheInfo()
	{
		$this->redisHandler->save(self::$key1, 'value');

		$this->assertInternalType('array', $this->redisHandler->getCacheInfo());
	}

	public function testGetMetaData()
	{
		$time = time();
		$this->redisHandler->save(self::$key1, 'value');

		$this->assertFalse($this->redisHandler->getMetaData(self::$dummy));

		$actual = $this->redisHandler->getMetaData(self::$key1);
		$this->assertLessThanOrEqual(60, $actual['expire'] - $time);
		$this->assertLessThanOrEqual(0, $actual['mtime'] - $time);
		$this->assertSame('value', $actual['data']);
	}

	public function testIsSupported()
	{
		$this->assertTrue($this->redisHandler->isSupported());
	}
}
