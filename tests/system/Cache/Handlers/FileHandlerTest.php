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
use CodeIgniter\Cache\Exceptions\CacheException;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\I18n\Time;
use Config\Cache;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresOperatingSystem;

/**
 * @internal
 */
#[Group('Others')]
final class FileHandlerTest extends AbstractHandlerTestCase
{
    private static string $directory = 'FileHandler';
    private Cache $config;
    private string $originalReservedCharacters;

    protected function setUp(): void
    {
        parent::setUp();

        if (! function_exists('octal_permissions')) {
            helper('filesystem');
        }

        // Restored in tearDown() so a relaxed setting cannot leak into another test.
        $this->originalReservedCharacters = config('Cache')->reservedCharacters;

        // Initialize path
        $this->config = new Cache();
        $this->config->file['storePath'] .= self::$directory;

        if (! is_dir($this->config->file['storePath'])) {
            mkdir($this->config->file['storePath'], 0777, true);
        }

        $this->handler = CacheFactory::getHandler($this->config, 'file');
    }

    protected function tearDown(): void
    {
        if (isset($this->originalReservedCharacters)) {
            config('Cache')->reservedCharacters = $this->originalReservedCharacters;
        }

        parent::tearDown();

        if (is_dir($this->config->file['storePath'])) {
            chmod($this->config->file['storePath'], 0777);

            if (isset($this->handler)) {
                $this->handler->clean();
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
     */
    #[RequiresOperatingSystem('Linux|Darwin')]
    public function testNewWithNonWritablePath(): void
    {
        $this->expectException(CacheException::class);

        chmod($this->config->file['storePath'], 0444);
        CacheFactory::getHandler($this->config, 'file');
    }

    public function testSetDefaultPath(): void
    {
        // Initialize path
        $config = new Cache();
        unset($config->file['storePath']);

        $this->assertInstanceOf(FileHandler::class, CacheFactory::getHandler($config, 'file'));
    }

    /**
     * This test waits for 3 seconds before last assertion so this
     * is naturally a "slow" test on the perspective of the default limit.
     *
     * @timeLimit 3.5
     */
    public function testGet(): void
    {
        $this->assertTrue($this->handler->save(self::$key1, 'value', 2));

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

    /**
     * chmod('path', 0444) does not work on Windows
     */
    #[RequiresOperatingSystem('Linux|Darwin')]
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

    /**
     * @param string $pattern "{victim}" is replaced with the name of a file in the parent of the cache directory
     */
    #[DataProvider('provideDeleteMatchingDoesNotDeleteFilesOutsideCacheDirectory')]
    public function testDeleteMatchingDoesNotDeleteFilesOutsideCacheDirectory(string $pattern): void
    {
        $parent = realpath(dirname($this->config->file['storePath']));
        $this->assertNotFalse($parent);

        $victim = tempnam($parent, 'deleteMatchingVictim');
        $this->assertNotFalse($victim);

        try {
            // tempnam() falls back to the system temp directory on failure.
            $this->assertSame($parent, realpath(dirname($victim)));

            $this->assertTrue($this->handler->save(self::$key1, 'value'));

            $pattern = str_replace('{victim}', basename($victim), $pattern);

            $this->assertSame(0, $this->handler->deleteMatching($pattern));
            $this->assertFileExists($victim);
            $this->assertSame('value', $this->handler->get(self::$key1));
        } finally {
            if (is_file($victim)) {
                unlink($victim);
            }
        }
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideDeleteMatchingDoesNotDeleteFilesOutsideCacheDirectory(): iterable
    {
        yield 'parent directory' => ['../{victim}'];

        yield 'parent directory with wildcard' => ['../{victim}*'];

        yield 'parent directory with backslash' => ['..\\{victim}'];

        yield 'parent directory only' => ['..'];
    }

    public function testDeleteMatchingDoesNotDeleteFilesInSubdirectories(): void
    {
        $directoryName = 'dir_' . bin2hex(random_bytes(8));
        $subdirectory  = $this->config->file['storePath'] . '/' . $directoryName . '/';

        $this->assertTrue(mkdir($subdirectory));

        try {
            $this->assertNotFalse(file_put_contents($subdirectory . 'key_1', 'data'));

            $this->assertSame(0, $this->handler->deleteMatching($directoryName . '/key_*'));
            $this->assertFileExists($subdirectory . 'key_1');
        } finally {
            if (is_file($subdirectory . 'key_1')) {
                unlink($subdirectory . 'key_1');
            }

            rmdir($subdirectory);
        }
    }

    #[RequiresOperatingSystem('Linux|Darwin')]
    public function testDeleteMatchingSupportsEscapedWildcards(): void
    {
        $this->assertTrue($this->handler->save('key*1', 'value'));
        $this->assertTrue($this->handler->save('key_1', 'value'));

        $this->assertSame(1, $this->handler->deleteMatching('key\*1'));
        $this->assertNull($this->handler->get('key*1'));
        $this->assertSame('value', $this->handler->get('key_1'));
    }

    public function testDeleteMatchingTreatsCachePathLiterally(): void
    {
        // As a glob pattern, "cache[1]" would match the sibling "cache1" instead of itself.
        $cachePath   = $this->config->file['storePath'] . '/cache[1]/';
        $siblingPath = $this->config->file['storePath'] . '/cache1/';

        $this->assertTrue(mkdir($cachePath));
        $this->assertTrue(mkdir($siblingPath));

        try {
            $this->assertNotFalse(file_put_contents($siblingPath . 'key_1', 'data'));

            $config                    = new Cache();
            $config->file['storePath'] = $cachePath;
            $handler                   = CacheFactory::getHandler($config, 'file');

            $this->assertTrue($handler->save('key_1', 'value'));

            $this->assertSame(1, $handler->deleteMatching('*'));
            $this->assertNull($handler->get('key_1'));
            $this->assertFileExists($siblingPath . 'key_1');
        } finally {
            foreach ([$cachePath, $siblingPath] as $path) {
                if (is_file($path . 'key_1')) {
                    unlink($path . 'key_1');
                }

                rmdir($path);
            }
        }
    }

    public function testDeleteMatchingWildcardDoesNotMatchHiddenFiles(): void
    {
        $hidden = $this->config->file['storePath'] . '/.hidden';

        $this->assertNotFalse(file_put_contents($hidden, 'data'));

        try {
            $this->assertTrue($this->handler->save(self::$key1, 'value'));

            $this->assertSame(1, $this->handler->deleteMatching('*'));
            $this->assertFileExists($hidden);
        } finally {
            if (is_file($hidden)) {
                unlink($hidden);
            }
        }
    }

    public function testIncrement(): void
    {
        $this->handler->save(self::$key1, 1);
        $this->handler->save(self::$key2, 'value');

        $this->assertSame(11, $this->handler->increment(self::$key1, 10));
        $this->assertFalse($this->handler->increment(self::$key2, 10));
        $this->assertSame(10, $this->handler->increment(self::$key3, 10));
    }

    public function testIncrementWithDefaultPrefix(): void
    {
        $this->config->prefix = 'test_';

        $this->handler = CacheFactory::getHandler($this->config, 'file');
        $this->handler->save(self::$key1, 1);
        $this->handler->save(self::$key2, 'value');

        $this->assertSame(11, $this->handler->increment(self::$key1, 10));
        $this->assertSame($this->handler->increment(self::$key1, 10), $this->handler->get(self::$key1));
        $this->assertFalse($this->handler->increment(self::$key2, 10));
        $this->assertSame(10, $this->handler->increment(self::$key3, 10));
        $this->assertSame($this->handler->increment(self::$key3, 10), $this->handler->get(self::$key3));
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

    public function testDecrementWithDefaultPrefix(): void
    {
        $this->handler->save(self::$key1, 10);
        $this->handler->save(self::$key2, 'value');

        // Line following commented out to force the cache to add a zero entry for key3
        // $this->fileHandler->save(self::$key3, 0);

        $this->assertSame(9, $this->handler->decrement(self::$key1, 1));
        $this->assertSame($this->handler->decrement(self::$key1, 1), $this->handler->get(self::$key1));
        $this->assertFalse($this->handler->decrement(self::$key2, 1));
        $this->assertSame(-1, $this->handler->decrement(self::$key3, 1));
        $this->assertSame($this->handler->decrement(self::$key3, 1), $this->handler->get(self::$key3));
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

    #[DataProvider('provideSaveMode')]
    #[RequiresOperatingSystem('Linux|Darwin')]
    public function testSaveMode(int $int, string $string): void
    {
        // Initialize mode
        $this->config->file['mode'] = $int;

        $this->handler = CacheFactory::getHandler($this->config, 'file');
        $this->handler->save(self::$key1, 'value');

        $file = $this->config->file['storePath'] . DIRECTORY_SEPARATOR . self::$key1;
        $mode = octal_permissions(fileperms($file));

        $this->assertSame($string, $mode);
    }

    /**
     * @return iterable<int, array{int, numeric-string}>
     */
    public static function provideSaveMode(): iterable
    {
        return [
            [0640, '640'],
            [0600, '600'],
            [0660, '660'],
            [0777, '777'],
        ];
    }

    public function testFileHandler(): void
    {
        $cache = new Cache();

        $cache->validHandlers['file'] = BaseTestFileHandler::class;

        /** @var BaseTestFileHandler $fileHandler */
        $fileHandler = CacheFactory::getHandler($cache, 'file');
        $this->assertInstanceOf(BaseTestFileHandler::class, $fileHandler);

        $actual = $fileHandler->getFileInfoTest();
        $this->assertIsArray($actual);
        $this->assertArrayHasKey('name', $actual);
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
        $this->assertNull($this->handler->getMetaData(self::$dummy));
    }

    #[RequiresOperatingSystem('Linux|Darwin')]
    public function testGetUnreadableFile(): void
    {
        $this->handler->save(self::$key1, 'value');

        $filePath = $this->config->file['storePath'] . DIRECTORY_SEPARATOR . $this->config->prefix . self::$key1;

        // Make the file unreadable
        chmod($filePath, 0000);

        $this->assertNull($this->handler->get(self::$key1));
    }

    public function testGetItemWithCorruptedData(): void
    {
        $filePath = $this->config->file['storePath'] . DIRECTORY_SEPARATOR . $this->config->prefix . self::$key1;

        file_put_contents($filePath, 'corrupted_serialized_data_that_cannot_be_unserialized');

        $this->assertFileExists($filePath);

        $this->assertNull($this->handler->get(self::$key1));
    }

    public function testPing(): void
    {
        $this->assertTrue($this->handler->ping());
    }

    public function testReconnect(): void
    {
        $this->assertTrue($this->handler->reconnect());
    }

    #[DataProvider('provideValidateKeyRejectsSeparatorsAndNullBytes')]
    public function testValidateKeyRejectsSeparatorsAndNullBytes(string $key, string $prefix): void
    {
        // Multi-store setups often empty this setting to share keys between
        // handlers, which must not weaken the file handler's own validation.
        config('Cache')->reservedCharacters = '';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache key cannot contain directory separators or null bytes.');

        FileHandler::validateKey($key, $prefix);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideValidateKeyRejectsSeparatorsAndNullBytes(): iterable
    {
        yield 'parent traversal' => ['../evil', ''];

        yield 'nested traversal' => ['sub/../../evil', ''];

        yield 'backslash traversal' => ['..\\evil', ''];

        yield 'forward slash separator' => ['sub/key', ''];

        yield 'backslash separator' => ['sub\\key', ''];

        yield 'forward slash only' => ['/', ''];

        yield 'backslash only' => ['\\', ''];

        yield 'null byte only' => ["\0", ''];

        yield 'null byte inside key' => ["sub\0key", ''];

        yield 'parent traversal in prefix' => ['key', '../'];

        yield 'backslash traversal in prefix' => ['key', '..\\'];

        yield 'null byte in prefix' => ['key', "a\0b"];

        yield 'forward slash in prefix' => ['key', 'pre/'];

        yield 'absolute path' => ['/etc/passwd', ''];

        yield 'trailing slash in key' => ['key/', ''];

        yield 'mixed separators' => ['a/b\\c', ''];

        yield 'backslash and zero at start' => ['\\0key', ''];

        yield 'backslash and zero at end' => ['key\\0', ''];

        yield 'single slash prefix' => ['key', '/'];

        yield 'backslash zero prefix' => ['key', '\\0'];

        // trim() strips the leading and trailing dots only, so the remaining
        // backslashes make this prefix pass the dot-only check.
        yield 'escaped traversal in prefix' => ['key', '..\\\\..\\\\'];
    }

    #[DataProvider('provideValidateKeyRejectsDotOnlyPrefix')]
    public function testValidateKeyRejectsDotOnlyPrefix(string $key, string $prefix): void
    {
        config('Cache')->reservedCharacters = '';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache key prefix cannot consist only of dots and spaces.');

        FileHandler::validateKey($key, $prefix);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideValidateKeyRejectsDotOnlyPrefix(): iterable
    {
        yield 'dot' => ['key', '.'];

        yield 'two dots' => ['key', '..'];

        yield 'three dots' => ['key', '...'];

        yield 'two dots and a space' => ['key', '.. '];

        yield 'spaces around a dot' => ['key', ' . '];

        yield 'space only' => ['key', ' '];

        yield 'spaces only' => ['key', '   '];

        yield 'prefix with dots and plain key' => ['item', '..'];
    }

    #[DataProvider('provideValidateKeyAcceptsHarmlessNames')]
    public function testValidateKeyAcceptsHarmlessNames(string $key, string $prefix, string $expected): void
    {
        config('Cache')->reservedCharacters = '';

        $this->assertSame($expected, FileHandler::validateKey($key, $prefix));
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function provideValidateKeyAcceptsHarmlessNames(): iterable
    {
        yield 'plain key' => ['valid_key', '', 'valid_key'];

        yield 'dots before extension' => ['foo..json', '', 'foo..json'];

        yield 'dots inside name' => ['user..settings', '', 'user..settings'];

        yield 'leading dots' => ['..item', '', '..item'];

        yield 'trailing dots' => ['item..', '', 'item..'];

        yield 'double leading dots' => ['..foo', '', '..foo'];

        yield 'three dots' => ['...', '', '...'];

        yield 'single dot' => ['.', '', '.'];

        yield 'two dots' => ['..', '', '..'];

        yield 'dots with trailing space' => ['.. ', '', '.. '];

        yield 'spaces and dots' => [' . ', '', ' . '];

        yield 'colon character' => ['test:stream', '', 'test:stream'];

        yield 'drive letter' => ['C:file', '', 'C:file'];

        yield 'dot but not dot-only prefix' => ['key', '.app', '.appkey'];

        yield 'colon in prefix' => ['key', 'app:', 'app:key'];

        yield 'underscore in prefix' => ['key', 'app_', 'app_key'];

        yield 'non-ASCII key' => ['zażółć_gęślą_jaźń', '', 'zażółć_gęślą_jaźń'];
    }

    public function testValidateKeyHashesLongKeyBeforeValidatingName(): void
    {
        config('Cache')->reservedCharacters = '';

        // A key of nothing but dots used to be rejected before hashing.
        $dots = str_repeat('.', 500);
        $this->assertSame(md5($dots), FileHandler::validateKey($dots));

        // The parent hashes the key before the file name is validated.
        $key = str_repeat('a', 300) . '/evil';
        $this->assertSame(md5($key), FileHandler::validateKey($key));

        $longKey = str_repeat('a', 300);
        $this->assertSame('pre_' . md5($longKey), FileHandler::validateKey($longKey, 'pre_'));
    }

    public function testValidateKeyKeepsMaxLengthBoundary(): void
    {
        $atLimit = str_repeat('a', 255);
        $this->assertSame($atLimit, FileHandler::validateKey($atLimit));

        // One byte over the limit is hashed, so the file name stays short.
        $overLimit = str_repeat('a', 256);
        $this->assertSame(md5($overLimit), FileHandler::validateKey($overLimit));

        // The prefix is counted in the limit but is never hashed.
        $prefixKey = str_repeat('a', 251);
        $this->assertSame('pre_' . $prefixKey, FileHandler::validateKey($prefixKey, 'pre_'));

        $prefixOver = str_repeat('a', 252);
        $this->assertSame('pre_' . md5($prefixOver), FileHandler::validateKey($prefixOver, 'pre_'));
    }

    public function testValidateKeyRejectsTraversalInPrefixOfHashedKey(): void
    {
        config('Cache')->reservedCharacters = '';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache key cannot contain directory separators or null bytes.');

        // The prefix is not hashed, so a separator in it must still be rejected.
        FileHandler::validateKey(str_repeat('a', 300), '../');
    }

    public function testValidateKeyRejectsDotOnlyPrefixOfHashedKey(): void
    {
        config('Cache')->reservedCharacters = '';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache key prefix cannot consist only of dots and spaces.');

        // Hashing the key must not bypass the prefix check.
        FileHandler::validateKey(str_repeat('a', 300), '.');
    }

    public function testValidateKeyRejectsEmptyKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache key cannot be empty.');

        FileHandler::validateKey('');
    }

    public function testValidateKeyRejectsNonStringKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache key must be a string');

        FileHandler::validateKey(42);
    }

    public function testValidateKeyRejectsReservedCharacters(): void
    {
        // Set the default explicitly: this test documents the default reserved
        // characters, so it must not depend on the configured app value.
        config('Cache')->reservedCharacters = '{}()/\@:';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache key contains reserved characters {}()/\@:');

        FileHandler::validateKey('sub/key');
    }

    #[DataProvider('provideValidateKeyRejectsEveryDefaultReservedCharacter')]
    public function testValidateKeyRejectsEveryDefaultReservedCharacter(string $char): void
    {
        config('Cache')->reservedCharacters = '{}()/\@:';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache key contains reserved characters {}()/\@:');

        FileHandler::validateKey('a' . $char . 'b');
    }

    /**
     * @return iterable<int, array{string}>
     */
    public static function provideValidateKeyRejectsEveryDefaultReservedCharacter(): iterable
    {
        return [
            ['{'],
            ['}'],
            ['('],
            [')'],
            ['/'],
            ['\\'],
            ['@'],
            [':'],
        ];
    }

    public function testValidateKeyAcceptsValidKeyWithDefaultConfig(): void
    {
        $this->assertSame('valid_key', FileHandler::validateKey('valid_key'));
    }

    public function testValidateKeyAcceptsFalsyStringKey(): void
    {
        // "0" is falsy but a perfectly valid cache key.
        $this->assertSame('0', FileHandler::validateKey('0'));
    }

    public function testValidateKeySuccessWithPrefix(): void
    {
        $this->assertSame('prefixvalid_key', FileHandler::validateKey('valid_key', 'prefix'));
    }

    public function testDottedKeySurvivesFullLifecycle(): void
    {
        $this->assertTrue($this->handler->save('..item', 'value'));
        $this->assertSame('value', $this->handler->get('..item'));
        $this->assertTrue($this->handler->delete('..item'));
    }

    public function testDottedDirectoryKeyIsNotWritten(): void
    {
        // No regression from before the pull request: the path resolves to a
        // directory, so is_file() and fopen() keep the item from being stored.
        $this->assertFalse($this->handler->save('..', 'value'));
        $this->assertNull($this->handler->get('..'));
        $this->assertFalse($this->handler->delete('..'));
    }

    public function testIncrementRejectsTraversalKey(): void
    {
        config('Cache')->reservedCharacters = '';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache key cannot contain directory separators or null bytes.');

        $this->handler->increment('../counter');
    }

    public function testPublicApiRejectsSeparatorWithDefaultConfig(): void
    {
        config('Cache')->reservedCharacters = '{}()/\@:';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache key contains reserved characters {}()/\@:');

        $this->handler->save('../evil', 'value');
    }

    public function testDotOnlyPrefixFromConfigIsRejectedByPublicApi(): void
    {
        $config                    = new Cache();
        $config->prefix            = '..';
        $config->file['storePath'] = $this->config->file['storePath'];

        $handler = CacheFactory::getHandler($config, 'file');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache key prefix cannot consist only of dots and spaces.');

        $handler->save('key', 'value');
    }

    /**
     * A relaxed reserved character list must not turn the public API into a way
     * to read or write files outside the cache directory.
     */
    public function testTraversalThroughPublicApiDoesNotLeaveCacheDirectory(): void
    {
        // The weakened configuration is the very scenario the separators check
        // has to cover, so it must not be rejected by the reserved list instead.
        config('Cache')->reservedCharacters = '';

        $victim = dirname($this->config->file['storePath']) . DIRECTORY_SEPARATOR . 'victim_' . uniqid('', true);
        $this->assertNotFalse(file_put_contents($victim, 'original'));

        try {
            foreach (['../' . basename($victim), '..\\' . basename($victim)] as $key) {
                try {
                    $this->handler->save($key, 'overwritten');
                    $this->fail('save() accepted the traversal key "' . $key . '".');
                } catch (InvalidArgumentException $e) {
                    $this->assertSame('Cache key cannot contain directory separators or null bytes.', $e->getMessage());
                }

                try {
                    $this->handler->delete($key);
                    $this->fail('delete() accepted the traversal key "' . $key . '".');
                } catch (InvalidArgumentException $e) {
                    $this->assertSame('Cache key cannot contain directory separators or null bytes.', $e->getMessage());
                }

                try {
                    $this->handler->getMetaData($key);
                    $this->fail('getMetaData() accepted the traversal key "' . $key . '".');
                } catch (InvalidArgumentException $e) {
                    $this->assertSame('Cache key cannot contain directory separators or null bytes.', $e->getMessage());
                }
            }

            $this->assertSame('original', file_get_contents($victim));
        } finally {
            @unlink($victim);
        }
    }

    #[RequiresOperatingSystem('Linux|Darwin')]
    public function testWeakenedReservedCharactersAllowsColonInFileName(): void
    {
        // Rejecting separators must not reject characters that are valid in
        // file names, otherwise relaxing the reserved list would do nothing.
        config('Cache')->reservedCharacters = '';

        $this->assertTrue($this->handler->save('test:stream', 'value'));
        $this->assertSame('value', $this->handler->get('test:stream'));
        $this->assertTrue($this->handler->delete('test:stream'));
    }
}
