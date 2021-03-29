<?php
namespace CodeIgniter\HTTP;

use CodeIgniter\HTTP\Cookie\Cookie;
use CodeIgniter\HTTP\Cookie\CookieStore;
use CodeIgniter\HTTP\Exceptions\CookieException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;

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
		$config               = new App();
		$config->cookiePrefix = 'mine';
		$response             = new Response($config);
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
		$config   = new App();
		$response = new Response($config);
		$response->setCookie('foo', 'bar');
		$response->setCookie('bee', 'bop');

		$this->assertCount(2, $response->getCookies());
		$this->assertTrue($response->hasCookie('foo'));
		$this->assertTrue($response->hasCookie('bee'));
	}

	public function testCookieGet()
	{
		$config   = new App();
		$response = new Response($config);
		$response->setCookie('foo', 'bar');
		$response->setCookie('bee', 'bop');

		$this->assertCount(2, $response->getCookie());
		$this->assertNull($response->getCookie('bogus'));
	}

	public function testCookieDomain()
	{
		$config   = new App();
		$response = new Response($config);

		$response->setCookie('foo', 'bar');
		$cookie = $response->getCookie('foo');
		$this->assertSame('', $cookie->getDomain());

		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'domain' => 'somewhere.com']);
		$cookie = $response->getCookie('bee');
		$this->assertSame('somewhere.com', $cookie->getDomain());

		$config->cookieDomain = 'mine.com';
		$response             = new Response($config);
		$response->setCookie('alu', 'la');
		$cookie = $response->getCookie('alu');
		$this->assertSame('mine.com', $cookie->getDomain());
	}

	public function testCookiePath()
	{
		$config   = new App();
		$response = new Response($config);

		$response->setCookie('foo', 'bar');
		$cookie = $response->getCookie('foo');
		$this->assertSame('/', $cookie->getPath());

		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'path' => '/tmp/here']);
		$cookie = $response->getCookie('bee');
		$this->assertSame('/tmp/here', $cookie->getPath());
	}

	public function testCookieSecure()
	{
		$config   = new App();
		$response = new Response($config);

		$response->setCookie('foo', 'bar');
		$cookie = $response->getCookie('foo');
		$this->assertFalse($cookie->isSecure());

		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'secure' => true]);
		$cookie = $response->getCookie('bee');
		$this->assertTrue($cookie->isSecure());
	}

	public function testCookieHTTPOnly()
	{
		$config   = new App();
		$response = new Response($config);

		$response->setCookie('foo', 'bar');
		$cookie = $response->getCookie('foo');
		$this->assertTrue($cookie->isHttpOnly());

		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'httponly' => false]);
		$cookie = $response->getCookie('bee');
		$this->assertTrue($cookie->isHttpOnly());
	}

	public function testCookieExpiry()
	{
		$config   = new App();
		$response = new Response($config);

		$response->setCookie('foo', 'bar');
		$cookie = $response->getCookie('foo');
		$this->assertTrue($cookie->isExpired());

		$response = new Response($config);
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => 1000]);
		$cookie = $response->getCookie('bee');
		$this->assertFalse($cookie->isExpired());

		$response = new Response($config);
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => -1000]);
		$cookie = $response->getCookie('bee');
		$this->assertSame(0, $cookie->getExpiresTimestamp());
	}

	public function testCookieDelete()
	{
		$config   = new App();
		$response = new Response($config);

		// foo is already expired, bee will stick around
		$response->setCookie('foo', 'bar');
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => 1000]);
		$cookie = $response->getCookie('bee');
		$this->assertFalse($cookie->isExpired());

		// delete cookie manually
		$response = new Response($config);
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => '']);
		$cookie = $response->getCookie('bee');
		$this->assertTrue($cookie->isExpired(), $cookie->getExpiresTimestamp() . ' should be less than ' . time());

		// delete with no effect
		$response = new Response($config);
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => 1000]);
		$response->deleteCookie();
		$cookie = $response->getCookie('bee');
		$this->assertFalse($cookie->isExpired());

		// delete cookie for real
		$response = new Response($config);
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => 1000]);
		$response->deleteCookie('bee');
		$cookie = $response->getCookie('bee');
		$this->assertTrue($cookie->isExpired(), $cookie->getExpiresTimestamp() . ' should be less than ' . time());

		// delete cookie for real, with prefix
		$config->cookiePrefix = 'mine';
		$response             = new Response($config);
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => 1000]);
		$response->deleteCookie('bee');
		$cookie = $response->getCookie('bee');
		$this->assertSame($cookie->getExpiresTimestamp(), 0);

		// delete cookie with wrong prefix?
		$config->cookiePrefix = 'mine';
		$response             = new Response($config);
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => 1000]);
		$response->deleteCookie('bee', '', '', 'wrong');
		$cookie = $response->getCookie('bee');
		$this->assertFalse($cookie->isExpired(), $cookie->getExpiresTimestamp() . ' should be less than ' . time());
		$response->deleteCookie('bee', '', '', 'mine');
		$cookie = $response->getCookie('bee');
		$this->assertSame($cookie->getExpiresTimestamp(), 0);

		// delete cookie with wrong domain?
		$config->cookieDomain = '.mine.com';
		$response             = new Response($config);
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
		$config   = new App();
		$response = new Response($config);
		$response->setCookie([
			'name'  => 'bar',
			'value' => 'foo',
		]);

		$allCookies = $response->getCookies();
		$this->assertCount(1, $allCookies);
		$this->assertInstanceOf(Cookie::class, $allCookies['bar;;/']);
		$this->assertSame('Lax', $allCookies['bar;;/']->getSameSite());
	}

	public function testCookieStrictSetSameSite()
	{
		$config                 = new App();
		$config->cookieSameSite = 'Strict';
		$response               = new Response($config);
		$response->setCookie([
			'name'  => 'bar',
			'value' => 'foo',
		]);

		$allCookies = $response->getCookies();
		$this->assertCount(1, $allCookies);
		$this->assertInstanceOf(Cookie::class, $allCookies['bar;;/']);
		$this->assertSame('Strict', $allCookies['bar;;/']->getSameSite());
	}

	public function testCookieBlankSetSameSite()
	{
		$config                 = new App();
		$config->cookieSameSite = '';
		$response               = new Response($config);
		$response->setCookie([
			'name'  => 'bar',
			'value' => 'foo',
		]);

		$allCookies = $response->getCookies();
		$this->assertCount(1, $allCookies);
		$this->assertInstanceOf(Cookie::class, $allCookies['bar;;/']);
		$this->assertSame('', $allCookies['bar;;/']->getSameSite());
	}

	public function testCookieWithoutSameSite()
	{
		$config = new App();
		unset($config->cookieSameSite);
		$response = new Response($config);
		$response->setCookie([
			'name'  => 'bar',
			'value' => 'foo',
		]);

		$allCookies = $response->getCookies();
		$this->assertCount(1, $allCookies);
		$this->assertInstanceOf(Cookie::class, $allCookies['bar;;/']);
		$this->assertSame('Lax', $allCookies['bar;;/']->getSameSite());
	}

	public function testCookieStrictSameSite()
	{
		$config   = new App();
		$response = new Response($config);
		$response->setCookie([
			'name'     => 'bar',
			'value'    => 'foo',
			'samesite' => 'Strict',
		]);

		$allCookies = $response->getCookies();
		$this->assertCount(1, $allCookies);
		$this->assertInstanceOf(Cookie::class, $allCookies['bar;;/']);
		$this->assertSame('Strict', $allCookies['bar;;/']->getSameSite());
	}

	public function testCookieInvalidSameSite()
	{
		$config   = new App();
		$response = new Response($config);

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
