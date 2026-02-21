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
use CodeIgniter\HTTP\SiteURI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Superglobals;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockAppConfig;
use CodeIgniter\Test\Mock\MockSecurity;
use Config\Security as SecurityConfig;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class SecurityCSRFCookieRandomizeTokenTest extends CIUnitTestCase
{
    private const CSRF_PROTECTION_HASH  = '8b9218a55906f9dcc1dc263dce7f005a';
    private const CSRF_RANDOMIZED_TOKEN = '8bc70b67c91494e815c7d2219c1ae0ab005513c290126d34d41bf41c5265e0f1';

    protected function setUp(): void
    {
        parent::setUp();

        Services::injectMock('superglobals', new Superglobals(post: [], cookie: []));

        $config                 = new SecurityConfig();
        $config->csrfProtection = Security::CSRF_PROTECTION_COOKIE;
        $config->tokenRandomize = true;
        Factories::injectMock('config', 'Security', $config);

        $security = new MockSecurity($config);
        service('superglobals')->setCookie($security->getCookieName(), self::CSRF_PROTECTION_HASH);

        $this->resetServices();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->resetServices();
        Factories::reset('config');
    }

    public function testTokenIsReadFromCookie(): void
    {
        $security = new MockSecurity(config('Security'));

        $this->assertSame(self::CSRF_RANDOMIZED_TOKEN, $security->getHash());
    }

    public function testCsrfVerifySetNewCookie(): void
    {
        service('superglobals')
            ->setServer('REQUEST_METHOD', 'POST')
            ->setPost('foo', 'bar')
            ->setPost('csrf_test_name', self::CSRF_RANDOMIZED_TOKEN);

        $config  = new MockAppConfig();
        $request = new IncomingRequest($config, new SiteURI($config), null, new UserAgent());

        $security = new Security(config('Security'));

        $this->assertInstanceOf(Security::class, $security->verify($request));
        $this->assertLogged('info', 'CSRF token verified.');
        $this->assertSame(['foo' => 'bar'], service('superglobals')->getPostArray());

        $cookieHash = service('response')->getCookie($security->getCookieName())->getValue();
        $this->assertNotSame(self::CSRF_PROTECTION_HASH, $cookieHash);
        $this->assertSame(32, strlen($cookieHash));
    }
}
