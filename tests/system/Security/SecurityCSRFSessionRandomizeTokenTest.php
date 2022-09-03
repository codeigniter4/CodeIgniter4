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
use CodeIgniter\Test\Mock\MockSecurity;
use CodeIgniter\Test\Mock\MockSession;
use CodeIgniter\Test\TestLogger;
use Config\App as AppConfig;
use Config\Logger as LoggerConfig;
use Config\Security as SecurityConfig;

/**
 * @runTestsInSeparateProcesses
 *
 * @preserveGlobalState disabled
 *
 * @internal
 *
 * @group SeparateProcess
 */
final class SecurityCSRFSessionRandomizeTokenTest extends CIUnitTestCase
{
    /**
     * @var string CSRF protection hash
     */
    private string $hash = '8b9218a55906f9dcc1dc263dce7f005a';

    /**
     * @var string CSRF randomized token
     */
    private string $randomizedToken = '8bc70b67c91494e815c7d2219c1ae0ab005513c290126d34d41bf41c5265e0f1';

    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
        Factories::reset();

        $config                 = new SecurityConfig();
        $config->csrfProtection = Security::CSRF_PROTECTION_SESSION;
        $config->tokenRandomize = true;
        Factories::injectMock('config', 'Security', $config);

        $this->injectSession($this->hash);
    }

    private function createSession($options = []): Session
    {
        $defaults = [
            'sessionDriver'            => FileHandler::class,
            'sessionCookieName'        => 'ci_session',
            'sessionExpiration'        => 7200,
            'sessionSavePath'          => null,
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

        $session = new MockSession(new ArrayHandler($appConfig, '127.0.0.1'), $appConfig);
        $session->setLogger(new TestLogger(new LoggerConfig()));

        return $session;
    }

    private function injectSession(string $hash): void
    {
        $session = $this->createSession();
        $session->set('csrf_test_name', $hash);
        Services::injectMock('session', $session);
    }

    public function testHashIsReadFromSession()
    {
        $security = new MockSecurity(new MockAppConfig());

        $this->assertSame(
            $this->randomizedToken,
            $security->getHash()
        );
    }

    public function testCSRFVerifyPostNoToken()
    {
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('The action you requested is not allowed.');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        unset($_POST['csrf_test_name']);

        $request = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());

        $security = new Security(new MockAppConfig());

        $security->verify($request);
    }

    public function testCSRFVerifyPostThrowsExceptionOnNoMatch()
    {
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('The action you requested is not allowed.');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['csrf_test_name']   = '8b9218a55906f9dcc1dc263dce7f005b';

        $request = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());

        $security = new Security(new MockAppConfig());

        $security->verify($request);
    }

    public function testCSRFVerifyPostInvalidToken()
    {
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('The action you requested is not allowed.');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['csrf_test_name']   = '!';

        $request = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());

        $security = new Security(new MockAppConfig());

        $security->verify($request);
    }

    public function testCSRFVerifyPostReturnsSelfOnMatch()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['foo']              = 'bar';
        $_POST['csrf_test_name']   = $this->randomizedToken;

        $request = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());

        $security = new Security(new MockAppConfig());

        $this->assertInstanceOf(Security::class, $security->verify($request));
        $this->assertLogged('info', 'CSRF token verified.');
        $this->assertCount(1, $_POST);
    }

    public function testCSRFVerifyPOSTHeaderThrowsExceptionOnNoMatch()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());
        $request->setHeader('X-CSRF-TOKEN', '8b9218a55906f9dcc1dc263dce7f005b');

        $security = new Security(new MockAppConfig());

        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('The action you requested is not allowed.');

        $security->verify($request);
    }

    public function testCSRFVerifyPOSTHeaderReturnsSelfOnMatch()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['foo']              = 'bar';

        $request = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());
        $request->setHeader('X-CSRF-TOKEN', $this->randomizedToken);

        $security = new Security(new MockAppConfig());

        $this->assertInstanceOf(Security::class, $security->verify($request));
        $this->assertLogged('info', 'CSRF token verified.');
        $this->assertCount(1, $_POST);
    }

    public function testCSRFVerifyPUTHeaderThrowsExceptionOnNoMatch()
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';

        $request = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());
        $request->setHeader('X-CSRF-TOKEN', '8b9218a55906f9dcc1dc263dce7f005b');

        $security = new Security(new MockAppConfig());

        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('The action you requested is not allowed.');

        $security->verify($request);
    }

    public function testCSRFVerifyPUTHeaderReturnsSelfOnMatch()
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';

        $request = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());
        $request->setHeader('X-CSRF-TOKEN', $this->randomizedToken);

        $security = new Security(new MockAppConfig());

        $this->assertInstanceOf(Security::class, $security->verify($request));
        $this->assertLogged('info', 'CSRF token verified.');
    }

    public function testCSRFVerifyJsonThrowsExceptionOnNoMatch()
    {
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('The action you requested is not allowed.');

        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());
        $request->setBody('{"csrf_test_name":"8b9218a55906f9dcc1dc263dce7f005b"}');

        $security = new Security(new MockAppConfig());

        $security->verify($request);
    }

    public function testCSRFVerifyJsonReturnsSelfOnMatch()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());
        $request->setBody('{"csrf_test_name":"' . $this->randomizedToken . '","foo":"bar"}');

        $security = new Security(new MockAppConfig());

        $this->assertInstanceOf(Security::class, $security->verify($request));
        $this->assertLogged('info', 'CSRF token verified.');
        $this->assertSame('{"foo":"bar"}', $request->getBody());
    }

    public function testRegenerateWithFalseSecurityRegenerateProperty()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['csrf_test_name']   = $this->randomizedToken;

        $config                 = Factories::config('Security');
        $config->tokenRandomize = true;
        $config->regenerate     = false;
        Factories::injectMock('config', 'Security', $config);

        $request = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());

        $security = new MockSecurity(new MockAppConfig());

        $oldHash = $security->getHash();
        $security->verify($request);
        $newHash = $security->getHash();

        $this->assertSame($oldHash, $newHash);
    }

    public function testRegenerateWithTrueSecurityRegenerateProperty()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['csrf_test_name']   = $this->randomizedToken;

        $config                 = Factories::config('Security');
        $config->tokenRandomize = true;
        $config->regenerate     = true;
        Factories::injectMock('config', 'Security', $config);

        $request = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());

        $security = new Security(new MockAppConfig());

        $oldHash = $security->getHash();
        $security->verify($request);
        $newHash = $security->getHash();

        $this->assertNotSame($oldHash, $newHash);
    }
}
