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

use CodeIgniter\Cache\FactoriesCache\FileVarExportHandler;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Config\Modules;

/**
 * @internal
 * @no-final
 *
 * @group Others
 */
class FactoriesCacheFileVarExportHandlerTest extends CIUnitTestCase
{
    protected FactoriesCache $cache;

    /**
     * @var CacheInterface|FileVarExportHandler
     */
    protected $handler;

    protected function createFactoriesCache(): void
    {
        $this->handler = new FileVarExportHandler();
        $this->cache   = new FactoriesCache($this->handler);
    }

    public function testInstantiate()
    {
        $this->createFactoriesCache();

        $this->assertInstanceOf(FactoriesCache::class, $this->cache);
    }

    public function testSave()
    {
        Factories::reset();
        Factories::config('App');

        $this->createFactoriesCache();

        $this->cache->save('config');

        $cachedData = $this->handler->get('FactoriesCache_config');

        $this->assertArrayHasKey('aliases', $cachedData);
        $this->assertArrayHasKey('instances', $cachedData);
        $this->assertArrayHasKey(Modules::class, $cachedData['aliases']);
        $this->assertArrayHasKey('App', $cachedData['aliases']);
    }

    public function testLoad()
    {
        Factories::reset();
        /** @var App $appConfig */
        $appConfig          = Factories::config('App');
        $appConfig->baseURL = 'http://test.example.jp/this-is-test/';

        $this->createFactoriesCache();
        $this->cache->save('config');

        Factories::reset();

        $this->cache->load('config');

        $appConfig = Factories::config('App');
        $this->assertSame('http://test.example.jp/this-is-test/', $appConfig->baseURL);
    }

    public function testDelete()
    {
        $this->createFactoriesCache();

        $this->cache->delete('config');

        $this->assertFalse($this->cache->load('config'));
    }
}
