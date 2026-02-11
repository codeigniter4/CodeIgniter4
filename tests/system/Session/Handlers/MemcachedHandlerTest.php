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
        parent::tearDown();

        MemcachedHandler::resetPersistentConnections();
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
}
