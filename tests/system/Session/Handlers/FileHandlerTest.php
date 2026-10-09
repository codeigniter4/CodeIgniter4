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

namespace CodeIgniter\Session\Handlers;

use Closure;
use CodeIgniter\Session\Exceptions\SessionException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\TestLogger;
use Config\Logger as LoggerConfig;
use Config\Session as SessionConfig;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresOperatingSystem;

/**
 * @internal
 */
#[Group('Others')]
final class FileHandlerTest extends CIUnitTestCase
{
    private string $sessionDriver = FileHandler::class;
    private string $sessionName   = 'ci_session';
    private string $userIpAddress = '127.0.0.1';
    private string $tempPath;
    private string $originalSavePath;
    private string $originalSidBits;
    private string $originalSidLength;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalSavePath  = (string) ini_get('session.save_path');
        $this->originalSidBits   = (string) ini_get('session.sid_bits_per_character');
        $this->originalSidLength = (string) ini_get('session.sid_length');

        $this->tempPath = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'FileHandlerTest_' . bin2hex(random_bytes(6));
        mkdir($this->tempPath, 0700, true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        ini_set('session.save_path', $this->originalSavePath);
        ini_set('session.sid_bits_per_character', $this->originalSidBits);
        ini_set('session.sid_length', $this->originalSidLength);

        $this->removeDirectory($this->tempPath);
    }

    /**
     * @param array<string, bool|int|string|null> $options Replace values for `Config\Session`.
     */
    protected function getInstance(array $options = []): FileHandler
    {
        $defaults = [
            'driver'            => $this->sessionDriver,
            'cookieName'        => $this->sessionName,
            'expiration'        => 7200,
            'savePath'          => $this->tempPath,
            'matchIP'           => false,
            'timeToUpdate'      => 300,
            'regenerateDestroy' => false,
        ];
        $sessionConfig = new SessionConfig();
        $config        = array_merge($defaults, $options);

        foreach ($config as $key => $value) {
            $sessionConfig->{$key} = $value;
        }

        $handler = new FileHandler($sessionConfig, $this->userIpAddress);
        $handler->setLogger(new TestLogger(new LoggerConfig()));

        return $handler;
    }

    /**
     * Runs a callback while suppressing any PHP warnings it raises.
     *
     * @param Closure(): mixed $callback
     */
    private function withSuppressedErrors(Closure $callback): mixed
    {
        set_error_handler(static fn (int $severity, string $message): bool => true);

        try {
            return $callback();
        } finally {
            restore_error_handler();
        }
    }

    private function removeDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        $items = scandir($path);

        foreach ($items === false ? [] : $items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $target = $path . DIRECTORY_SEPARATOR . $item;

            if (is_dir($target)) {
                $this->removeDirectory($target);
            } else {
                @unlink($target);
            }
        }

        @chmod($path, 0700);
        @rmdir($path);
    }

    private static function sessionId(string $char): string
    {
        return str_repeat($char, 32);
    }

    // --------------------------------------------------------------------
    // Constructor & Initialization
    // --------------------------------------------------------------------

    public function testConstructorTrimsAndSetsIniSavePath(): void
    {
        $this->getInstance(['savePath' => $this->tempPath . '//']);

        $this->assertSame($this->tempPath, ini_get('session.save_path'));
    }

    public function testConstructorForcesSessionIDRegexSettings(): void
    {
        ini_set('session.sid_bits_per_character', '5');
        ini_set('session.sid_length', '26');

        $this->getInstance();

        $this->assertSame('4', ini_get('session.sid_bits_per_character'));
        $this->assertSame('32', ini_get('session.sid_length'));
    }

    // --------------------------------------------------------------------
    // open()
    // --------------------------------------------------------------------

    public function testOpenExistingDirectory(): void
    {
        $handler = $this->getInstance();

        $this->assertTrue($handler->open($this->tempPath, $this->sessionName));
    }

    public function testOpenWithMatchIPIsolatesSessions(): void
    {
        $id   = self::sessionId('1');
        $data = 'ip_test_data';

        $handler1 = $this->getInstance(['matchIP' => true]);
        $this->assertTrue($handler1->open($this->tempPath, $this->sessionName));
        $this->assertSame('', $handler1->read($id));
        $this->assertTrue($handler1->write($id, $data));
        $this->assertTrue($handler1->close());

        // File created with hashed IP in filename
        $this->assertFileExists($this->tempPath . '/' . $this->sessionName . md5($this->userIpAddress) . $id);

        // Handler with different IP cannot read the session (isolation)
        $sessionConfig             = new SessionConfig();
        $sessionConfig->driver     = $this->sessionDriver;
        $sessionConfig->cookieName = $this->sessionName;
        $sessionConfig->savePath   = $this->tempPath;
        $sessionConfig->matchIP    = true;

        $handler2 = new FileHandler($sessionConfig, '192.168.1.1');
        $handler2->setLogger(new TestLogger(new LoggerConfig()));
        $this->assertTrue($handler2->open($this->tempPath, $this->sessionName));
        $this->assertSame('', $handler2->read($id));
        $this->assertTrue($handler2->close());
    }

    public function testOpenCreatesMissingDirectory(): void
    {
        $handler = $this->getInstance();

        $path = $this->tempPath . '/new/nested';

        $this->assertTrue($handler->open($path, $this->sessionName));
        $this->assertDirectoryExists($path);
    }

    public function testOpenThrowsForInvalidSavePath(): void
    {
        file_put_contents($this->tempPath . '/blocker', 'data');

        $handler = $this->getInstance();

        $this->expectException(SessionException::class);
        $this->withSuppressedErrors(fn (): bool => $handler->open($this->tempPath . '/blocker/sub', $this->sessionName));
    }

    #[RequiresOperatingSystem('Linux|Darwin')]
    public function testOpenThrowsForWriteProtectedSavePath(): void
    {
        $dir = $this->tempPath . '/readonly';
        mkdir($dir, 0700);
        chmod($dir, 0555);

        if (is_writable($dir)) {
            $this->markTestSkipped('Directory permissions cannot be restricted for this user (e.g. running as root).');
        }

        try {
            $handler = $this->getInstance();

            $this->expectException(SessionException::class);
            $handler->open($dir, $this->sessionName);
        } finally {
            chmod($dir, 0755);
        }
    }

    // --------------------------------------------------------------------
    // read()
    // --------------------------------------------------------------------

    public function testReadNewSession(): void
    {
        $handler = $this->getInstance();
        $handler->open($this->tempPath, $this->sessionName);

        $id = self::sessionId('1');

        $this->assertSame('', $handler->read($id));

        $handler->close();
    }

    public function testReadExistingSession(): void
    {
        $id   = self::sessionId('2');
        $data = '__ci_last_regenerate|i:1664607454;key|s:5:"value";';
        file_put_contents($this->tempPath . '/' . $this->sessionName . $id, $data);

        $handler = $this->getInstance();
        $handler->open($this->tempPath, $this->sessionName);

        $this->assertSame($data, $handler->read($id));

        $handler->close();
    }

    public function testReadTwiceWithoutCloseRewinds(): void
    {
        $id   = self::sessionId('3');
        $data = '__ci_last_regenerate|i:1664607454;key|s:5:"value";';

        $handler = $this->getInstance();
        $handler->open($this->tempPath, $this->sessionName);

        $this->assertSame('', $handler->read($id));
        $this->assertTrue($handler->write($id, $data));

        $this->assertSame($data, $handler->read($id));

        $handler->close();
    }

    public function testReadFailsWhenFileCannotBeOpened(): void
    {
        $handler = $this->getInstance();
        $handler->open($this->tempPath, $this->sessionName);

        $id = self::sessionId('4');
        mkdir($this->tempPath . '/' . $this->sessionName . $id, 0700, true);

        $result = $this->withSuppressedErrors(static fn (): false|string => $handler->read($id));

        $this->assertFalse($result);
        $this->assertLogContains('error', 'Session: Unable to open file');
    }

    // --------------------------------------------------------------------
    // write()
    // --------------------------------------------------------------------

    public function testWriteFailsWhenNoFileHandle(): void
    {
        $handler = $this->getInstance();

        $this->assertFalse($handler->write(self::sessionId('a'), 'data'));
    }

    public function testReadWriteRoundtrip(): void
    {
        $id   = self::sessionId('b');
        $data = '__ci_last_regenerate|i:1664607454;key|s:5:"value";';

        $handler = $this->getInstance();
        $handler->open($this->tempPath, $this->sessionName);

        $this->assertSame('', $handler->read($id));
        $this->assertTrue($handler->write($id, $data));
        // Identical data on a new file returns true without writing again.
        $this->assertTrue($handler->write($id, $data));

        $handler->close();

        $this->assertSame($data, file_get_contents($this->tempPath . '/' . $this->sessionName . $id));

        // A second handler reads the file back; an identical write just touches it.
        $handler2 = $this->getInstance();
        $handler2->open($this->tempPath, $this->sessionName);

        $this->assertSame($data, $handler2->read($id));
        $this->assertTrue($handler2->write($id, $data));

        // Different data truncates and rewrites the existing file.
        $data2 = 'changed|i:1664607455;key|s:6:"value2";';
        $this->assertTrue($handler2->write($id, $data2));

        $handler2->close();

        $this->assertSame($data2, file_get_contents($this->tempPath . '/' . $this->sessionName . $id));
    }

    public function testWriteEmptyDataClearsSession(): void
    {
        $id   = self::sessionId('c');
        $data = 'some_data';

        $handler1 = $this->getInstance();
        $handler1->open($this->tempPath, $this->sessionName);
        $this->assertSame('', $handler1->read($id));
        $this->assertTrue($handler1->write($id, $data));
        $handler1->close();

        $this->assertSame($data, file_get_contents($this->tempPath . '/' . $this->sessionName . $id));

        $handler2 = $this->getInstance();
        $handler2->open($this->tempPath, $this->sessionName);
        $this->assertSame($data, $handler2->read($id));
        $this->assertTrue($handler2->write($id, ''));
        $handler2->close();

        $this->assertSame('', file_get_contents($this->tempPath . '/' . $this->sessionName . $id));
    }

    public function testWriteWithDifferentSessionID(): void
    {
        $handler = $this->getInstance();
        $handler->open($this->tempPath, $this->sessionName);

        $id1 = self::sessionId('d');
        $id2 = self::sessionId('e');

        $this->assertSame('', $handler->read($id1));
        $this->assertTrue($handler->write($id2, 'data'));
        $handler->close();
    }

    // --------------------------------------------------------------------
    // close()
    // --------------------------------------------------------------------

    public function testCloseWithOpenFile(): void
    {
        $handler = $this->getInstance();
        $handler->open($this->tempPath, $this->sessionName);
        $handler->read(self::sessionId('1'));

        $this->assertTrue($handler->close());
        // After closing, write fails
        $this->assertFalse($handler->write(self::sessionId('1'), 'data'));
    }

    public function testCloseWithoutOpenFile(): void
    {
        $handler = $this->getInstance();

        $this->assertTrue($handler->close());
    }

    // --------------------------------------------------------------------
    // destroy()
    // --------------------------------------------------------------------

    public function testDestroyExistingSession(): void
    {
        $handler = $this->getInstance();
        $handler->open($this->tempPath, $this->sessionName);

        $id = self::sessionId('2');
        $handler->read($id);
        $handler->close();

        $this->assertFileExists($this->tempPath . '/' . $this->sessionName . $id);
        $this->assertTrue($handler->destroy($id));
        $this->assertFileDoesNotExist($this->tempPath . '/' . $this->sessionName . $id);
    }

    public function testDestroyNonexistentSession(): void
    {
        $handler = $this->getInstance();
        $handler->open($this->tempPath, $this->sessionName);
        $handler->close();

        $this->assertTrue($handler->destroy(self::sessionId('3')));
    }

    // --------------------------------------------------------------------
    // gc()
    // --------------------------------------------------------------------

    public function testGCWhenSavePathMissing(): void
    {
        $handler = $this->getInstance(['savePath' => $this->tempPath . '/missing']);

        $this->assertFalse($handler->gc(3600));
        $this->assertLogContains('debug', "Session: Garbage collector couldn't list files under directory");
    }

    public function testGCRemovesExpiredSessionsOnly(): void
    {
        $handler = $this->getInstance(['savePath' => $this->tempPath]);

        $expired = $this->tempPath . '/' . $this->sessionName . self::sessionId('a');
        file_put_contents($expired, 'data');
        touch($expired, time() - 4000);

        $recent = $this->tempPath . '/' . $this->sessionName . self::sessionId('b');
        file_put_contents($recent, 'data');
        touch($recent);

        $directory = $this->tempPath . '/' . $this->sessionName . self::sessionId('c');
        mkdir($directory, 0700);

        $foreign = $this->tempPath . '/not-a-session-file';
        file_put_contents($foreign, 'data');
        touch($foreign, time() - 4000);

        $nonHex = $this->tempPath . '/' . $this->sessionName . str_repeat('z', 32);
        file_put_contents($nonHex, 'data');
        touch($nonHex, time() - 4000);

        $this->assertSame(1, $handler->gc(3600));

        $this->assertFileDoesNotExist($expired);
        $this->assertFileExists($recent);
        $this->assertDirectoryExists($directory);
        $this->assertFileExists($foreign);
        $this->assertFileExists($nonHex);
    }

    public function testGCWithMatchIP(): void
    {
        $handler = $this->getInstance(['savePath' => $this->tempPath, 'matchIP' => true]);

        $expired = $this->tempPath . '/' . $this->sessionName . md5($this->userIpAddress) . self::sessionId('a');
        file_put_contents($expired, 'data');
        touch($expired, time() - 4000);

        $withoutIp = $this->tempPath . '/' . $this->sessionName . self::sessionId('b');
        file_put_contents($withoutIp, 'data');
        touch($withoutIp, time() - 4000);

        $this->assertSame(1, $handler->gc(3600));

        $this->assertFileDoesNotExist($expired);
        $this->assertFileExists($withoutIp);
    }
}
