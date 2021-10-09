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
use CodeIgniter\Security\CSRF\CSRFConfig;
use CodeIgniter\Security\Exceptions\SecurityException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockAppConfig;
use CodeIgniter\Test\Mock\MockCSRFCookie;
use CodeIgniter\Test\Mock\MockSecurity;
use Config\App as AppConfig;
use Config\Security as SecurityConfig;

/**
 * @backupGlobals enabled
 *
 * @internal
 */
final class SecurityTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_COOKIE = [];

        Factories::reset();
    }

    public function testBasicConfigIsSaved()
    {
        $security = new Security(new MockAppConfig());

        $hash = $security->getHash();

        $this->assertSame(32, strlen($hash));
        $this->assertSame('csrf_test_name', $security->getTokenName());
    }

    public function testHashIsReadFromCookie()
    {
        $_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005a';

        $security = new Security(new MockAppConfig());

        $this->assertSame('8b9218a55906f9dcc1dc263dce7f005a', $security->getHash());
    }

    private function createSecurity(?AppConfig $appConfig = null, ?SecurityConfig $securityConfig = null): Security
    {
        $appConfig = $appConfig ?? new MockAppConfig();
        $security  = new MockSecurity($appConfig);
        $security->setCsrf(new MockCSRFCookie(new CSRFConfig($securityConfig), config('Cookie')));

        return $security;
    }

    public function testCSRFVerifySetsCookieWhenNotPOST()
    {
        $security = $this->createSecurity();

        $_SERVER['REQUEST_METHOD'] = 'GET';

        $security->verify(new Request(new MockAppConfig()));

        $this->assertSame($_COOKIE['csrf_cookie_name'], $security->getHash());
    }

    public function testCSRFVerifyPostThrowsExceptionOnNoMatch()
    {
        $security = new MockSecurity(new MockAppConfig());
        $request  = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());

        $_SERVER['REQUEST_METHOD']   = 'POST';
        $_POST['csrf_test_name']     = '8b9218a55906f9dcc1dc263dce7f005a';
        $_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005b';

        $this->expectException(SecurityException::class);
        $security->verify($request);
    }

    public function testCSRFVerifyPostReturnsSelfOnMatch()
    {
        $security = $this->createSecurity();

        $request = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());

        $_SERVER['REQUEST_METHOD']   = 'POST';
        $_POST['foo']                = 'bar';
        $_POST['csrf_test_name']     = '8b9218a55906f9dcc1dc263dce7f005a';
        $_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005a';

        $this->assertInstanceOf(Security::class, $security->verify($request));
        $this->assertLogged('info', 'CSRF token verified.');

        $this->assertTrue(count($_POST) === 1);
    }

    public function testCSRFVerifyHeaderThrowsExceptionOnNoMatch()
    {
        $security = new MockSecurity(new MockAppConfig());
        $request  = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());

        $request->setHeader('X-CSRF-TOKEN', '8b9218a55906f9dcc1dc263dce7f005a');

        $_SERVER['REQUEST_METHOD']   = 'POST';
        $_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005b';

        $this->expectException(SecurityException::class);
        $security->verify($request);
    }

    public function testCSRFVerifyHeaderReturnsSelfOnMatch()
    {
        $security = $this->createSecurity();

        $request = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());

        $request->setHeader('X-CSRF-TOKEN', '8b9218a55906f9dcc1dc263dce7f005a');

        $_SERVER['REQUEST_METHOD']   = 'POST';
        $_POST['foo']                = 'bar';
        $_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005a';

        $this->assertInstanceOf(Security::class, $security->verify($request));
        $this->assertLogged('info', 'CSRF token verified.');

        $this->assertCount(1, $_POST);
    }

    public function testCSRFVerifyJsonThrowsExceptionOnNoMatch()
    {
        $security = new MockSecurity(new MockAppConfig());
        $request  = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());

        $request->setBody('{"csrf_test_name":"8b9218a55906f9dcc1dc263dce7f005a"}');

        $_SERVER['REQUEST_METHOD']   = 'POST';
        $_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005b';

        $this->expectException(SecurityException::class);
        $security->verify($request);
    }

    public function testCSRFVerifyJsonReturnsSelfOnMatch()
    {
        $appConfig = new MockAppConfig();
        $security  = $this->createSecurity($appConfig);

        $request = new IncomingRequest($appConfig, new URI('http://badurl.com'), null, new UserAgent());

        $request->setBody('{"csrf_test_name":"8b9218a55906f9dcc1dc263dce7f005a","foo":"bar"}');

        $_SERVER['REQUEST_METHOD']   = 'POST';
        $_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005a';

        $this->assertInstanceOf(Security::class, $security->verify($request));
        $this->assertLogged('info', 'CSRF token verified.');

        $this->assertTrue($request->getBody() === '{"foo":"bar"}');
    }

    public function testSanitizeFilename()
    {
        $security = new MockSecurity(new MockAppConfig());

        $filename = './<!--foo-->';

        $this->assertSame('foo', $security->sanitizeFilename($filename));
    }

    public function testRegenerateWithFalseSecurityRegenerateProperty()
    {
        $securityConfig             = new SecurityConfig();
        $securityConfig->regenerate = false;
        Factories::injectMock('config', 'Security', $securityConfig);

        $appConfig = new MockAppConfig();
        $security  = $this->createSecurity($appConfig, $securityConfig);

        $request = new IncomingRequest($appConfig, new URI('http://badurl.com'), null, new UserAgent());

        $_SERVER['REQUEST_METHOD']   = 'POST';
        $_POST['csrf_test_name']     = '8b9218a55906f9dcc1dc263dce7f005a';
        $_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005a';

        $oldHash = $security->getHash();
        $security->verify($request);
        $newHash = $security->getHash();

        $this->assertSame($oldHash, $newHash);
    }

    public function testRegenerateWithTrueSecurityRegenerateProperty()
    {
        $securityConfig             = new SecurityConfig();
        $securityConfig->regenerate = true;
        Factories::injectMock('config', 'Security', $securityConfig);

        $appConfig = new MockAppConfig();
        $security  = $this->createSecurity($appConfig, $securityConfig);

        $request = new IncomingRequest($appConfig, new URI('http://badurl.com'), null, new UserAgent());

        $_SERVER['REQUEST_METHOD']   = 'POST';
        $_POST['csrf_test_name']     = '8b9218a55906f9dcc1dc263dce7f005a';
        $_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005a';

        $oldHash = $security->getHash();
        $security->verify($request);
        $newHash = $security->getHash();

        $this->assertNotSame($oldHash, $newHash);
    }

    public function testGetters(): void
    {
        $security = new MockSecurity(new MockAppConfig());

        $this->assertIsString($security->getHash());
        $this->assertIsString($security->getTokenName());
        $this->assertIsString($security->getHeaderName());
        $this->assertIsString($security->getCookieName());
        $this->assertIsBool($security->shouldRedirect());
    }
}
