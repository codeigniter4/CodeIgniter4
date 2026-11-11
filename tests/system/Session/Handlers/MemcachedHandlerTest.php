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

use CodeIgniter\Session\Exceptions\SessionException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\TestLogger;
use Config\Logger as LoggerConfig;
use Config\Session as SessionConfig;
use Memcached;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

/**
 * @internal
 */
#[Group('DatabaseLive')]
#[RequiresPhpExtension('memcached')]
final class MemcachedHandlerTest extends CIUnitTestCase
{
    private string $sessionDriver   = MemcachedHandler::class;
    private string $sessionName     = 'ci_session';
    private string $sessionSavePath = '127.0.0.1:11211';
    private string $userIpAddress   = '127.0.0.1';

    /**
     * @var list<string>
     */
    private array $sessionIds = [];

    /**
     * @param array<string, bool|int|string|null> $options Replace values for `Config\Session`.
     */
    protected function getInstance($options = []): MemcachedHandler
    {
        $defaults = [
            'driver'            => $this->sessionDriver,
            'cookieName'        => $this->sessionName,
            'expiration'        => 7200,
            'savePath'          => $this->sessionSavePath,
            'matchIP'           => false,
            'timeToUpdate'      => 300,
            'regenerateDestroy' => false,
        ];
        $sessionConfig = new SessionConfig();
        $config        = array_merge($defaults, $options);

        foreach ($config as $key => $value) {
            $sessionConfig->{$key} = $value;
        }

        $handler = new MemcachedHandler($sessionConfig, $this->userIpAddress);
        $handler->setLogger(new TestLogger(new LoggerConfig()));

        return $handler;
    }

    protected function tearDown(): void
    {
        if ($this->sessionIds !== []) {
            $memcached = new Memcached();
            $memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
            $memcached->addServer('127.0.0.1', 11211);

            $prefix = 'ci_session:' . $this->sessionName . ':';

            foreach ($this->sessionIds as $sessionId) {
                $memcached->delete($prefix . $sessionId);
                $memcached->delete($prefix . $sessionId . ':lock');
            }

            $this->sessionIds = [];
        }

        parent::tearDown();

        MemcachedHandler::resetPersistentConnections();
    }

    private function generateSessionId(string $prefix): string
    {
        $sessionId          = $prefix . bin2hex(random_bytes(13));
        $this->sessionIds[] = $sessionId;

        return $sessionId;
    }

    public function testConstructorThrowsWithEmptySavePath(): void
    {
        $this->expectException(SessionException::class);

        $this->getInstance(['savePath' => '']);
    }

    public function testConstructorDoesNotThrowWithValidSavePath(): void
    {
        $handler = $this->getInstance(['savePath' => '127.0.0.1:11211']);

        $this->assertInstanceOf(MemcachedHandler::class, $handler);
    }

    public function testOpen(): void
    {
        $handler = $this->getInstance();
        $this->assertTrue($handler->open($this->sessionSavePath, $this->sessionName));
    }

    public function testWriteAndReadBack(): void
    {
        $handler = $this->getInstance();
        $handler->open($this->sessionSavePath, $this->sessionName);

        $sessionId = '555556b43phsnnf8if6bo33b635e4447';

        // Initial read to acquire lock and set session ID
        $this->assertSame('', $handler->read($sessionId));

        $data = <<<'DATA'
            __ci_last_regenerate|i:1664607454;_ci_previous_url|s:32:"http://localhost:8080/index.php/";key|s:5:"value";
            DATA;
        $this->assertTrue($handler->write($sessionId, $data));

        $handler->close();

        // Read back in a new handler to verify persistence
        $handler2 = $this->getInstance();
        $handler2->open($this->sessionSavePath, $this->sessionName);

        $this->assertSame($data, $handler2->read($sessionId));

        $handler2->close();
    }

    public function testReadEmptySession(): void
    {
        $handler = $this->getInstance();
        $handler->open($this->sessionSavePath, $this->sessionName);

        $this->assertSame('', $handler->read('123456b43phsnnf8if6bo33b635e4321'));

        $handler->close();
    }

    public function testGC(): void
    {
        $handler = $this->getInstance();
        $this->assertSame(1, $handler->gc(3600));
    }

    public function testReopenAndReadWithSameHandlerInstance(): void
    {
        $handler = $this->getInstance();
        $handler->open($this->sessionSavePath, $this->sessionName);

        $sessionId = $this->generateSessionId('666666');

        $this->assertSame('', $handler->read($sessionId));

        $data = '__ci_last_regenerate|i:1664607454;key|s:5:"value";';
        $this->assertTrue($handler->write($sessionId, $data));
        $this->assertTrue($handler->close());

        // Reopen with the same handler instance (e.g. session_write_close() + session_start())
        $this->assertTrue($handler->open($this->sessionSavePath, $this->sessionName));
        $this->assertSame($data, $handler->read($sessionId));
        $this->assertTrue($handler->close());
    }

    public function testRegenerateSessionIdWithSameHandlerInstance(): void
    {
        $handler = $this->getInstance();
        $handler->open($this->sessionSavePath, $this->sessionName);

        $oldSessionId = $this->generateSessionId('777777');
        $newSessionId = $this->generateSessionId('888888');

        $this->assertSame('', $handler->read($oldSessionId));

        $data = '__ci_last_regenerate|i:1664607454;role|s:5:"admin";';
        $this->assertTrue($handler->write($oldSessionId, $data));
        $this->assertTrue($handler->close());

        // PHP reuses the same SessionHandler object on regeneration
        $this->assertTrue($handler->open($this->sessionSavePath, $this->sessionName));
        $this->assertSame('', $handler->read($newSessionId));
        $this->assertTrue($handler->write($newSessionId, $data));
        $this->assertTrue($handler->close());

        $this->assertTrue($handler->open($this->sessionSavePath, $this->sessionName));
        $this->assertSame($data, $handler->read($newSessionId));
        $this->assertTrue($handler->close());
    }

    public function testRegenerateSessionIdWriteWithoutClose(): void
    {
        $handler = $this->getInstance();
        $handler->open($this->sessionSavePath, $this->sessionName);

        $oldSessionId = $this->generateSessionId('999999');
        $newSessionId = $this->generateSessionId('aaaaaa');

        $this->assertSame('', $handler->read($oldSessionId));

        $oldData = '__ci_last_regenerate|i:1664607454;role|s:5:"guest";';
        $this->assertTrue($handler->write($oldSessionId, $oldData));

        // During PHP session_regenerate_id(), write() is called with the new ID
        // while the lock for the old session ID is still held.
        $newData = '__ci_last_regenerate|i:1664607500;role|s:5:"admin";';
        $this->assertTrue($handler->write($newSessionId, $newData));
        $this->assertTrue($handler->close());

        // Verify the old lock was released and the new session data is readable
        $handler2 = $this->getInstance();
        $this->assertTrue($handler2->open($this->sessionSavePath, $this->sessionName));
        $this->assertSame($oldData, $handler2->read($oldSessionId));
        $this->assertTrue($handler2->close());

        $this->assertTrue($handler2->open($this->sessionSavePath, $this->sessionName));
        $this->assertSame($newData, $handler2->read($newSessionId));
        $this->assertTrue($handler2->close());
    }
}
