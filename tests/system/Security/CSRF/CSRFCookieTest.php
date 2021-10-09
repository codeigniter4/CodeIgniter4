<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Securit\CSRF;

use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Security\CSRF\CSRFConfig;
use CodeIgniter\Security\CSRF\CSRFCookie;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockAppConfig;
use Config\Cookie as CookieConfig;

/**
 * @backupGlobals enabled
 *
 * @internal
 */
final class CSRFCookieTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_COOKIE = [];

        Factories::reset();
    }

    public function testSendingCookiesFalse(): void
    {
        $request = $this->createMock(IncomingRequest::class);
        $request->method('isSecure')->willReturn(false);

        $cookieConfig = new CookieConfig();
        $csrfConfig   = new CSRFConfig();

        $cookieConfig->secure = true;
        Factories::injectMock('config', CookieConfig::class, $cookieConfig);

        $security = $this->getMockBuilder(CSRFCookie::class)
            ->setConstructorArgs([$csrfConfig, $cookieConfig])
            ->onlyMethods(['doSendCookie'])
            ->getMock();

        $sendCookie = $this->getPrivateMethodInvoker($security, 'sendCookie');

        $security->expects($this->never())->method('doSendCookie');
        $this->assertFalse($sendCookie($request));
    }

    public function testSendingGoodCookies(): void
    {
        $request = $this->createMock(IncomingRequest::class);
        $request->method('isSecure')->willReturn(true);

        $config       = new MockAppConfig();
        $cookieConfig = new CookieConfig();
        $csrfConfig   = new CSRFConfig();

        $config->cookieSecure = true;

        $security = $this->getMockBuilder(CSRFCookie::class)
            ->setConstructorArgs([$csrfConfig, $cookieConfig])
            ->onlyMethods(['doSendCookie'])
            ->getMock();

        $sendCookie = $this->getPrivateMethodInvoker($security, 'sendCookie');

        $security->expects($this->once())->method('doSendCookie');
        $this->assertTrue($sendCookie($request));
    }
}
