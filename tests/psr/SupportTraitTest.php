<?php

use CodeIgniter\Cache\Handlers\DummyHandler;
use CodeIgniter\Psr\Cache\CacheArgumentException;
use CodeIgniter\Psr\Cache\SimpleCache;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockCache;
use Config\Cache;
use Config\Services;

class SupportTraitTest extends CIUnitTestCase
{
	public function setUp(): void
	{
		parent::setUp();

		Services::resetSingle('cache');
	}

	public function testDefaultUsesSharedInstance()
	{
		Services::injectMock('cache', new MockCache());

		$psr    = new SimpleCache();
		$result = $this->getPrivateProperty($psr, 'adapter');

		$this->assertInstanceOf(MockCache::class, $result);
	}

	public function testUsesConfig()
	{
		$config          = new Cache();
		$config->handler = 'dummy';

		$psr    = new SimpleCache($config);
		$result = $this->getPrivateProperty($psr, 'adapter');

		$this->assertInstanceOf(DummyHandler::class, $result);
	}

	public function testUsesHandler()
	{
		$psr    = new SimpleCache(new MockCache());
		$result = $this->getPrivateProperty($psr, 'adapter');

		$this->assertInstanceOf(MockCache::class, $result);
	}

	public function testThrowsException()
	{
		$this->expectException(CacheArgumentException::class);
		$this->expectExceptionMessage('CodeIgniter\Psr\Cache\SimpleCache constructor only accepts an adapter or configuration');

		$psr = new SimpleCache(42);
	}
}
