<?php

namespace CodeIgniter\Cache\Handlers;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Cache;

class RedisHandlerTest extends CIUnitTestCase
{
	private $redisHandler;
	private static $key1 = 'key1';
	private static $key2 = 'key2';
	private static $key3 = 'key3';
	private static function getKeyArray()
	{
		return [
			self::$key1,
			self::$key2,
			self::$key3,
		];
	}

	private static $dummy = 'dymmy';
	private $config;

	protected function setUp(): void
	{
		parent::setUp();

		$this->config = new Cache();

		$this->redisHandler = new RedisHandler($this->config);
		if (! $this->redisHandler->isSupported())
		{
			$this->markTestSkipped('Not support redis');
		}

		$this->redisHandler->initialize();
	}

	public function tearDown(): void
	{
		foreach (self::getKeyArray() as $key)
		{
			$this->redisHandler->delete($key);
		}
	}

	public function testNew()
	{
		$this->assertInstanceOf(RedisHandler::class, $this->redisHandler);
	}

	public function testDestruct()
	{
		$this->redisHandler = new RedisHandler($this->config);
		$this->redisHandler->initialize();

		$this->assertInstanceOf(RedisHandler::class, $this->redisHandler);
	}

	public function testGet()
	{
		$this->redisHandler->save(self::$key1, 'value', 2);

		$this->assertSame('value', $this->redisHandler->get(self::$key1));
		$this->assertNull($this->redisHandler->get(self::$dummy));

		CLI::wait(3);
		$this->assertNull($this->redisHandler->get(self::$key1));
	}

	public function testRemember()
	{
		$this->redisHandler->remember(self::$key1, 2, function () {
			return 'value';
		});

		$this->assertSame('value', $this->redisHandler->get(self::$key1));
		$this->assertNull($this->redisHandler->get(self::$dummy));

		CLI::wait(3);
		$this->assertNull($this->redisHandler->get(self::$key1));
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

		$this->assertIsArray($this->redisHandler->getCacheInfo());
	}

	public function testGetMetaData()
	{
		$time = time();
		$this->redisHandler->save(self::$key1, 'value');

		$this->assertNull($this->redisHandler->getMetaData(self::$dummy));

		$actual = $this->redisHandler->getMetaData(self::$key1);
		$this->assertLessThanOrEqual(60, $actual['expire'] - $time);
		$this->assertLessThanOrEqual(1, $actual['mtime'] - $time);
		$this->assertSame('value', $actual['data']);
	}

	public function testIsSupported()
	{
		$this->assertTrue($this->redisHandler->isSupported());
	}
}
