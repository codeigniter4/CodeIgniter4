<?php namespace CodeIgniter\Cache;

class CacheFactoryTest extends \CIUnitTestCase
{
	private static $directory = 'CacheFactory';
	private $cacheFactory;
	private $config;

	public function setUp()
	{
		$this->cacheFactory = new CacheFactory();

		//Initialize path
		$this->config = new \Config\Cache();
		$this->config->path .= self::$directory;
	}

	public function tearDown()
	{
		if (is_dir($this->config->path)) {
			chmod($this->config->path, 0777);
			rmdir($this->config->path);
		}
	}

	public function testNew()
	{
		$this->assertInstanceOf(CacheFactory::class, $this->cacheFactory);
	}

	/**
	 * @expectedException        InvalidArgumentException
	 * @expectedExceptionMessage Cache config must have an array of $validHandlers.
	 */
	public function testGetHandlerExceptionCacheInvalidHandlers()
	{
		$this->config->validHandlers = null;

		$this->cacheFactory->getHandler($this->config);
	}

	/**
	 * @expectedException        InvalidArgumentException
	 * @expectedExceptionMessage Cache config must have a handler and backupHandler set.
	 */
	public function testGetHandlerExceptionCacheNoBackup()
	{
		$this->config->backupHandler = null;

		$this->cacheFactory->getHandler($this->config);
	}

	/**
	 * @expectedException        InvalidArgumentException
	 * @expectedExceptionMessage Cache config must have a handler and backupHandler set.
	 */
	public function testGetHandlerExceptionCacheNoHandler()
	{
		$this->config->handler = null;

		$this->cacheFactory->getHandler($this->config);
	}

	/**
	 * @expectedException        InvalidArgumentException
	 * @expectedExceptionMessage Cache config has an invalid handler or backup handler specified.
	 */
	public function testGetHandlerExceptionCacheHandlerNotFound()
	{
		unset($this->config->validHandlers[$this->config->handler]);

		$this->cacheFactory->getHandler($this->config);
	}

	public function testGetDummyHandler()
	{
		if (!is_dir($this->config->path)) {
			mkdir($this->config->path, 0555, true);
		}

		$this->config->backupHandler = 'file';

		$this->assertInstanceOf(\CodeIgniter\Cache\Handlers\DummyHandler::class, $this->cacheFactory->getHandler($this->config));

		//Initialize path
		$this->config = new \Config\Cache();
		$this->config->path .= self::$directory;
	}
}
