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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('CacheLive')]
final class RedisHandlerTest extends AbstractHandlerTestCase
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

    #[DataProvider('provideDeleteMatching')]
    public function testDeleteMatching(string $pattern, int $expectedDeleteCount, string $prefix = ''): void
    {
        $cache = new Cache();

        if ($prefix !== '') {
            $cache->prefix = $prefix;
        }

        /** @var RedisHandler $handler */
        $handler = CacheFactory::getHandler($cache, 'redis');

        for ($i = 1; $i <= 101; $i++) {
            $handler->save('key_' . $i, 'value_' . $i);
        }

        $cacheInfo = $handler->getCacheInfo();
        $this->assertIsArray($cacheInfo);
        $this->assertArrayHasKey('db0', $cacheInfo);
        $this->assertIsString($cacheInfo['db0']);
        $this->assertMatchesRegularExpression('/^keys=(?P<count>\d+)/', $cacheInfo['db0']);

        preg_match('/^keys=(?P<count>\d+)/', $cacheInfo['db0'], $matches);
        $this->assertSame(101, (int) $matches['count']);

        $this->assertSame($expectedDeleteCount, $handler->deleteMatching($pattern));

        $cacheInfo = $handler->getCacheInfo();
        $this->assertIsArray($cacheInfo);
        $this->assertArrayHasKey('db0', $cacheInfo);
        $this->assertIsString($cacheInfo['db0']);
        $this->assertMatchesRegularExpression('/^keys=(?P<count>\d+)/', $cacheInfo['db0']);

        preg_match('/^keys=(?P<count>\d+)/', $cacheInfo['db0'], $matches);
        $this->assertSame(101 - $expectedDeleteCount, (int) $matches['count']);

        $handler->deleteMatching('key_*');
    }

    /**
     * @return iterable<string, array{0: string, 1: int, 2?: string}>
     */
    public static function provideDeleteMatching(): iterable
    {
        // Given the key "key_1*", deleteMatching() should delete 13 keys:
        // key_1, key_10, key_11, key_12, key_13, key_14, key_15, key_16, key_17, key_18, key_19, key_100, key_101
        yield 'prefix' => ['key_1*', 13];

        // Given the key "*1", deleteMatching() should delete 11 keys:
        // key_1, key_11, key_21, key_31, key_41, key_51, key_61, key_71, key_81, key_91, key_101
        yield 'suffix' => ['*1', 11];

        yield 'cache-prefix' => ['key_1*', 13, 'foo_'];
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
