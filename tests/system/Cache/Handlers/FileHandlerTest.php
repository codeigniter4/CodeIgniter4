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

use CodeIgniter\Cache\Exceptions\CacheException;
use CodeIgniter\CLI\CLI;
use CodeIgniter\I18n\Time;
use Config\Cache;

/**
 * @internal
 *
 * @group Others
 */
final class FileHandlerTest extends AbstractHandlerTest
{
    private static string $directory = 'FileHandler';
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

        if (! function_exists('octal_permissions')) {
            helper('filesystem');
        }

        // Initialize path
        $this->config = new Cache();
        $this->config->file['storePath'] .= self::$directory;

        if (! is_dir($this->config->file['storePath'])) {
            mkdir($this->config->file['storePath'], 0777, true);
        }

        $this->handler = new FileHandler($this->config);
        $this->handler->initialize();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (is_dir($this->config->file['storePath'])) {
            chmod($this->config->file['storePath'], 0777);

            foreach (self::getKeyArray() as $key) {
                if (is_file($this->config->file['storePath'] . DIRECTORY_SEPARATOR . $key)) {
                    chmod($this->config->file['storePath'] . DIRECTORY_SEPARATOR . $key, 0777);
                    unlink($this->config->file['storePath'] . DIRECTORY_SEPARATOR . $key);
                }
            }

            rmdir($this->config->file['storePath']);
        }
    }

    public function testNew(): void
    {
        $this->assertInstanceOf(FileHandler::class, $this->handler);
    }

    /**
     * chmod('path', 0444) does not work on Windows
     *
     * @requires OS Linux|Darwin
     */
    public function testNewWithNonWritablePath(): void
    {
        $this->expectException(CacheException::class);

        chmod($this->config->file['storePath'], 0444);
        new FileHandler($this->config);
    }

    public function testSetDefaultPath(): void
    {
        // Initialize path
        $config                    = new Cache();
        $config->file['storePath'] = null;

        $this->handler = new FileHandler($config);
        $this->handler->initialize();

        $this->assertInstanceOf(FileHandler::class, $this->handler);
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

    /**
     * chmod('path', 0444) does not work on Windows
     *
     * @requires OS Linux|Darwin
     */
    public function testSave(): void
    {
        $this->assertTrue($this->handler->save(self::$key1, 'value'));

        chmod($this->config->file['storePath'], 0444);
        $this->assertFalse($this->handler->save(self::$key2, 'value'));
    }

    public function testSaveExcessiveKeyLength(): void
    {
        $key  = str_repeat('a', 260);
        $file = $this->config->file['storePath'] . DIRECTORY_SEPARATOR . md5($key);

        $this->assertTrue($this->handler->save($key, 'value'));
        $this->assertFileExists($file);

        unlink($file);
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
        $this->assertCount(101, $this->handler->getCacheInfo());

        // Checking that given the prefix "key_1", deleteMatching deletes 13 keys:
        // (key_1, key_10, key_11, key_12, key_13, key_14, key_15, key_16, key_17, key_18, key_19, key_100, key_101)
        $this->assertSame(13, $this->handler->deleteMatching('key_1*'));

        // check that there remains (101 - 13) = 88 items is cache store
        $this->assertCount(88, $this->handler->getCacheInfo());

        // Clear all files
        $this->handler->clean();
    }

    public function testDeleteMatchingSuffix(): void
    {
        // Save 101 items to match on
        for ($i = 1; $i <= 101; $i++) {
            $this->handler->save('key_' . $i, 'value' . $i);
        }

        // check that there are 101 items is cache store
        $this->assertCount(101, $this->handler->getCacheInfo());

        // Checking that given the suffix "1", deleteMatching deletes 11 keys:
        // (key_1, key_11, key_21, key_31, key_41, key_51, key_61, key_71, key_81, key_91, key_101)
        $this->assertSame(11, $this->handler->deleteMatching('*1'));

        // check that there remains (101 - 13) = 88 items is cache store
        $this->assertCount(90, $this->handler->getCacheInfo());

        // Clear all files
        $this->handler->clean();
    }

    public function testIncrement(): void
    {
        $this->handler->save(self::$key1, 1);
        $this->handler->save(self::$key2, 'value');

        $this->assertSame(11, $this->handler->increment(self::$key1, 10));
        $this->assertFalse($this->handler->increment(self::$key2, 10));
        $this->assertSame(10, $this->handler->increment(self::$key3, 10));
    }

    public function testDecrement(): void
    {
        $this->handler->save(self::$key1, 10);
        $this->handler->save(self::$key2, 'value');

        // Line following commented out to force the cache to add a zero entry for key3
        // $this->fileHandler->save(self::$key3, 0);

        $this->assertSame(9, $this->handler->decrement(self::$key1, 1));
        $this->assertFalse($this->handler->decrement(self::$key2, 1));
        $this->assertSame(-1, $this->handler->decrement(self::$key3, 1));
    }

    public function testClean(): void
    {
        $this->handler->save(self::$key1, 1);
        $this->handler->save(self::$key2, 'value');

        $this->assertTrue($this->handler->clean());

        $this->handler->save(self::$key1, 1);
        $this->handler->save(self::$key2, 'value');
    }

    public function testGetCacheInfo(): void
    {
        $this->handler->save(self::$key1, 'value');

        $actual = $this->handler->getCacheInfo();
        $this->assertArrayHasKey(self::$key1, $actual);
        $this->assertSame(self::$key1, $actual[self::$key1]['name']);
        $this->assertArrayHasKey('server_path', $actual[self::$key1]);
    }

    public function testIsSupported(): void
    {
        $this->assertTrue($this->handler->isSupported());
    }

    /**
     * @dataProvider provideSaveMode
     *
     * permissions given on Windows are fixed to `0666`
     *
     * @requires OS Linux|Darwin
     */
    public function testSaveMode(int $int, string $string): void
    {
        // Initialize mode
        $config               = new Cache();
        $config->file['mode'] = $int;

        $this->handler = new FileHandler($config);
        $this->handler->initialize();

        $this->handler->save(self::$key1, 'value');

        $file = $config->file['storePath'] . DIRECTORY_SEPARATOR . self::$key1;
        $mode = octal_permissions(fileperms($file));

        $this->assertSame($string, $mode);
    }

    public static function provideSaveMode(): iterable
    {
        return [
            [
                0640,
                '640',
            ],
            [
                0600,
                '600',
            ],
            [
                0660,
                '660',
            ],
            [
                0777,
                '777',
            ],
        ];
    }

    public function testFileHandler(): void
    {
        $fileHandler = new BaseTestFileHandler();

        $actual = $fileHandler->getFileInfoTest();

        $this->assertArrayHasKey('server_path', $actual);
        $this->assertArrayHasKey('size', $actual);
        $this->assertArrayHasKey('date', $actual);
        $this->assertArrayHasKey('readable', $actual);
        $this->assertArrayHasKey('writable', $actual);
        $this->assertArrayHasKey('executable', $actual);
        $this->assertArrayHasKey('fileperms', $actual);
    }

    public function testGetMetaDataMiss(): void
    {
        $this->assertFalse($this->handler->getMetaData(self::$dummy));
    }
}

final class BaseTestFileHandler extends FileHandler
{
    private static string $directory = 'FileHandler';
    private Cache $config;

    public function __construct()
    {
        $this->config = new Cache();
        $this->config->file['storePath'] .= self::$directory;

        parent::__construct($this->config);
    }

    public function getFileInfoTest()
    {
        $tmpHandle = tmpfile();
        stream_get_meta_data($tmpHandle);

        return $this->getFileInfo(stream_get_meta_data($tmpHandle)['uri'], [
            'name',
            'server_path',
            'size',
            'date',
            'readable',
            'writable',
            'executable',
            'fileperms',
        ]);
    }
}
