<?php namespace CodeIgniter\Cache\Handlers;

class PredisHandlerTest extends \CodeIgniter\Test\CIUnitTestCase
{
	private $PredisHandler;
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

		$this->config = new \Config\Cache();

		$this->PredisHandler = new PredisHandler($this->config);
		if (! $this->PredisHandler->isSupported())
		{
			$this->markTestSkipped('Not support Predis');
		}

		$this->PredisHandler->initialize();
	}

	public function tearDown(): void
	{
		foreach (self::getKeyArray() as $key)
		{
			$this->PredisHandler->delete($key);
		}
	}

	public function testNew()
	{
		$this->assertInstanceOf(PredisHandler::class, $this->PredisHandler);
	}

	public function testDestruct()
	{
		$this->PredisHandler = new PRedisHandler($this->config);
		$this->PredisHandler->initialize();

		$this->assertInstanceOf(PRedisHandler::class, $this->PredisHandler);
	}

	public function testGet()
	{
		$this->PredisHandler->save(self::$key1, 'value', 2);

		$this->assertSame('value', $this->PredisHandler->get(self::$key1));
		$this->assertNull($this->PredisHandler->get(self::$dummy));

		\CodeIgniter\CLI\CLI::wait(3);
		$this->assertNull($this->PredisHandler->get(self::$key1));
	}

	public function testSave()
	{
		$this->assertTrue($this->PredisHandler->save(self::$key1, 'value'));
	}

	public function testDelete()
	{
		$this->PredisHandler->save(self::$key1, 'value');

		$this->assertTrue($this->PredisHandler->delete(self::$key1));
		$this->assertFalse($this->PredisHandler->delete(self::$dummy));
	}

	public function testClean()
	{
		$this->PredisHandler->save(self::$key1, 1);
		$this->PredisHandler->save(self::$key2, 'value');

		$this->assertTrue($this->PredisHandler->clean());
	}

	public function testGetCacheInfo()
	{
		$this->PredisHandler->save(self::$key1, 'value');

		$this->assertIsArray($this->PredisHandler->getCacheInfo());
	}

	public function testGetMetaData()
	{
		$time = time();
		$this->PredisHandler->save(self::$key1, 'value');

		$this->assertNull($this->PredisHandler->getMetaData(self::$dummy));

		$actual = $this->PredisHandler->getMetaData(self::$key1);
		$this->assertLessThanOrEqual(60, $actual['expire'] - $time);
		$this->assertLessThanOrEqual(0, $actual['mtime'] - $time);
		$this->assertSame('value', $actual['data']);
	}

	public function testIsSupported()
	{
		$this->assertTrue($this->PredisHandler->isSupported());
	}
}
