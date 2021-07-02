<?php

namespace CodeIgniter\Cache\Handlers;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Cache;
use Exception;

/**
 * @internal
 */
final class MemcachedHandlerTest extends CIUnitTestCase
{
    private $memcachedHandler;
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

        $this->memcachedHandler = new MemcachedHandler($this->config);

        $this->memcachedHandler->initialize();
    }

    protected function tearDown(): void
    {
        foreach (self::getKeyArray() as $key) {
            $this->memcachedHandler->delete($key);
        }
    }

    public function testNew()
    {
        $this->assertInstanceOf(MemcachedHandler::class, $this->memcachedHandler);
    }

    /**
     * This test waits for 3 seconds before last assertion so this
     * is naturally a "slow" test on the perspective of the default limit.
     *
     * @timeLimit 3.5
     */
    public function testGet()
    {
        $this->memcachedHandler->save(self::$key1, 'value', 2);

        $this->assertSame('value', $this->memcachedHandler->get(self::$key1));
        $this->assertNull($this->memcachedHandler->get(self::$dummy));

        CLI::wait(3);
        $this->assertNull($this->memcachedHandler->get(self::$key1));
    }

    /**
     * This test waits for 3 seconds before last assertion so this
     * is naturally a "slow" test on the perspective of the default limit.
     *
     * @timeLimit 3.5
     */
    public function testRemember()
    {
        $this->memcachedHandler->remember(self::$key1, 2, static function () {
            return 'value';
        });

        $this->assertSame('value', $this->memcachedHandler->get(self::$key1));
        $this->assertNull($this->memcachedHandler->get(self::$dummy));

        CLI::wait(3);
        $this->assertNull($this->memcachedHandler->get(self::$key1));
    }

    public function testSave()
    {
        $this->assertTrue($this->memcachedHandler->save(self::$key1, 'value'));
    }

    public function testDelete()
    {
        $this->memcachedHandler->save(self::$key1, 'value');

        $this->assertTrue($this->memcachedHandler->delete(self::$key1));
        $this->assertFalse($this->memcachedHandler->delete(self::$dummy));
    }

    public function testDeleteMatching()
    {
        // Not implemented for Memcached, should throw an exception
        $this->expectException(Exception::class);

        $this->memcachedHandler->deleteMatching('key*');
    }

    public function testIncrement()
    {
        $this->memcachedHandler->save(self::$key1, 1);

        $this->assertFalse($this->memcachedHandler->increment(self::$key1, 10));

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

    public function testDecrement()
    {
        $this->memcachedHandler->save(self::$key1, 10);

        $this->assertFalse($this->memcachedHandler->decrement(self::$key1, 1));

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

    public function testClean()
    {
        $this->memcachedHandler->save(self::$key1, 1);
        $this->memcachedHandler->save(self::$key2, 'value');

        $this->assertTrue($this->memcachedHandler->clean());
    }

    public function testGetCacheInfo()
    {
        $this->memcachedHandler->save(self::$key1, 'value');

        $this->assertIsArray($this->memcachedHandler->getCacheInfo());
    }

    public function testGetMetaData()
    {
        $time = time();
        $this->memcachedHandler->save(self::$key1, 'value');

        $this->assertFalse($this->memcachedHandler->getMetaData(self::$dummy));

        $actual = $this->memcachedHandler->getMetaData(self::$key1);
        $this->assertLessThanOrEqual(60, $actual['expire'] - $time);
        $this->assertLessThanOrEqual(1, $actual['mtime'] - $time);
        $this->assertSame('value', $actual['data']);
    }

    public function testIsSupported()
    {
        $this->assertTrue($this->memcachedHandler->isSupported());
    }
}
