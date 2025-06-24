<?php

declare(strict_types=1);

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

/**
 * @internal
 */
abstract class AbstractFactoriesCacheHandlerTestCase extends CIUnitTestCase
{
    protected FactoriesCache $cache;
    protected CacheInterface|FileVarExportHandler $handler;

    abstract protected function createFactoriesCache(): void;

    public function testInstantiate(): void
    {
        $this->createFactoriesCache();
        $this->assertInstanceOf(FactoriesCache::class, $this->cache);
    }

    public function testSave(): void
    {
        Factories::reset();
        Factories::config('App');

        $this->createFactoriesCache();

        $this->cache->save('config');

        $cachedData = $this->handler->get('FactoriesCache_config');

        $this->assertIsArray($cachedData);
        $this->assertArrayHasKey('aliases', $cachedData);
        $this->assertArrayHasKey('instances', $cachedData);
        $this->assertArrayHasKey('App', $cachedData['aliases']);
    }

    public function testLoad(): void
    {
        Factories::reset();

        /** @var App $appConfig */
        $appConfig = Factories::config('App');

        $appConfig->baseURL = 'http://test.example.jp/this-is-test/';

        $this->createFactoriesCache();
        $this->cache->save('config');

        Factories::reset();
        $this->cache->load('config');

        /** @var App $appConfig */
        $appConfig = Factories::config('App');
        $this->assertSame('http://test.example.jp/this-is-test/', $appConfig->baseURL);
    }

    public function testDelete(): void
    {
        $this->createFactoriesCache();

        $this->cache->delete('config');

        $this->assertFalse($this->cache->load('config'));
    }
}
