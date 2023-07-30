<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Helpers;

use CodeIgniter\Config\Factories;
use CodeIgniter\Cookie\Exceptions\CookieException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockResponse;
use Config\App;
use Config\Cookie;
use Config\Cookie as CookieConfig;
use Config\Services;

/**
 * @internal
 *
 * @group Others
 */
final class CookieHelperTest extends CIUnitTestCase
{
    private IncomingRequest $request;
    private string $name;
    private string $value;
    private int $expire;
    private Response $response;

    protected function setUp(): void
    {
        $_COOKIE = [];

        parent::setUp();

        $this->name   = 'greetings';
        $this->value  = 'hello world';
        $this->expire = 9999;

        Services::injectMock('response', new MockResponse(new App()));
        $this->response = Services::response();
        $this->request  = new IncomingRequest(new App(), new URI(), null, new UserAgent());
        Services::injectMock('request', $this->request);

        helper('cookie');
    }

    public function testSetCookie(): void
    {
        set_cookie($this->name, $this->value, $this->expire);

        $this->assertTrue($this->response->hasCookie($this->name));

        delete_cookie($this->name);
    }

    public function testHasCookie(): void
    {
        $cookieAttr = [
            'name'   => $this->name,
            'value'  => $this->value,
            'expire' => $this->expire,
        ];
        set_cookie($cookieAttr);

        $this->assertTrue(has_cookie($this->name, $this->value));

        delete_cookie($this->name);
    }

    public function testSetCookieByArrayParameters(): void
    {
        $cookieAttr = [
            'name'   => $this->name,
            'value'  => $this->value,
            'expire' => $this->expire,
        ];
        set_cookie($cookieAttr);

        $this->assertTrue($this->response->hasCookie($this->name, $this->value));

        delete_cookie($this->name);
    }

    public function testSetCookieConfigCookieIsUsed(): void
    {
        /** @var Cookie $config */
        $config           = config('Cookie');
        $config->secure   = true;
        $config->httponly = true;
        $config->samesite = 'None';
        Factories::injectMock('config', 'Cookie', $config);

        $cookieAttr = [
            'name'   => $this->name,
            'value'  => $this->value,
            'expire' => $this->expire,
        ];
        set_cookie($cookieAttr);

        $cookie  = $this->response->getCookie($this->name);
        $options = $cookie->getOptions();
        $this->assertTrue($options['secure']);
        $this->assertTrue($options['httponly']);
        $this->assertSame('None', $options['samesite']);

        delete_cookie($this->name);
    }

    public function testSetCookieSecured(): void
    {
        $pre       = 'Hello, I try to';
        $pst       = 'your site';
        $unsec     = "{$pre} <script>alert('Hack');</script> {$pst}";
        $sec       = "{$pre} [removed]alert&#40;&#39;Hack&#39;&#41;;[removed] {$pst}";
        $unsecured = 'unsecured';
        $secured   = 'secured';

        set_cookie($unsecured, $unsec, $this->expire);
        set_cookie($secured, $sec, $this->expire);

        $this->assertTrue($this->response->hasCookie($unsecured, $unsec));
        $this->assertTrue($this->response->hasCookie($secured, $sec));

        delete_cookie($unsecured);
        delete_cookie($secured);
    }

    public function testDeleteCookie(): void
    {
        $this->response->setCookie($this->name, $this->value, $this->expire);

        delete_cookie($this->name);

        $cookie = $this->response->getCookie($this->name);

        // The cookie is set to be cleared when the request is sent....
        $this->assertSame('', $cookie->getValue());
        $this->assertSame(0, $cookie->getExpiresTimestamp());
    }

    public function testGetCookie(): void
    {
        $_COOKIE['TEST'] = '5';

        $this->assertSame('5', get_cookie('TEST'));
    }

    public function testGetCookieDefaultPrefix(): void
    {
        $_COOKIE['prefix_TEST'] = '5';

        $config         = new CookieConfig();
        $config->prefix = 'prefix_';
        Factories::injectMock('config', CookieConfig::class, $config);

        $this->assertSame('5', get_cookie('TEST', false, ''));
    }

    public function testGetCookiePrefix(): void
    {
        $_COOKIE['abc_TEST'] = '5';

        $config         = new CookieConfig();
        $config->prefix = 'prefix_';
        Factories::injectMock('config', CookieConfig::class, $config);

        $this->assertSame('5', get_cookie('TEST', false, 'abc_'));
    }

    public function testGetCookieNoPrefix(): void
    {
        $_COOKIE['abc_TEST'] = '5';

        $config         = new CookieConfig();
        $config->prefix = 'prefix_';
        Factories::injectMock('config', CookieConfig::class, $config);

        $this->assertSame('5', get_cookie('abc_TEST', false, null));
    }

    public function testDeleteCookieAfterLastSet(): void
    {
        delete_cookie($this->name);

        $cookie = $this->response->getCookie($this->name);
        // The cookie is set to be cleared when the request is sent....
        $this->assertSame('', $cookie->getValue());
    }

    public function testSameSiteDefault(): void
    {
        $cookieAttr = [
            'name'   => $this->name,
            'value'  => $this->value,
            'expire' => $this->expire,
        ];

        set_cookie($cookieAttr);

        $this->assertTrue($this->response->hasCookie($this->name));
        $theCookie = $this->response->getCookie($this->name);
        $this->assertSame('Lax', $theCookie->getSameSite());

        delete_cookie($this->name);
    }

    public function testSameSiteInvalid(): void
    {
        $cookieAttr = [
            'name'     => $this->name,
            'value'    => $this->value,
            'expire'   => $this->expire,
            'samesite' => 'Invalid',
        ];

        $this->expectException(CookieException::class);
        $this->expectExceptionMessage(lang('Cookie.invalidSameSite', ['Invalid']));

        set_cookie($cookieAttr);
    }

    public function testSameSiteParamArray(): void
    {
        $cookieAttr = [
            'name'     => $this->name,
            'value'    => $this->value,
            'expire'   => $this->expire,
            'samesite' => 'Strict',
        ];

        set_cookie($cookieAttr);

        $this->assertTrue($this->response->hasCookie($this->name));
        $theCookie = $this->response->getCookie($this->name);
        $this->assertSame('Strict', $theCookie->getSameSite());

        delete_cookie($this->name);
    }

    public function testSameSiteParam(): void
    {
        set_cookie($this->name, $this->value, $this->expire, '', '', '', '', '', 'Strict');

        $this->assertTrue($this->response->hasCookie($this->name));
        $theCookie = $this->response->getCookie($this->name);
        $this->assertSame('Strict', $theCookie->getSameSite());

        delete_cookie($this->name);
    }
}
