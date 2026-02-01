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

namespace CodeIgniter\Cache\Handlers;

use CodeIgniter\Cache\CacheFactory;
use CodeIgniter\CLI\CLI;
use CodeIgniter\I18n\Time;
use Config\Cache;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('CacheLive')]
final class PredisHandlerTest extends AbstractHandlerTestCase
{
    private Cache $config;

    /**
     * @return list<string>
     */
    private static function getKeyArray(): array
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

        $this->config  = new Cache();
        $this->handler = CacheFactory::getHandler($this->config, 'predis');
    }

    protected function tearDown(): void
    {
        foreach (self::getKeyArray() as $key) {
            $this->handler->delete($key);
        }
    }

    public function testNew(): void
    {
        $this->assertInstanceOf(PredisHandler::class, $this->handler);
    }

    public function testDestruct(): void
    {
        $this->handler = CacheFactory::getHandler($this->config, 'predis');
        $this->assertInstanceOf(PredisHandler::class, $this->handler);
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
        $this->handler->remember(self::$key1, 2, static fn (): string => 'value');

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

        $metadata = $this->handler->getMetaData(self::$key1);
        $this->assertIsArray($metadata);
        $this->assertNull($metadata['expire']);
        $this->assertLessThanOrEqual(1, $metadata['mtime'] - Time::now()->getTimestamp());
        $this->assertSame('value', $metadata['data']);

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

        $cacheInfo = $this->handler->getCacheInfo();
        $this->assertIsArray($cacheInfo);

        // check that there are 101 items is cache store
        $this->assertSame('101', $cacheInfo['Keyspace']['db0']['keys']);

        // Checking that given the prefix "key_1", deleteMatching deletes 13 keys:
        // (key_1, key_10, key_11, key_12, key_13, key_14, key_15, key_16, key_17, key_18, key_19, key_100, key_101)
        $this->assertSame(13, $this->handler->deleteMatching('key_1*'));

        // check that there remains (101 - 13) = 88 items is cache store
        $this->assertSame('88', $this->handler->getCacheInfo()['Keyspace']['db0']['keys']);
    }

    public function testDeleteMatchingSuffix(): void
    {
        // Save 101 items to match on
        for ($i = 1; $i <= 101; $i++) {
            $this->handler->save('key_' . $i, 'value' . $i);
        }

        $cacheInfo = $this->handler->getCacheInfo();
        $this->assertIsArray($cacheInfo);

        // check that there are 101 items is cache store
        $this->assertSame('101', $cacheInfo['Keyspace']['db0']['keys']);

        // Checking that given the suffix "1", deleteMatching deletes 11 keys:
        // (key_1, key_11, key_21, key_31, key_41, key_51, key_61, key_71, key_81, key_91, key_101)
        $this->assertSame(11, $this->handler->deleteMatching('*1'));

        // check that there remains (101 - 13) = 88 items is cache store
        $this->assertSame('90', $this->handler->getCacheInfo()['Keyspace']['db0']['keys']);
    }

    public function testDeleteMatchingNothing(): void
    {
        $this->assertSame(0, $this->handler->deleteMatching('user_1_info*'));
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

    public function testPing(): void
    {
        $this->assertTrue($this->handler->ping());
    }

    public function testReconnect(): void
    {
        $this->handler->save(self::$key1, 'value');
        $this->assertSame('value', $this->handler->get(self::$key1));

        $this->assertTrue($this->handler->reconnect());

        $this->assertSame('value', $this->handler->get(self::$key1));
    }
}
