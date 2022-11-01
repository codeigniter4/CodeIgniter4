<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Cache;

use CodeIgniter\Cache\Exceptions\CacheException;
use CodeIgniter\Cache\Handlers\DummyHandler;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Cache;

/**
 * @internal
 *
 * @group Others
 */
final class CacheFactoryTest extends CIUnitTestCase
{
    private static string $directory = 'CacheFactory';
    private CacheFactory $cacheFactory;
    private Cache $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheFactory = new CacheFactory();

        // Initialize path
        $this->config = new Cache();
        $this->config->storePath .= self::$directory;
    }

    protected function tearDown(): void
    {
        if (is_dir($this->config->storePath)) {
            chmod($this->config->storePath, 0777);
            rmdir($this->config->storePath);
        }
    }

    public function testNew()
    {
        $this->assertInstanceOf(CacheFactory::class, $this->cacheFactory);
    }

    public function testGetHandlerExceptionCacheInvalidHandlers()
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessage('Cache config must have an array of $validHandlers.');

        $this->config->validHandlers = null;

        $this->cacheFactory->getHandler($this->config);
    }

    public function testGetHandlerExceptionCacheNoBackup()
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessage('Cache config must have a handler and backupHandler set.');

        $this->config->backupHandler = null;

        $this->cacheFactory->getHandler($this->config);
    }

    public function testGetHandlerExceptionCacheNoHandler()
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessage('Cache config must have a handler and backupHandler set.');

        $this->config->handler = null;

        $this->cacheFactory->getHandler($this->config);
    }

    public function testGetHandlerExceptionCacheHandlerNotFound()
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessage('Cache config has an invalid handler or backup handler specified.');

        unset($this->config->validHandlers[$this->config->handler]);

        $this->cacheFactory->getHandler($this->config);
    }

    public function testGetDummyHandler()
    {
        if (! is_dir($this->config->storePath)) {
            mkdir($this->config->storePath, 0555, true);
        }

        $this->config->handler = 'dummy';

        $this->assertInstanceOf(DummyHandler::class, $this->cacheFactory->getHandler($this->config));

        // Initialize path
        $this->config = new Cache();
        $this->config->storePath .= self::$directory;
    }

    public function testHandlesBadHandler()
    {
        if (! is_dir($this->config->storePath)) {
            mkdir($this->config->storePath, 0555, true);
        }

        $this->config->handler = 'dummy';

        if (stripos('win', php_uname()) === 0) {
            $this->assertTrue(true); // can't test properly if we are on Windows
        } else {
            $this->assertInstanceOf(DummyHandler::class, $this->cacheFactory->getHandler($this->config, 'wincache', 'wincache'));
        }

        // Initialize path
        $this->config = new Cache();
        $this->config->storePath .= self::$directory;
    }
}
