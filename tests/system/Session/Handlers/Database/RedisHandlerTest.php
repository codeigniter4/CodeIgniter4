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

namespace CodeIgniter\Session\Handlers\Database;

use CodeIgniter\Session\Handlers\RedisHandler;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Session as SessionConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Redis;

/**
 * @internal
 */
#[Group('DatabaseLive')]
#[RequiresPhpExtension('redis')]
final class RedisHandlerTest extends CIUnitTestCase
{
    private string $sessionDriver   = RedisHandler::class;
    private string $sessionName     = 'ci_session';
    private string $sessionSavePath = 'tcp://127.0.0.1:6379';
    private string $userIpAddress   = '127.0.0.1';

    /**
     * @param array<string, bool|int|string|null> $options Replace values for `Config\Session`.
     */
    protected function getInstance($options = []): RedisHandler
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

        return new RedisHandler($sessionConfig, $this->userIpAddress);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        RedisHandler::resetPersistentConnections();
    }

    public function testOpen(): void
    {
        $handler = $this->getInstance();
        $this->assertTrue($handler->open($this->sessionSavePath, $this->sessionName));
    }

    public function testOpenWithDefaultProtocol(): void
    {
        $default = $this->sessionSavePath;

        $this->sessionSavePath = '127.0.0.1:6379';

        $handler = $this->getInstance();
        $this->assertTrue($handler->open($this->sessionSavePath, $this->sessionName));

        // Rollback to default
        $this->sessionSavePath = $default;
    }

    public function testWrite(): void
    {
        $handler = $this->getInstance();
        $handler->open($this->sessionSavePath, $this->sessionName);
        $handler->read('555556b43phsnnf8if6bo33b635e4447');

        $data = <<<'DATA'
            __ci_last_regenerate|i:1664607454;_ci_previous_url|s:32:"http://localhost:8080/index.php/";key|s:5:"value";
            DATA;
        $this->assertTrue($handler->write('555556b43phsnnf8if6bo33b635e4447', $data));

        $handler->close();
    }

    public function testReadSuccess(): void
    {
        $handler = $this->getInstance();
        $handler->open($this->sessionSavePath, $this->sessionName);

        $expected = <<<'DATA'
            __ci_last_regenerate|i:1664607454;_ci_previous_url|s:32:"http://localhost:8080/index.php/";key|s:5:"value";
            DATA;
        $this->assertSame($expected, $handler->read('555556b43phsnnf8if6bo33b635e4447'));

        $handler->close();
    }

    public function testReadFailure(): void
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

    /**
     * See https://github.com/codeigniter4/CodeIgniter4/issues/7695
     */
    public function testSecondaryReadAfterClose(): void
    {
        $handler = $this->getInstance();
        $handler->open($this->sessionSavePath, $this->sessionName);

        $expected = <<<'DATA'
            __ci_last_regenerate|i:1664607454;_ci_previous_url|s:32:"http://localhost:8080/index.php/";key|s:5:"value";
            DATA;
        $this->assertSame($expected, $handler->read('555556b43phsnnf8if6bo33b635e4447'));

        $handler->close();

        $handler->open($this->sessionSavePath, $this->sessionName);

        $this->assertSame($expected, $handler->read('555556b43phsnnf8if6bo33b635e4447'));

        $handler->close();
    }

    /**
     * @param array<string, float|int|string|null> $expected
     */
    #[DataProvider('provideSetSavePath')]
    public function testSetSavePath(string $savePath, array $expected): void
    {
        $option  = ['savePath' => $savePath];
        $handler = $this->getInstance($option);

        $savePath = $this->getPrivateProperty($handler, 'savePath');

        $this->assertSame($expected, $savePath);
    }

    /**
     * @return iterable<string, list<array<string, array<string, string>|float|int|string|null>|string>> $expected
     */
    public static function provideSetSavePath(): iterable
    {
        yield from [
            'w/o protocol' => [
                '127.0.0.1:6379',
                [
                    'host'       => 'tcp://127.0.0.1',
                    'port'       => 6379,
                    'password'   => null,
                    'database'   => 0,
                    'timeout'    => 0.0,
                    'persistent' => null,
                ],
            ],
            'tls auth' => [
                'tls://127.0.0.1:6379?auth=password',
                [
                    'host'       => 'tls://127.0.0.1',
                    'port'       => 6379,
                    'password'   => 'password',
                    'database'   => 0,
                    'timeout'    => 0.0,
                    'persistent' => null,
                ],
            ],
            'tcp auth' => [
                'tcp://127.0.0.1:6379?auth=password',
                [
                    'host'       => 'tcp://127.0.0.1',
                    'port'       => 6379,
                    'password'   => 'password',
                    'database'   => 0,
                    'timeout'    => 0.0,
                    'persistent' => null,
                ],
            ],
            'timeout float' => [
                'tcp://127.0.0.1:6379?timeout=2.5',
                [
                    'host'       => 'tcp://127.0.0.1',
                    'port'       => 6379,
                    'password'   => null,
                    'database'   => 0,
                    'timeout'    => 2.5,
                    'persistent' => null,
                ],
            ],
            'timeout int' => [
                'tcp://127.0.0.1:6379?timeout=10',
                [
                    'host'       => 'tcp://127.0.0.1',
                    'port'       => 6379,
                    'password'   => null,
                    'database'   => 0,
                    'timeout'    => 10.0,
                    'persistent' => null,
                ],
            ],
            'auth acl' => [
                'tcp://localhost:6379?auth[user]=redis-admin&auth[pass]=admin-password',
                [
                    'host'       => 'tcp://localhost',
                    'port'       => 6379,
                    'password'   => ['user' => 'redis-admin', 'pass' => 'admin-password'],
                    'database'   => 0,
                    'timeout'    => 0.0,
                    'persistent' => null,
                ],
            ],
            'unix domain socket' => [
                'unix:///tmp/redis.sock',
                [
                    'host'       => '/tmp/redis.sock',
                    'port'       => 0,
                    'password'   => null,
                    'database'   => 0,
                    'timeout'    => 0.0,
                    'persistent' => null,
                ],
            ],
            'unix domain socket w/o protocol' => [
                '/tmp/redis.sock',
                [
                    'host'       => '/tmp/redis.sock',
                    'port'       => 0,
                    'password'   => null,
                    'database'   => 0,
                    'timeout'    => 0.0,
                    'persistent' => null,
                ],
            ],
            'persistent connection with numeric one' => [
                'tcp://127.0.0.1:6379?timeout=10&persistent=1',
                [
                    'host'       => 'tcp://127.0.0.1',
                    'port'       => 6379,
                    'password'   => null,
                    'database'   => 0,
                    'timeout'    => 10.0,
                    'persistent' => true,
                ],
            ],
            'no persistent connection with numeric zero' => [
                'tcp://127.0.0.1:6379?timeout=10&persistent=0',
                [
                    'host'       => 'tcp://127.0.0.1',
                    'port'       => 6379,
                    'password'   => null,
                    'database'   => 0,
                    'timeout'    => 10.0,
                    'persistent' => false,
                ],
            ],
            'persistent connection with boolean true' => [
                'tcp://127.0.0.1:6379?timeout=10&persistent=true',
                [
                    'host'       => 'tcp://127.0.0.1',
                    'port'       => 6379,
                    'password'   => null,
                    'database'   => 0,
                    'timeout'    => 10.0,
                    'persistent' => true,
                ],
            ],
            'persistent connection with boolean false' => [
                'tcp://127.0.0.1:6379?timeout=10&persistent=false',
                [
                    'host'       => 'tcp://127.0.0.1',
                    'port'       => 6379,
                    'password'   => null,
                    'database'   => 0,
                    'timeout'    => 10.0,
                    'persistent' => false,
                ],
            ],
        ];
    }

    public function testConnectionReuse(): void
    {
        $handler1 = $this->getInstance();
        $handler1->open($this->sessionSavePath, $this->sessionName);

        $connection1 = $this->getPrivateProperty($handler1, 'redis');
        $this->assertInstanceOf(Redis::class, $connection1);

        $handler2 = $this->getInstance();
        $handler2->open($this->sessionSavePath, $this->sessionName);

        $connection2 = $this->getPrivateProperty($handler2, 'redis');

        $this->assertSame($connection1, $connection2);

        $handler1->close();
        $handler2->close();
    }

    public function testDifferentConfigurationsGetDifferentConnections(): void
    {
        $handler1 = $this->getInstance();
        $handler1->open($this->sessionSavePath, $this->sessionName);

        $connection1 = $this->getPrivateProperty($handler1, 'redis');

        $handler2 = $this->getInstance(['cookieName' => 'different_session']);
        $handler2->open($this->sessionSavePath, 'different_session');

        $connection2 = $this->getPrivateProperty($handler2, 'redis');

        $this->assertNotSame($connection1, $connection2);

        $handler1->close();
        $handler2->close();
    }

    public function testResetPersistentConnections(): void
    {
        $handler1 = $this->getInstance();
        $handler1->open($this->sessionSavePath, $this->sessionName);

        $connection1 = $this->getPrivateProperty($handler1, 'redis');

        RedisHandler::resetPersistentConnections();

        $handler2 = $this->getInstance();
        $handler2->open($this->sessionSavePath, $this->sessionName);

        $connection2 = $this->getPrivateProperty($handler2, 'redis');

        $this->assertNotSame($connection1, $connection2);

        $handler1->close();
        $handler2->close();
    }
}
