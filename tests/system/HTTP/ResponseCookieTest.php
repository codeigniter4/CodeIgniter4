<?php

namespace CodeIgniter\HTTP;

use CodeIgniter\Cookie\Cookie;
use CodeIgniter\Cookie\CookieStore;
use CodeIgniter\Cookie\Exceptions\CookieException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Config\Cookie as CookieConfig;

/**
 * @internal
 */
final class ResponseCookieTest extends CIUnitTestCase
{
    /**
     * @var array
     */
    private $defaults;

    protected function setUp(): void
    {
        parent::setUp();
        $this->defaults = Cookie::setDefaults();
    }

    protected function tearDown(): void
    {
        Cookie::setDefaults($this->defaults);
    }

    public function testCookiePrefixed()
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

    public function testCookiesAll()
    {
        $response = new Response(new App());
        $response->setCookie('foo', 'bar');
        $response->setCookie('bee', 'bop');

        $this->assertCount(2, $response->getCookies());
        $this->assertTrue($response->hasCookie('foo'));
        $this->assertTrue($response->hasCookie('bee'));
    }

    public function testCookieGet()
    {
        $response = new Response(new App());
        $response->setCookie('foo', 'bar');
        $response->setCookie('bee', 'bop');

        $this->assertCount(2, $response->getCookie());
        $this->assertNull($response->getCookie('bogus'));
    }

    public function testCookieDomain()
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

    public function testCookiePath()
    {
        $response = new Response(new App());

        $response->setCookie('foo', 'bar');
        $cookie = $response->getCookie('foo');
        $this->assertSame('/', $cookie->getPath());

        $response->setCookie(['name' => 'bee', 'value' => 'bop', 'path' => '/tmp/here']);
        $cookie = $response->getCookie('bee');
        $this->assertSame('/tmp/here', $cookie->getPath());
    }

    public function testCookieSecure()
    {
        $response = new Response(new App());

        $response->setCookie('foo', 'bar');
        $cookie = $response->getCookie('foo');
        $this->assertFalse($cookie->isSecure());

        $response->setCookie(['name' => 'bee', 'value' => 'bop', 'secure' => true]);
        $cookie = $response->getCookie('bee');
        $this->assertTrue($cookie->isSecure());
    }

    public function testCookieHTTPOnly()
    {
        $response = new Response(new App());

        $response->setCookie('foo', 'bar');
        $cookie = $response->getCookie('foo');
        $this->assertFalse($cookie->isHTTPOnly());

        $response->setCookie(['name' => 'bee', 'value' => 'bop', 'httponly' => true]);
        $cookie = $response->getCookie('bee');
        $this->assertTrue($cookie->isHTTPOnly());
    }

    public function testCookieExpiry()
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

    public function testCookieDelete()
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

    public function testCookieDefaultSetSameSite()
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

    public function testCookieStrictSetSameSite()
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

    public function testCookieBlankSetSameSite()
    {
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

    public function testCookieWithoutSameSite()
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

    public function testCookieStrictSameSite()
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

    public function testCookieInvalidSameSite()
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

    public function testGetCookieStore()
    {
        $response = new Response(new App());
        $this->assertInstanceOf(CookieStore::class, $response->getCookieStore());
    }
}
