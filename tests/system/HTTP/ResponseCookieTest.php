<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use CodeIgniter\Config\Factories;
use CodeIgniter\Cookie\Cookie;
use CodeIgniter\Cookie\CookieStore;
use CodeIgniter\Cookie\Exceptions\CookieException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Config\Cookie as CookieConfig;

/**
 * @internal
 *
 * @group Others
 */
final class ResponseCookieTest extends CIUnitTestCase
{
    private array $defaults;

    protected function setUp(): void
    {
        parent::setUp();
        $this->defaults = Cookie::setDefaults();
    }

    protected function tearDown(): void
    {
        Cookie::setDefaults($this->defaults);
    }

    public function testCookiePrefixed(): void
    {
        $config         = config('Cookie');
        $config->prefix = 'mine';
        $response       = new Response(new App());
        $response->setCookie('foo', 'bar');

        $this->assertInstanceOf(Cookie::class, $response->getCookie('foo'));
        $this->assertTrue($response->hasCookie('foo'));
        $this->assertTrue($response->hasCookie('foo', 'bar'));
        $this->assertTrue($response->hasCookie('foo', 'bar', 'mine'));
        $this->assertTrue($response->hasCookie('foo', null, 'mine'));
        $this->assertFalse($response->hasCookie('foo', null, 'yours'));
    }

    public function testCookiesAll(): void
    {
        $response = new Response(new App());
        $response->setCookie('foo', 'bar');
        $response->setCookie('bee', 'bop');

        $this->assertCount(2, $response->getCookies());
        $this->assertTrue($response->hasCookie('foo'));
        $this->assertTrue($response->hasCookie('bee'));
    }

    public function testSetCookieObject(): void
    {
        $cookie   = new Cookie('foo', 'bar');
        $response = new Response(new App());

        $response->setCookie($cookie);

        $this->assertCount(1, $response->getCookies());
        $this->assertTrue($response->hasCookie('foo'));
    }

    public function testCookieGet(): void
    {
        $response = new Response(new App());
        $response->setCookie('foo', 'bar');
        $response->setCookie('bee', 'bop');

        $this->assertCount(2, $response->getCookie());
        $this->assertNull($response->getCookie('bogus'));
    }

    public function testCookieDomain(): void
    {
        $response = new Response(new App());

        $response->setCookie('foo', 'bar');
        $cookie = $response->getCookie('foo');
        $this->assertSame('', $cookie->getDomain());

        $response->setCookie(['name' => 'bee', 'value' => 'bop', 'domain' => 'somewhere.com']);
        $cookie = $response->getCookie('bee');
        $this->assertSame('somewhere.com', $cookie->getDomain());

        $config         = config('Cookie');
        $config->domain = 'mine.com';
        $response       = new Response(new App());
        $response->setCookie('alu', 'la');
        $cookie = $response->getCookie('alu');
        $this->assertSame('mine.com', $cookie->getDomain());
    }

    public function testCookiePath(): void
    {
        $response = new Response(new App());

        $response->setCookie('foo', 'bar');
        $cookie = $response->getCookie('foo');
        $this->assertSame('/', $cookie->getPath());

        $response->setCookie(['name' => 'bee', 'value' => 'bop', 'path' => '/tmp/here']);
        $cookie = $response->getCookie('bee');
        $this->assertSame('/tmp/here', $cookie->getPath());
    }

    public function testCookieSecure(): void
    {
        $response = new Response(new App());

        $response->setCookie('foo', 'bar');
        $cookie = $response->getCookie('foo');
        $this->assertFalse($cookie->isSecure());

        $response->setCookie(['name' => 'bee', 'value' => 'bop', 'secure' => true]);
        $cookie = $response->getCookie('bee');
        $this->assertTrue($cookie->isSecure());
    }

    public function testCookieHTTPOnly(): void
    {
        $response = new Response(new App());

        $response->setCookie('foo', 'bar');
        $cookie = $response->getCookie('foo');
        $this->assertTrue($cookie->isHTTPOnly());

        $response->setCookie(['name' => 'bee', 'value' => 'bop', 'httponly' => false]);
        $cookie = $response->getCookie('bee');
        $this->assertFalse($cookie->isHTTPOnly());
    }

    public function testCookieExpiry(): void
    {
        $response = new Response(new App());

        $response->setCookie('foo', 'bar');
        $cookie = $response->getCookie('foo');
        $this->assertTrue($cookie->isExpired());

        $response = new Response(new App());
        $response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => 1000]);
        $cookie = $response->getCookie('bee');
        $this->assertFalse($cookie->isExpired());

        $response = new Response(new App());
        $response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => -1000]);
        $cookie = $response->getCookie('bee');
        $this->assertSame(0, $cookie->getExpiresTimestamp());
    }

    public function testCookieDelete(): void
    {
        $response = new Response(new App());

        // foo is already expired, bee will stick around
        $response->setCookie('foo', 'bar');
        $response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => 1000]);
        $cookie = $response->getCookie('bee');
        $this->assertFalse($cookie->isExpired());

        // delete cookie manually
        $response = new Response(new App());
        $response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => '']);
        $cookie = $response->getCookie('bee');
        $this->assertTrue($cookie->isExpired(), $cookie->getExpiresTimestamp() . ' should be less than ' . time());

        // delete with no effect
        $response = new Response(new App());
        $response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => 1000]);
        $response->deleteCookie();
        $cookie = $response->getCookie('bee');
        $this->assertFalse($cookie->isExpired());

        // delete cookie for real
        $response = new Response(new App());
        $response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => 1000]);
        $response->deleteCookie('bee');
        $cookie = $response->getCookie('bee');
        $this->assertTrue($cookie->isExpired(), $cookie->getExpiresTimestamp() . ' should be less than ' . time());

        $config = config('Cookie');
        // delete cookie for real, with prefix
        $config->prefix = 'mine';
        $response       = new Response(new App());
        $response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => 1000]);
        $response->deleteCookie('bee');
        $cookie = $response->getCookie('bee');
        $this->assertSame($cookie->getExpiresTimestamp(), 0);

        // delete cookie with wrong prefix?
        $config->prefix = 'mine';
        $response       = new Response(new App());
        $response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => 1000]);
        $response->deleteCookie('bee', '', '', 'wrong');
        $cookie = $response->getCookie('bee');
        $this->assertFalse($cookie->isExpired(), $cookie->getExpiresTimestamp() . ' should be less than ' . time());
        $response->deleteCookie('bee', '', '', 'mine');
        $cookie = $response->getCookie('bee');
        $this->assertSame($cookie->getExpiresTimestamp(), 0);

        // delete cookie with wrong domain?
        $config->domain = '.mine.com';
        $response       = new Response(new App());
        $response->setCookie(['name' => 'bees', 'value' => 'bop', 'expire' => 1000]);
        $response->deleteCookie('bees', 'wrong', '', '');
        $cookie = $response->getCookie('bees');
        $this->assertFalse($cookie->isExpired(), $cookie->getExpiresTimestamp() . ' should be less than ' . time());
        $response->deleteCookie('bees', '.mine.com', '', '');
        $cookie = $response->getCookie('bees');
        $this->assertSame($cookie->getExpiresTimestamp(), 0);
    }

    public function testCookieDefaultSetSameSite(): void
    {
        $response = new Response(new App());
        $response->setCookie([
            'name'  => 'bar',
            'value' => 'foo',
        ]);

        $allCookies = $response->getCookies();
        $this->assertCount(1, $allCookies);
        $this->assertInstanceOf(Cookie::class, $allCookies['bar;/;']);
        $this->assertSame('Lax', $allCookies['bar;/;']->getSameSite());
    }

    public function testCookieStrictSetSameSite(): void
    {
        $config           = config('Cookie');
        $config->samesite = 'Strict';
        $response         = new Response(new App());
        $response->setCookie([
            'name'  => 'bar',
            'value' => 'foo',
        ]);

        $allCookies = $response->getCookies();
        $this->assertCount(1, $allCookies);
        $this->assertInstanceOf(Cookie::class, $allCookies['bar;/;']);
        $this->assertSame('Strict', $allCookies['bar;/;']->getSameSite());
    }

    public function testCookieBlankSetSameSite(): void
    {
        /** @var CookieConfig $config */
        $config           = config('Cookie');
        $config->samesite = '';
        $response         = new Response(new App());
        $response->setCookie([
            'name'  => 'bar',
            'value' => 'foo',
        ]);

        $allCookies = $response->getCookies();
        $this->assertCount(1, $allCookies);
        $this->assertInstanceOf(Cookie::class, $allCookies['bar;/;']);
        $this->assertSame('', $allCookies['bar;/;']->getSameSite());
    }

    public function testCookieWithoutSameSite(): void
    {
        $config = new CookieConfig();
        unset($config->samesite);
        $response = new Response(new App());
        $response->setCookie([
            'name'  => 'bar',
            'value' => 'foo',
        ]);

        $allCookies = $response->getCookies();
        $this->assertCount(1, $allCookies);
        $this->assertInstanceOf(Cookie::class, $allCookies['bar;/;']);
        $this->assertSame('Lax', $allCookies['bar;/;']->getSameSite());
    }

    public function testCookieStrictSameSite(): void
    {
        $response = new Response(new App());
        $response->setCookie([
            'name'     => 'bar',
            'value'    => 'foo',
            'samesite' => 'Strict',
        ]);

        $allCookies = $response->getCookies();
        $this->assertCount(1, $allCookies);
        $this->assertInstanceOf(Cookie::class, $allCookies['bar;/;']);
        $this->assertSame('Strict', $allCookies['bar;/;']->getSameSite());
    }

    public function testCookieInvalidSameSite(): void
    {
        $response = new Response(new App());

        $this->expectException(CookieException::class);
        $this->expectExceptionMessage(lang('Cookie.invalidSameSite', ['Invalid']));

        $response->setCookie([
            'name'     => 'bar',
            'value'    => 'foo',
            'samesite' => 'Invalid',
        ]);
    }

    public function testSetCookieConfigCookieIsUsed(): void
    {
        /** @var CookieConfig $config */
        $config           = config('Cookie');
        $config->secure   = true;
        $config->httponly = true;
        $config->samesite = 'None';
        Factories::injectMock('config', 'Cookie', $config);

        $cookieAttr = [
            'name'   => 'bar',
            'value'  => 'foo',
            'expire' => 9999,
        ];
        $response = new Response(new App());
        $response->setCookie($cookieAttr);

        $cookie  = $response->getCookie('bar');
        $options = $cookie->getOptions();
        $this->assertTrue($options['secure']);
        $this->assertTrue($options['httponly']);
        $this->assertSame('None', $options['samesite']);
    }

    public function testGetCookieStore(): void
    {
        $response = new Response(new App());
        $this->assertInstanceOf(CookieStore::class, $response->getCookieStore());
    }
}
