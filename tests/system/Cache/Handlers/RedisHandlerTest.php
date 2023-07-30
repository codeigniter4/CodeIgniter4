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

/**
 * @group CacheLive
 *
 * @internal
 */
final class RedisHandlerTest extends AbstractHandlerTest
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

        if (! extension_loaded('redis')) {
            $this->markTestSkipped('redis extension not loaded.');
        }

        $this->config = new Cache();

        $this->handler = new RedisHandler($this->config);

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
        $this->assertInstanceOf(RedisHandler::class, $this->handler);
    }

    public function testDestruct(): void
    {
        $this->handler = new RedisHandler($this->config);
        $this->handler->initialize();

        $this->assertInstanceOf(RedisHandler::class, $this->handler);
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

    public function testDeleteMatchingPrefix(): void
    {
        // Save 101 items to match on
        for ($i = 1; $i <= 101; $i++) {
            $this->handler->save('key_' . $i, 'value' . $i);
        }

        // check that there are 101 items is cache store
        $dbInfo = explode(',', $this->handler->getCacheInfo()['db0']);
        $this->assertSame('keys=101', $dbInfo[0]);

        // Checking that given the prefix "key_1", deleteMatching deletes 13 keys:
        // (key_1, key_10, key_11, key_12, key_13, key_14, key_15, key_16, key_17, key_18, key_19, key_100, key_101)
        $this->assertSame(13, $this->handler->deleteMatching('key_1*'));

        // check that there remains (101 - 13) = 88 items is cache store
        $dbInfo = explode(',', $this->handler->getCacheInfo()['db0']);
        $this->assertSame('keys=88', $dbInfo[0]);
    }

    public function testDeleteMatchingSuffix(): void
    {
        // Save 101 items to match on
        for ($i = 1; $i <= 101; $i++) {
            $this->handler->save('key_' . $i, 'value' . $i);
        }

        // check that there are 101 items is cache store
        $dbInfo = explode(',', $this->handler->getCacheInfo()['db0']);
        $this->assertSame('keys=101', $dbInfo[0]);

        // Checking that given the suffix "1", deleteMatching deletes 11 keys:
        // (key_1, key_11, key_21, key_31, key_41, key_51, key_61, key_71, key_81, key_91, key_101)
        $this->assertSame(11, $this->handler->deleteMatching('*1'));

        // check that there remains (101 - 13) = 88 items is cache store
        $dbInfo = explode(',', $this->handler->getCacheInfo()['db0']);
        $this->assertSame('keys=90', $dbInfo[0]);
    }

    public function testIncrementAndDecrement(): void
    {
        $this->handler->save('counter', 100);

        foreach (range(1, 10) as $step) {
            $this->handler->increment('counter', $step);
        }

        $this->assertSame(155, $this->handler->get('counter'));

        $this->handler->decrement('counter', 20);
        $this->assertSame(135, $this->handler->get('counter'));

        $this->handler->increment('counter', 5);
        $this->assertSame(140, $this->handler->get('counter'));
    }

    public function testClean(): void
    {
        $this->handler->save(self::$key1, 1);

        $this->assertTrue($this->handler->clean());
    }

    public function testGetCacheInfo(): void
    {
        $this->handler->save(self::$key1, 'value');

        $this->assertIsArray($this->handler->getCacheInfo());
    }

    public function testGetMetadataNotNull(): void
    {
        $this->handler->save(self::$key1, 'value');

        $metadata = $this->handler->getMetaData(self::$key1);

        $this->assertNotNull($metadata);
        $this->assertIsArray($metadata);
    }

    public function testIsSupported(): void
    {
        $this->assertTrue($this->handler->isSupported());
    }
}
