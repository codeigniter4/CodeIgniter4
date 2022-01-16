<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Session\Handlers;

use CodeIgniter\Test\CIUnitTestCase;
use Config\App as AppConfig;

abstract class RedisHandlerTest extends CIUnitTestCase
{
    protected $sessionID;
    protected $sessionData;
    protected $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sessionID   = md5(random_bytes(32));
        $this->sessionData = '__ci_last_regenerate|i:1624650854;_ci_previous_url|s:40:\"http://localhost/index.php/home/index\";';

        // Setup initial session data.
        $s = $this->getInstance();
        $this->assertTrue($s->open('ci_session', 'ci_session'));
        $this->assertTrue($s->write($this->sessionID, $this->sessionData));
        $this->assertTrue($s->close());
    }

    protected function getInstance($options = []): ?BaseHandler
    {
        $defaults = [
            'sessionCookieName'        => 'ci_session',
            'sessionExpiration'        => 7200,
            'sessionSavePath'          => 'tcp://127.0.0.1:6379?timeout=3',
            'sessionMatchIP'           => false,
            'sessionTimeToUpdate'      => 300,
            'sessionRegenerateDestroy' => false,
            'cookieDomain'             => '',
            'cookiePrefix'             => '',
            'cookiePath'               => '/',
            'cookieSecure'             => false,
            'cookieSameSite'           => 'Lax',
            'ip'                       => '1.1.1.1',
        ];
        $config       = array_merge($defaults, $options);
        $this->config = new AppConfig();

        foreach ($config as $key => $c) {
            $this->config->{$key} = $c;
        }

        return null;
    }

    public function testOpen()
    {
        $handler = $this->getInstance();
        $this->assertTrue($handler->open('ci_session', 'ci_session'));
        $this->assertTrue($handler->close());
    }

    public function testReadSuccess()
    {
        $handler = $this->getInstance();
        $this->assertTrue($handler->open('ci_session', 'ci_session'));
        $this->assertSame($this->sessionData, $handler->read($this->sessionID));
        $this->assertTrue($handler->close());
    }

    public function testReadFailure()
    {
        $handler = $this->getInstance();
        $this->assertTrue($handler->open('ci_session', 'ci_session'));
        $this->assertSame('', $handler->read(md5(random_bytes(32))));
        $this->assertTrue($handler->close());
    }

    // Don't have a testWriteInsert because the setUp function does that.

    public function testWriteUpdate()
    {
        $data    = '__ci_last_regenerate|i:1642291331;_ci_previous_url|s:80:\"http://localhost/index.php/controller/function\";';
        $handler = $this->getInstance();
        $this->assertTrue($handler->open('ci_session', 'ci_session'));
        $this->assertTrue($handler->write($this->sessionID, $data));
        $this->assertTrue($handler->close());
        unset($handler);

        $handler = $this->getInstance();
        $this->assertTrue($handler->open('ci_session', 'ci_session'));
        $this->assertSame($data, $handler->read($this->sessionID));
        $this->assertTrue($handler->close());
    }

    public function testMatchIP()
    {
        $data      = '__ci_last_regenerate|i:1642292133;_ci_previous_url|s:100:\"http://localhost/index.php/newcontroller/newfunction\";';
        $sessionID = md5(random_bytes(32));
        // open new instance with matchIP
        $handler = $this->getInstance(['sessionMatchIP' => true]);
        $this->assertTrue($handler->open('ci_session', 'ci_session'));
        $this->assertTrue($handler->write($sessionID, $data));
        $this->assertTrue($handler->close());
        unset($handler);

        // open new instance without matchIP, expect mismatch on read.
        $handler = $this->getInstance();
        $this->assertTrue($handler->open('ci_session', 'ci_session'));
        $this->assertNotSame($data, $handler->read($sessionID));
        $this->assertTrue($handler->close());
        unset($handler);

        // open new instance with matchIP and different IP, expect mismatch on read.
        $handler = $this->getInstance(['sessionMatchIP' => true, 'ip' => '2.2.2.2']);
        $this->assertTrue($handler->open('ci_session', 'ci_session'));
        $this->assertNotSame($data, $handler->read($sessionID));
        $this->assertTrue($handler->close());
        unset($handler);
    }

    // no testGC, it always returns 1
}
