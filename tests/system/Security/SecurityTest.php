<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Security;

use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Security\Exceptions\SecurityException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockAppConfig;
use CodeIgniter\Test\Mock\MockSecurity;
use Config\Security as SecurityConfig;
use Config\Services;

/**
 * @backupGlobals enabled
 *
 * @internal
 *
 * @group Others
 */
final class SecurityTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_COOKIE = [];

        $this->resetServices();
    }

    private function createMockSecurity(?SecurityConfig $config = null): MockSecurity
    {
        $config ??= new SecurityConfig();

        return new MockSecurity($config);
    }

    public function testBasicConfigIsSaved(): void
    {
        $security = $this->createMockSecurity();

        $hash = $security->getHash();

        $this->assertSame(32, strlen($hash));
        $this->assertSame('csrf_test_name', $security->getTokenName());
    }

    public function testHashIsReadFromCookie(): void
    {
        $_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005a';

        $security = $this->createMockSecurity();

        $this->assertSame(
            '8b9218a55906f9dcc1dc263dce7f005a',
            $security->getHash()
        );
    }

    public function testGetHashSetsCookieWhenGETWithoutCSRFCookie(): void
    {
        $security = $this->createMockSecurity();

        $_SERVER['REQUEST_METHOD'] = 'GET';

        $security->verify(new Request(new MockAppConfig()));

        $cookie = Services::response()->getCookie('csrf_cookie_name');
        $this->assertSame($security->getHash(), $cookie->getValue());
    }

    public function testGetHashReturnsCSRFCookieWhenGETWithCSRFCookie(): void
    {
        $_SERVER['REQUEST_METHOD']   = 'GET';
        $_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005a';

        $security = $this->createMockSecurity();

        $security->verify(new Request(new MockAppConfig()));

        $this->assertSame($_COOKIE['csrf_cookie_name'], $security->getHash());
    }

    public function testCSRFVerifyPostThrowsExceptionOnNoMatch(): void
    {
        $_SERVER['REQUEST_METHOD']   = 'POST';
        $_POST['csrf_test_name']     = '8b9218a55906f9dcc1dc263dce7f005a';
        $_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005b';

        $security = $this->createMockSecurity();
        $request  = new IncomingRequest(
            new MockAppConfig(),
            new URI('http://badurl.com'),
            null,
            new UserAgent()
        );

        $this->expectException(SecurityException::class);
        $security->verify($request);
    }

    public function testCSRFVerifyPostReturnsSelfOnMatch(): void
    {
        $_SERVER['REQUEST_METHOD']   = 'POST';
        $_POST['foo']                = 'bar';
        $_POST['csrf_test_name']     = '8b9218a55906f9dcc1dc263dce7f005a';
        $_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005a';

        $security = $this->createMockSecurity();
        $request  = new IncomingRequest(
            new MockAppConfig(),
            new URI('http://badurl.com'),
            null,
            new UserAgent()
        );

        $this->assertInstanceOf(Security::class, $security->verify($request));
        $this->assertLogged('info', 'CSRF token verified.');

        $this->assertCount(1, $_POST);
    }

    public function testCSRFVerifyHeaderThrowsExceptionOnNoMatch(): void
    {
        $_SERVER['REQUEST_METHOD']   = 'POST';
        $_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005b';

        $security = $this->createMockSecurity();
        $request  = new IncomingRequest(
            new MockAppConfig(),
            new URI('http://badurl.com'),
            null,
            new UserAgent()
        );

        $request->setHeader('X-CSRF-TOKEN', '8b9218a55906f9dcc1dc263dce7f005a');

        $this->expectException(SecurityException::class);
        $security->verify($request);
    }

    public function testCSRFVerifyHeaderReturnsSelfOnMatch(): void
    {
        $_SERVER['REQUEST_METHOD']   = 'POST';
        $_POST['foo']                = 'bar';
        $_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005a';

        $security = $this->createMockSecurity();
        $request  = new IncomingRequest(
            new MockAppConfig(),
            new URI('http://badurl.com'),
            null,
            new UserAgent()
        );

        $request->setHeader('X-CSRF-TOKEN', '8b9218a55906f9dcc1dc263dce7f005a');

        $this->assertInstanceOf(Security::class, $security->verify($request));
        $this->assertLogged('info', 'CSRF token verified.');

        $this->assertCount(1, $_POST);
    }

    public function testCSRFVerifyJsonThrowsExceptionOnNoMatch(): void
    {
        $_SERVER['REQUEST_METHOD']   = 'POST';
        $_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005b';

        $security = $this->createMockSecurity();
        $request  = new IncomingRequest(
            new MockAppConfig(),
            new URI('http://badurl.com'),
            null,
            new UserAgent()
        );

        $request->setBody(
            '{"csrf_test_name":"8b9218a55906f9dcc1dc263dce7f005a"}'
        );

        $this->expectException(SecurityException::class);
        $security->verify($request);
    }

    public function testCSRFVerifyJsonReturnsSelfOnMatch(): void
    {
        $_SERVER['REQUEST_METHOD']   = 'POST';
        $_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005a';

        $security = $this->createMockSecurity();
        $request  = new IncomingRequest(
            new MockAppConfig(),
            new URI('http://badurl.com'),
            null,
            new UserAgent()
        );

        $request->setBody(
            '{"csrf_test_name":"8b9218a55906f9dcc1dc263dce7f005a","foo":"bar"}'
        );

        $this->assertInstanceOf(Security::class, $security->verify($request));
        $this->assertLogged('info', 'CSRF token verified.');

        $this->assertSame('{"foo":"bar"}', $request->getBody());
    }

    public function testCSRFVerifyPutBodyThrowsExceptionOnNoMatch(): void
    {
        $_SERVER['REQUEST_METHOD']   = 'PUT';
        $_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005b';

        $security = $this->createMockSecurity();
        $request  = new IncomingRequest(
            new MockAppConfig(),
            new URI('http://badurl.com'),
            null,
            new UserAgent()
        );

        $request->setBody(
            'csrf_test_name=8b9218a55906f9dcc1dc263dce7f005a'
        );

        $this->expectException(SecurityException::class);
        $security->verify($request);
    }

    public function testCSRFVerifyPutBodyReturnsSelfOnMatch(): void
    {
        $_SERVER['REQUEST_METHOD']   = 'PUT';
        $_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005a';

        $security = $this->createMockSecurity();
        $request  = new IncomingRequest(
            new MockAppConfig(),
            new URI('http://badurl.com'),
            null,
            new UserAgent()
        );

        $request->setBody(
            'csrf_test_name=8b9218a55906f9dcc1dc263dce7f005a&foo=bar'
        );

        $this->assertInstanceOf(Security::class, $security->verify($request));
        $this->assertLogged('info', 'CSRF token verified.');

        $this->assertSame('foo=bar', $request->getBody());
    }

    public function testSanitizeFilename(): void
    {
        $security = $this->createMockSecurity();

        $filename = './<!--foo-->';

        $this->assertSame('foo', $security->sanitizeFilename($filename));
    }

    public function testRegenerateWithFalseSecurityRegenerateProperty(): void
    {
        $_SERVER['REQUEST_METHOD']   = 'POST';
        $_POST['csrf_test_name']     = '8b9218a55906f9dcc1dc263dce7f005a';
        $_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005a';

        $config             = new SecurityConfig();
        $config->regenerate = false;
        Factories::injectMock('config', 'Security', $config);

        $security = new MockSecurity($config);
        $request  = new IncomingRequest(
            new MockAppConfig(),
            new URI('http://badurl.com'),
            null,
            new UserAgent()
        );

        $oldHash = $security->getHash();
        $security->verify($request);
        $newHash = $security->getHash();

        $this->assertSame($oldHash, $newHash);
    }

    public function testRegenerateWithFalseSecurityRegeneratePropertyManually(): void
    {
        $_SERVER['REQUEST_METHOD']   = 'POST';
        $_POST['csrf_test_name']     = '8b9218a55906f9dcc1dc263dce7f005a';
        $_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005a';

        $config             = new SecurityConfig();
        $config->regenerate = false;
        Factories::injectMock('config', 'Security', $config);

        $security = $this->createMockSecurity($config);
        $request  = new IncomingRequest(
            new MockAppConfig(),
            new URI('http://badurl.com'),
            null,
            new UserAgent()
        );

        $oldHash = $security->getHash();
        $security->verify($request);
        $security->generateHash();
        $newHash = $security->getHash();

        $this->assertNotSame($oldHash, $newHash);
    }

    public function testRegenerateWithTrueSecurityRegenerateProperty(): void
    {
        $_SERVER['REQUEST_METHOD']   = 'POST';
        $_POST['csrf_test_name']     = '8b9218a55906f9dcc1dc263dce7f005a';
        $_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005a';

        $config             = new SecurityConfig();
        $config->regenerate = true;
        Factories::injectMock('config', 'Security', $config);

        $security = $this->createMockSecurity($config);
        $request  = new IncomingRequest(
            new MockAppConfig(),
            new URI('http://badurl.com'),
            null,
            new UserAgent()
        );

        $oldHash = $security->getHash();
        $security->verify($request);
        $newHash = $security->getHash();

        $this->assertNotSame($oldHash, $newHash);
    }

    public function testGetters(): void
    {
        $security = $this->createMockSecurity();

        $this->assertIsString($security->getHash());
        $this->assertIsString($security->getTokenName());
        $this->assertIsString($security->getHeaderName());
        $this->assertIsString($security->getCookieName());
        $this->assertIsBool($security->shouldRedirect());
    }
}
