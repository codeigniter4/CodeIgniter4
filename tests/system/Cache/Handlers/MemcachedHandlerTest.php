<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Cache\Handlers;

use CodeIgniter\CLI\CLI;
use CodeIgniter\I18n\Time;
use Config\Cache;
use Exception;

/**
 * @group CacheLive
 *
 * @internal
 */
final class MemcachedHandlerTest extends AbstractHandlerTest
{
    private Cache $config;

    private static function getKeyArray()
    {
        return [
            self::$key1,
            self::$key2,
            self::$key3,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('memcached')) {
            $this->markTestSkipped('Memcached extension not loaded.');
        }

        $this->config = new Cache();

        $this->handler = new MemcachedHandler($this->config);

        $this->handler->initialize();
    }

    protected function tearDown(): void
    {
        foreach (self::getKeyArray() as $key) {
            $this->handler->delete($key);
        }
    }

    public function testNew(): void
    {
        $this->assertInstanceOf(MemcachedHandler::class, $this->handler);
    }

    /**
     * This test waits for 3 seconds before last assertion so this
     * is naturally a "slow" test on the perspective of the default limit.
     *
     * @timeLimit 3.5
     */
    public function testGet(): void
    {
        $this->handler->save(self::$key1, 'value', 2);

        $this->assertSame('value', $this->handler->get(self::$key1));
        $this->assertNull($this->handler->get(self::$dummy));

        CLI::wait(3);
        $this->assertNull($this->handler->get(self::$key1));
    }

    /**
     * This test waits for 3 seconds before last assertion so this
     * is naturally a "slow" test on the perspective of the default limit.
     *
     * @timeLimit 3.5
     */
    public function testRemember(): void
    {
        $this->handler->remember(self::$key1, 2, static fn () => 'value');

        $this->assertSame('value', $this->handler->get(self::$key1));
        $this->assertNull($this->handler->get(self::$dummy));

        CLI::wait(3);
        $this->assertNull($this->handler->get(self::$key1));
    }

    public function testSave(): void
    {
        $this->assertTrue($this->handler->save(self::$key1, 'value'));
    }

    public function testSavePermanent(): void
    {
        $this->assertTrue($this->handler->save(self::$key1, 'value', 0));
        $metaData = $this->handler->getMetaData(self::$key1);

        $this->assertNull($metaData['expire']);
        $this->assertLessThanOrEqual(1, $metaData['mtime'] - Time::now()->getTimestamp());
        $this->assertSame('value', $metaData['data']);

        $this->assertTrue($this->handler->delete(self::$key1));
    }

    public function testDelete(): void
    {
        $this->handler->save(self::$key1, 'value');

        $this->assertTrue($this->handler->delete(self::$key1));
        $this->assertFalse($this->handler->delete(self::$dummy));
    }

    public function testDeleteMatching(): void
    {
        // Not implemented for Memcached, should throw an exception
        $this->expectException(Exception::class);

        $this->handler->deleteMatching('key*');
    }

    public function testIncrement(): void
    {
        $this->handler->save(self::$key1, 1);

        $this->assertFalse($this->handler->increment(self::$key1, 10));

        $config                   = new Cache();
        $config->memcached['raw'] = true;
        $memcachedHandler         = new MemcachedHandler($config);
        $memcachedHandler->initialize();

        $memcachedHandler->save(self::$key1, 1);
        $memcachedHandler->save(self::$key2, 'value');

        $this->assertSame(11, $memcachedHandler->increment(self::$key1, 10));
        $this->assertFalse($memcachedHandler->increment(self::$key2, 10));
        $this->assertSame(10, $memcachedHandler->increment(self::$key3, 10));
    }

    public function testDecrement(): void
    {
        $this->handler->save(self::$key1, 10);

        $this->assertFalse($this->handler->decrement(self::$key1, 1));

        $config                   = new Cache();
        $config->memcached['raw'] = true;
        $memcachedHandler         = new MemcachedHandler($config);
        $memcachedHandler->initialize();

        $memcachedHandler->save(self::$key1, 10);
        $memcachedHandler->save(self::$key2, 'value');

        $this->assertSame(9, $memcachedHandler->decrement(self::$key1, 1));
        $this->assertFalse($memcachedHandler->decrement(self::$key2, 1));
        $this->assertSame(1, $memcachedHandler->decrement(self::$key3, 1));
    }

    public function testClean(): void
    {
        $this->handler->save(self::$key1, 1);
        $this->handler->save(self::$key2, 'value');

        $this->assertTrue($this->handler->clean());
    }

    public function testGetCacheInfo(): void
    {
        $this->handler->save(self::$key1, 'value');

        $this->assertIsArray($this->handler->getCacheInfo());
    }

    public function testIsSupported(): void
    {
        $this->assertTrue($this->handler->isSupported());
    }

    public function testGetMetaDataMiss(): void
    {
        $this->assertFalse($this->handler->getMetaData(self::$dummy));
    }
}
