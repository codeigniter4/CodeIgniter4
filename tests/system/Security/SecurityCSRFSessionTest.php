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
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Security\Exceptions\SecurityException;
use CodeIgniter\Session\Handlers\ArrayHandler;
use CodeIgniter\Session\Handlers\FileHandler;
use CodeIgniter\Session\Session;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockAppConfig;
use CodeIgniter\Test\Mock\MockSession;
use CodeIgniter\Test\TestLogger;
use Config\Cookie;
use Config\Logger as LoggerConfig;
use Config\Security as SecurityConfig;
use Config\Session as SessionConfig;

/**
 * @runTestsInSeparateProcesses
 *
 * @preserveGlobalState disabled
 *
 * @internal
 *
 * @group SeparateProcess
 */
final class SecurityCSRFSessionTest extends CIUnitTestCase
{
    /**
     * @var string CSRF protection hash
     */
    private string $hash = '8b9218a55906f9dcc1dc263dce7f005a';

    private SecurityConfig $config;

    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
        Factories::reset();

        $this->config                 = new SecurityConfig();
        $this->config->csrfProtection = Security::CSRF_PROTECTION_SESSION;
        Factories::injectMock('config', 'Security', $this->config);

        $this->injectSession($this->hash);
    }

    private function createSession($options = []): Session
    {
        $defaults = [
            'driver'            => FileHandler::class,
            'cookieName'        => 'ci_session',
            'expiration'        => 7200,
            'savePath'          => '',
            'matchIP'           => false,
            'timeToUpdate'      => 300,
            'regenerateDestroy' => false,
        ];
        $config = array_merge($defaults, $options);

        $sessionConfig = new SessionConfig();

        foreach ($config as $key => $c) {
            $sessionConfig->{$key} = $c;
        }

        $cookie = new Cookie();

        foreach ([
            'prefix'   => '',
            'domain'   => '',
            'path'     => '/',
            'secure'   => false,
            'samesite' => 'Lax',
        ] as $key => $value) {
            $cookie->{$key} = $value;
        }
        Factories::injectMock('config', 'Cookie', $cookie);

        $session = new MockSession(new ArrayHandler($sessionConfig, '127.0.0.1'), $sessionConfig);
        $session->setLogger(new TestLogger(new LoggerConfig()));

        return $session;
    }

    private function injectSession(string $hash): void
    {
        $session = $this->createSession();
        $session->set('csrf_test_name', $hash);
        Services::injectMock('session', $session);
    }

    private function createSecurity(): Security
    {
        return new Security($this->config);
    }

    public function testHashIsReadFromSession(): void
    {
        $security = $this->createSecurity();

        $this->assertSame($this->hash, $security->getHash());
    }

    public function testCSRFVerifyPostThrowsExceptionOnNoMatch(): void
    {
        $this->expectException(SecurityException::class);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['csrf_test_name']   = '8b9218a55906f9dcc1dc263dce7f005b';

        $request = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());

        $security = $this->createSecurity();

        $security->verify($request);
    }

    public function testCSRFVerifyPostReturnsSelfOnMatch(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['foo']              = 'bar';
        $_POST['csrf_test_name']   = '8b9218a55906f9dcc1dc263dce7f005a';

        $request = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());

        $security = $this->createSecurity();

        $this->assertInstanceOf(Security::class, $security->verify($request));
        $this->assertLogged('info', 'CSRF token verified.');
        $this->assertCount(1, $_POST);
    }

    public function testCSRFVerifyPOSTHeaderThrowsExceptionOnNoMatch(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());
        $request->setHeader('X-CSRF-TOKEN', '8b9218a55906f9dcc1dc263dce7f005b');

        $security = $this->createSecurity();

        $this->expectException(SecurityException::class);
        $security->verify($request);
    }

    public function testCSRFVerifyPOSTHeaderReturnsSelfOnMatch(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['foo']              = 'bar';

        $request = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());
        $request->setHeader('X-CSRF-TOKEN', '8b9218a55906f9dcc1dc263dce7f005a');

        $security = $this->createSecurity();

        $this->assertInstanceOf(Security::class, $security->verify($request));
        $this->assertLogged('info', 'CSRF token verified.');
        $this->assertCount(1, $_POST);
    }

    public function testCSRFVerifyPUTHeaderThrowsExceptionOnNoMatch(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';

        $request = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());
        $request->setHeader('X-CSRF-TOKEN', '8b9218a55906f9dcc1dc263dce7f005b');

        $security = $this->createSecurity();

        $this->expectException(SecurityException::class);
        $security->verify($request);
    }

    public function testCSRFVerifyPUTHeaderReturnsSelfOnMatch(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';

        $request = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());
        $request->setHeader('X-CSRF-TOKEN', '8b9218a55906f9dcc1dc263dce7f005a');

        $security = $this->createSecurity();

        $this->assertInstanceOf(Security::class, $security->verify($request));
        $this->assertLogged('info', 'CSRF token verified.');
    }

    public function testCSRFVerifyPUTBodyReturnsSelfOnMatch(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';

        $request = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());
        $request->setBody('csrf_test_name=8b9218a55906f9dcc1dc263dce7f005a&foo=bar');

        $security = $this->createSecurity();

        $this->assertInstanceOf(Security::class, $security->verify($request));
        $this->assertLogged('info', 'CSRF token verified.');
    }

    public function testCSRFVerifyJsonThrowsExceptionOnNoMatch(): void
    {
        $this->expectException(SecurityException::class);

        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());
        $request->setBody('{"csrf_test_name":"8b9218a55906f9dcc1dc263dce7f005b"}');

        $security = $this->createSecurity();

        $security->verify($request);
    }

    public function testCSRFVerifyJsonReturnsSelfOnMatch(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());
        $request->setBody('{"csrf_test_name":"8b9218a55906f9dcc1dc263dce7f005a","foo":"bar"}');

        $security = $this->createSecurity();

        $this->assertInstanceOf(Security::class, $security->verify($request));
        $this->assertLogged('info', 'CSRF token verified.');
        $this->assertSame('{"foo":"bar"}', $request->getBody());
    }

    public function testRegenerateWithFalseSecurityRegenerateProperty(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['csrf_test_name']   = '8b9218a55906f9dcc1dc263dce7f005a';

        $config             = Factories::config('Security');
        $config->regenerate = false;
        Factories::injectMock('config', 'Security', $config);

        $request = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());

        $security = $this->createSecurity();

        $oldHash = $security->getHash();
        $security->verify($request);
        $newHash = $security->getHash();

        $this->assertSame($oldHash, $newHash);
    }

    public function testRegenerateWithTrueSecurityRegenerateProperty(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['csrf_test_name']   = '8b9218a55906f9dcc1dc263dce7f005a';

        $config             = Factories::config('Security');
        $config->regenerate = true;
        Factories::injectMock('config', 'Security', $config);

        $request = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());

        $security = $this->createSecurity();

        $oldHash = $security->getHash();
        $security->verify($request);
        $newHash = $security->getHash();

        $this->assertNotSame($oldHash, $newHash);
    }
}
