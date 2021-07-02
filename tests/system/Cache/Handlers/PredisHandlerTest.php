<?php

namespace CodeIgniter\Cache\Handlers;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Cache;

/**
 * @internal
 */
final class PredisHandlerTest extends CIUnitTestCase
{
    private $PredisHandler;
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

        $this->PredisHandler = new PredisHandler($this->config);

        $this->PredisHandler->initialize();
    }

    protected function tearDown(): void
    {
        foreach (self::getKeyArray() as $key) {
            $this->PredisHandler->delete($key);
        }
    }

    public function testNew()
    {
        $this->assertInstanceOf(PredisHandler::class, $this->PredisHandler);
    }

    public function testDestruct()
    {
        $this->PredisHandler = new PRedisHandler($this->config);
        $this->PredisHandler->initialize();

        $this->assertInstanceOf(PRedisHandler::class, $this->PredisHandler);
    }

    /**
     * This test waits for 3 seconds before last assertion so this
     * is naturally a "slow" test on the perspective of the default limit.
     *
     * @timeLimit 3.5
     */
    public function testGet()
    {
        $this->PredisHandler->save(self::$key1, 'value', 2);

        $this->assertSame('value', $this->PredisHandler->get(self::$key1));
        $this->assertNull($this->PredisHandler->get(self::$dummy));

        CLI::wait(3);
        $this->assertNull($this->PredisHandler->get(self::$key1));
    }

    /**
     * This test waits for 3 seconds before last assertion so this
     * is naturally a "slow" test on the perspective of the default limit.
     *
     * @timeLimit 3.5
     */
    public function testRemember()
    {
        $this->PredisHandler->remember(self::$key1, 2, static function () {
            return 'value';
        });

        $this->assertSame('value', $this->PredisHandler->get(self::$key1));
        $this->assertNull($this->PredisHandler->get(self::$dummy));

        CLI::wait(3);
        $this->assertNull($this->PredisHandler->get(self::$key1));
    }

    public function testSave()
    {
        $this->assertTrue($this->PredisHandler->save(self::$key1, 'value'));
    }

    public function testDelete()
    {
        $this->PredisHandler->save(self::$key1, 'value');

        $this->assertTrue($this->PredisHandler->delete(self::$key1));
        $this->assertFalse($this->PredisHandler->delete(self::$dummy));
    }

    public function testDeleteMatchingPrefix()
    {
        // Save 101 items to match on
        for ($i = 1; $i <= 101; $i++) {
            $this->PredisHandler->save('key_' . $i, 'value' . $i);
        }

        // check that there are 101 items is cache store
        $this->assertSame('101', $this->PredisHandler->getCacheInfo()['Keyspace']['db0']['keys']);

        // Checking that given the prefix "key_1", deleteMatching deletes 13 keys:
        // (key_1, key_10, key_11, key_12, key_13, key_14, key_15, key_16, key_17, key_18, key_19, key_100, key_101)
        $this->assertSame(13, $this->PredisHandler->deleteMatching('key_1*'));

        // check that there remains (101 - 13) = 88 items is cache store
        $this->assertSame('88', $this->PredisHandler->getCacheInfo()['Keyspace']['db0']['keys']);
    }

    public function testDeleteMatchingSuffix()
    {
        // Save 101 items to match on
        for ($i = 1; $i <= 101; $i++) {
            $this->PredisHandler->save('key_' . $i, 'value' . $i);
        }

        // check that there are 101 items is cache store
        $this->assertSame('101', $this->PredisHandler->getCacheInfo()['Keyspace']['db0']['keys']);

        // Checking that given the suffix "1", deleteMatching deletes 11 keys:
        // (key_1, key_11, key_21, key_31, key_41, key_51, key_61, key_71, key_81, key_91, key_101)
        $this->assertSame(11, $this->PredisHandler->deleteMatching('*1'));

        // check that there remains (101 - 13) = 88 items is cache store
        $this->assertSame('90', $this->PredisHandler->getCacheInfo()['Keyspace']['db0']['keys']);
    }

    public function testClean()
    {
        $this->PredisHandler->save(self::$key1, 1);
        $this->PredisHandler->save(self::$key2, 'value');

        $this->assertTrue($this->PredisHandler->clean());
    }

    public function testGetCacheInfo()
    {
        $this->PredisHandler->save(self::$key1, 'value');

        $this->assertIsArray($this->PredisHandler->getCacheInfo());
    }

    public function testGetMetaData()
    {
        $time = time();
        $this->PredisHandler->save(self::$key1, 'value');

        $this->assertNull($this->PredisHandler->getMetaData(self::$dummy));

        $actual = $this->PredisHandler->getMetaData(self::$key1);
        $this->assertLessThanOrEqual(60, $actual['expire'] - $time);
        $this->assertLessThanOrEqual(1, $actual['mtime'] - $time);
        $this->assertSame('value', $actual['data']);
    }

    public function testIsSupported()
    {
        $this->assertTrue($this->PredisHandler->isSupported());
    }
}
