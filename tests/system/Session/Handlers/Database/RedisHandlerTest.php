<?php

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
use Config\App as AppConfig;
use Redis;

/**
 * @requires extension redis
 *
 * @internal
 */
final class RedisHandlerTest extends CIUnitTestCase
{
    private string $sessionName     = 'ci_session';
    private string $sessionSavePath = 'tcp://127.0.0.1:6379';
    private string $userIpAddress   = '127.0.0.1';

    private function getInstance($options = [])
    {
        $defaults = [
            'sessionDriver'            => RedisHandler::class,
            'sessionCookieName'        => $this->sessionName,
            'sessionExpiration'        => 7200,
            'sessionSavePath'          => $this->sessionSavePath,
            'sessionMatchIP'           => false,
            'sessionTimeToUpdate'      => 300,
            'sessionRegenerateDestroy' => false,
            'cookieDomain'             => '',
            'cookiePrefix'             => '',
            'cookiePath'               => '/',
            'cookieSecure'             => false,
            'cookieSameSite'           => 'Lax',
        ];

        $config    = array_merge($defaults, $options);
        $appConfig = new AppConfig();

        foreach ($config as $key => $c) {
            $appConfig->{$key} = $c;
        }

        return new RedisHandler($appConfig, $this->userIpAddress);
    }

    public function testSavePathTimeoutFloat()
    {
        $handler = $this->getInstance(
            ['sessionSavePath' => 'tcp://127.0.0.1:6379?timeout=2.5']
        );

        $savePath = $this->getPrivateProperty($handler, 'savePath');

        $this->assertSame(2.5, $savePath['timeout']);
    }

    public function testSavePathTimeoutInt()
    {
        $handler = $this->getInstance(
            ['sessionSavePath' => 'tcp://127.0.0.1:6379?timeout=10']
        );

        $savePath = $this->getPrivateProperty($handler, 'savePath');

        $this->assertSame(10.0, $savePath['timeout']);
    }

    public function testOpen()
    {
        $handler = $this->getInstance();
        $this->assertTrue($handler->open($this->sessionSavePath, $this->sessionName));
    }

    public function testWrite()
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

    public function testReadSuccess()
    {
        $handler = $this->getInstance();
        $handler->open($this->sessionSavePath, $this->sessionName);

        $expected = <<<'DATA'
            __ci_last_regenerate|i:1664607454;_ci_previous_url|s:32:"http://localhost:8080/index.php/";key|s:5:"value";
            DATA;
        $this->assertSame($expected, $handler->read('555556b43phsnnf8if6bo33b635e4447'));

        $handler->close();
    }

    public function testReadFailure()
    {
        $handler = $this->getInstance();
        $handler->open($this->sessionSavePath, $this->sessionName);

        $this->assertSame('', $handler->read('123456b43phsnnf8if6bo33b635e4321'));

        $handler->close();
    }

    public function testGC()
    {
        $handler = $this->getInstance();
        $this->assertSame(1, $handler->gc(3600));
    }
}
