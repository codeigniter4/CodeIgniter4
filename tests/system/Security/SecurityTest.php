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

namespace CodeIgniter\Security;

use CodeIgniter\Config\Factories;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\SiteURI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Security\Exceptions\SecurityException;
use CodeIgniter\Superglobals;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockAppConfig;
use CodeIgniter\Test\Mock\MockSecurity;
use Config\Security as SecurityConfig;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[BackupGlobals(true)]
#[Group('Others')]
final class SecurityTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->resetServices();

        Services::injectMock('superglobals', new Superglobals(null, null, null, []));
    }

    private static function createMockSecurity(SecurityConfig $config = new SecurityConfig()): MockSecurity
    {
        return new MockSecurity($config);
    }

    private static function createIncomingRequest(): IncomingRequest
    {
        $config = new MockAppConfig();

        return new IncomingRequest(
            $config,
            new SiteURI($config),
            null,
            new UserAgent(),
        );
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
        service('superglobals')->setCookie('csrf_cookie_name', '8b9218a55906f9dcc1dc263dce7f005a');

        $security = $this->createMockSecurity();

        $this->assertSame(
            '8b9218a55906f9dcc1dc263dce7f005a',
            $security->getHash(),
        );
    }

    public function testGetHashSetsCookieWhenGETWithoutCSRFCookie(): void
    {
        $security = $this->createMockSecurity();

        service('superglobals')->setServer('REQUEST_METHOD', 'GET');

        $security->verify(new Request(new MockAppConfig()));

        $cookie = service('response')->getCookie('csrf_cookie_name');
        $this->assertSame($security->getHash(), $cookie->getValue());
    }

    public function testGetHashReturnsCSRFCookieWhenGETWithCSRFCookie(): void
    {
        service('superglobals')
            ->setServer('REQUEST_METHOD', 'GET')
            ->setCookie('csrf_cookie_name', '8b9218a55906f9dcc1dc263dce7f005a');

        $security = $this->createMockSecurity();

        $security->verify(new Request(new MockAppConfig()));

        $this->assertSame('8b9218a55906f9dcc1dc263dce7f005a', $security->getHash());
    }

    public function testCSRFVerifyPostThrowsExceptionOnNoMatch(): void
    {
        service('superglobals')
            ->setServer('REQUEST_METHOD', 'POST')
            ->setPost('csrf_test_name', '8b9218a55906f9dcc1dc263dce7f005a')
            ->setCookie('csrf_cookie_name', '8b9218a55906f9dcc1dc263dce7f005b');

        $security = $this->createMockSecurity();
        $request  = $this->createIncomingRequest();

        $this->expectException(SecurityException::class);
        $security->verify($request);
    }

    public function testCSRFVerifyPostReturnsSelfOnMatch(): void
    {
        service('superglobals')
            ->setServer('REQUEST_METHOD', 'POST')
            ->setPost('foo', 'bar')
            ->setPost('csrf_test_name', '8b9218a55906f9dcc1dc263dce7f005a')
            ->setCookie('csrf_cookie_name', '8b9218a55906f9dcc1dc263dce7f005a');

        $security = $this->createMockSecurity();
        $request  = $this->createIncomingRequest();

        $this->assertInstanceOf(Security::class, $security->verify($request));
        $this->assertLogged('info', 'CSRF token verified.');

        $this->assertCount(1, service('superglobals')->getPostArray());
    }

    public function testCSRFVerifyHeaderThrowsExceptionOnNoMatch(): void
    {
        service('superglobals')
            ->setServer('REQUEST_METHOD', 'POST')
            ->setCookie('csrf_cookie_name', '8b9218a55906f9dcc1dc263dce7f005b');

        $security = $this->createMockSecurity();
        $request  = $this->createIncomingRequest();

        $request->setHeader('X-CSRF-TOKEN', '8b9218a55906f9dcc1dc263dce7f005a');

        $this->expectException(SecurityException::class);
        $security->verify($request);
    }

    public function testCSRFVerifyHeaderReturnsSelfOnMatch(): void
    {
        service('superglobals')
            ->setServer('REQUEST_METHOD', 'POST')
            ->setPost('foo', 'bar')
            ->setCookie('csrf_cookie_name', '8b9218a55906f9dcc1dc263dce7f005a');

        $security = $this->createMockSecurity();
        $request  = $this->createIncomingRequest();

        $request->setHeader('X-CSRF-TOKEN', '8b9218a55906f9dcc1dc263dce7f005a');

        $this->assertInstanceOf(Security::class, $security->verify($request));
        $this->assertLogged('info', 'CSRF token verified.');

        $this->assertCount(1, service('superglobals')->getPostArray());
    }

    public function testCSRFVerifyJsonThrowsExceptionOnNoMatch(): void
    {
        service('superglobals')
            ->setServer('REQUEST_METHOD', 'POST')
            ->setCookie('csrf_cookie_name', '8b9218a55906f9dcc1dc263dce7f005b');

        $security = $this->createMockSecurity();
        $request  = $this->createIncomingRequest();

        $request->setBody(
            '{"csrf_test_name":"8b9218a55906f9dcc1dc263dce7f005a"}',
        );

        $this->expectException(SecurityException::class);
        $security->verify($request);
    }

    public function testCSRFVerifyJsonReturnsSelfOnMatch(): void
    {
        service('superglobals')
            ->setServer('REQUEST_METHOD', 'POST')
            ->setCookie('csrf_cookie_name', '8b9218a55906f9dcc1dc263dce7f005a');

        $security = $this->createMockSecurity();
        $request  = $this->createIncomingRequest();

        $request->setBody(
            '{"csrf_test_name":"8b9218a55906f9dcc1dc263dce7f005a","foo":"bar"}',
        );

        $this->assertInstanceOf(Security::class, $security->verify($request));
        $this->assertLogged('info', 'CSRF token verified.');

        $this->assertSame('{"foo":"bar"}', $request->getBody());
    }

    public function testCSRFVerifyPutBodyThrowsExceptionOnNoMatch(): void
    {
        service('superglobals')
            ->setServer('REQUEST_METHOD', 'PUT')
            ->setCookie('csrf_cookie_name', '8b9218a55906f9dcc1dc263dce7f005b');

        $security = $this->createMockSecurity();
        $request  = $this->createIncomingRequest();

        $request->setBody(
            'csrf_test_name=8b9218a55906f9dcc1dc263dce7f005a',
        );

        $this->expectException(SecurityException::class);
        $security->verify($request);
    }

    public function testCSRFVerifyPutBodyReturnsSelfOnMatch(): void
    {
        service('superglobals')
            ->setServer('REQUEST_METHOD', 'PUT')
            ->setCookie('csrf_cookie_name', '8b9218a55906f9dcc1dc263dce7f005a');

        $security = $this->createMockSecurity();
        $request  = $this->createIncomingRequest();

        $request->setBody(
            'csrf_test_name=8b9218a55906f9dcc1dc263dce7f005a&foo=bar',
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
        service('superglobals')
            ->setServer('REQUEST_METHOD', 'POST')
            ->setPost('csrf_test_name', '8b9218a55906f9dcc1dc263dce7f005a')
            ->setCookie('csrf_cookie_name', '8b9218a55906f9dcc1dc263dce7f005a');

        $config             = new SecurityConfig();
        $config->regenerate = false;
        Factories::injectMock('config', 'Security', $config);

        $security = new MockSecurity($config);
        $request  = $this->createIncomingRequest();

        $oldHash = $security->getHash();
        $security->verify($request);
        $newHash = $security->getHash();

        $this->assertSame($oldHash, $newHash);
    }

    public function testRegenerateWithFalseSecurityRegeneratePropertyManually(): void
    {
        service('superglobals')
            ->setServer('REQUEST_METHOD', 'POST')
            ->setPost('csrf_test_name', '8b9218a55906f9dcc1dc263dce7f005a')
            ->setCookie('csrf_cookie_name', '8b9218a55906f9dcc1dc263dce7f005a');

        $config             = new SecurityConfig();
        $config->regenerate = false;
        Factories::injectMock('config', 'Security', $config);

        $security = $this->createMockSecurity($config);
        $request  = $this->createIncomingRequest();

        $oldHash = $security->getHash();
        $security->verify($request);
        $security->generateHash();
        $newHash = $security->getHash();

        $this->assertNotSame($oldHash, $newHash);
    }

    public function testRegenerateWithTrueSecurityRegenerateProperty(): void
    {
        service('superglobals')
            ->setServer('REQUEST_METHOD', 'POST')
            ->setPost('csrf_test_name', '8b9218a55906f9dcc1dc263dce7f005a')
            ->setCookie('csrf_cookie_name', '8b9218a55906f9dcc1dc263dce7f005a');

        $config             = new SecurityConfig();
        $config->regenerate = true;
        Factories::injectMock('config', 'Security', $config);

        $security = $this->createMockSecurity($config);
        $request  = $this->createIncomingRequest();

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

    public function testGetPostedTokenReturnsTokenFromPost(): void
    {
        service('superglobals')->setPost('csrf_test_name', '8b9218a55906f9dcc1dc263dce7f005a');
        $request = $this->createIncomingRequest();
        $method  = self::getPrivateMethodInvoker($this->createMockSecurity(), 'getPostedToken');

        $this->assertSame('8b9218a55906f9dcc1dc263dce7f005a', $method($request));
    }

    public function testGetPostedTokenReturnsTokenFromHeader(): void
    {
        $request = $this->createIncomingRequest()->setHeader('X-CSRF-TOKEN', '8b9218a55906f9dcc1dc263dce7f005a');
        $method  = self::getPrivateMethodInvoker($this->createMockSecurity(), 'getPostedToken');

        $this->assertSame('8b9218a55906f9dcc1dc263dce7f005a', $method($request));
    }

    public function testGetPostedTokenReturnsTokenFromJsonBody(): void
    {
        $jsonBody = json_encode(['csrf_test_name' => '8b9218a55906f9dcc1dc263dce7f005a']);
        $request  = $this->createIncomingRequest()->setBody($jsonBody);
        $method   = self::getPrivateMethodInvoker($this->createMockSecurity(), 'getPostedToken');

        $this->assertSame('8b9218a55906f9dcc1dc263dce7f005a', $method($request));
    }

    public function testGetPostedTokenReturnsTokenFromFormBody(): void
    {
        $formBody = 'csrf_test_name=8b9218a55906f9dcc1dc263dce7f005a';
        $request  = $this->createIncomingRequest()->setBody($formBody);
        $method   = self::getPrivateMethodInvoker($this->createMockSecurity(), 'getPostedToken');

        $this->assertSame('8b9218a55906f9dcc1dc263dce7f005a', $method($request));
    }

    #[DataProvider('provideGetPostedTokenReturnsNullForInvalidInputs')]
    public function testGetPostedTokenReturnsNullForInvalidInputs(string $case, IncomingRequest $request): void
    {
        $method = self::getPrivateMethodInvoker($this->createMockSecurity(), 'getPostedToken');

        $this->assertNull(
            $method($request),
            sprintf('Failed asserting that %s returns null on invalid input.', $case),
        );
    }

    /**
     * @return iterable<string, array{string, IncomingRequest}>
     */
    public static function provideGetPostedTokenReturnsNullForInvalidInputs(): iterable
    {
        $testCases = [
            'empty_post'            => self::createIncomingRequest(),
            'invalid_post_data'     => self::createIncomingRequest()->setGlobal('post', ['csrf_test_name' => ['invalid' => 'data']]),
            'empty_header'          => self::createIncomingRequest()->setHeader('X-CSRF-TOKEN', ''),
            'invalid_json_data'     => self::createIncomingRequest()->setBody(json_encode(['csrf_test_name' => ['invalid' => 'data']])),
            'invalid_json'          => self::createIncomingRequest()->setBody('{invalid json}'),
            'missing_token_in_body' => self::createIncomingRequest()->setBody('other=value&another=test'),
            'invalid_form_data'     => self::createIncomingRequest()->setBody('csrf_test_name[]=invalid'),
        ];

        foreach ($testCases as $case => $request) {
            yield $case => [$case, $request];
        }
    }
}
