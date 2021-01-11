<?php

namespace CodeIgniter\HTTP;

use CodeIgniter\Cookie\Collection\Cookie;
use CodeIgniter\Cookie\Exceptions\CookieException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Config\Services;

class ResponseCookieTest extends CIUnitTestCase
{
	protected $cookie;

	protected function setUp(): void
	{
		parent::setUp();
		$this->server = $_SERVER;
		$this->cookie = Services::cookie();
	}

	public function tearDown(): void
	{
		$_SERVER = $this->server;
		$this->cookie->clear();
	}

	public function testCookiePrefixed()
	{
		$this->cookie->setPrefix('mine');

		$response = new Response(new App());
		$response->setCookie('foo', 'bar');

		$this->assertTrue(is_array($response->getCookie('foo')));
		$this->assertTrue($response->hasCookie('foo', 'bar', 'mine'));
		$this->assertFalse($response->hasCookie('foo', null, 'yours'));
	}

	public function testCookiesAll()
	{
		$response = new Response(new App());
		$response->setCookie('foo', 'bar');
		$response->setCookie('bee', 'bop');

		$this->assertEquals(2, count($response->getCookie()));
		$this->assertTrue($response->hasCookie('foo'));
		$this->assertTrue($response->hasCookie('bee'));
	}

	public function testCookieGet()
	{
		$response = new Response(new App());
		$response->setCookie('foo', 'bar');
		$response->setCookie('bee', 'bop');

		$this->assertEquals(2, count($response->getCookie()));
		$this->assertEquals(null, $response->getCookie('bogus'));
	}

	public function testCookieDomain()
	{
		$response = new Response(new App());

		$response->setCookie('foo', 'bar');
		$cookie = $response->getCookie('foo');
		$this->assertEquals('', $cookie['domain']);

		$response->setCookie([
			'name'   => 'bee',
			'value'  => 'bop',
			'domain' => 'somewhere.com',
		]);
		$cookie = $response->getCookie('bee');
		$this->assertEquals('somewhere.com', $cookie['domain']);

		$this->cookie->setDomain('mine.com');
		$response = new Response(new App());
		$response->setCookie('alu', 'la');
		$cookie = $response->getCookie('alu');
		$this->assertEquals('mine.com', $cookie['domain']);
	}

	public function testCookiePath()
	{
		$response = new Response(new App());

		$response->setCookie('foo', 'bar');
		$cookie = $response->getCookie('foo');
		$this->assertEquals('/', $cookie['path']);

		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'path' => '/tmp/here']);
		$cookie = $response->getCookie('bee');
		$this->assertEquals('/tmp/here', $cookie['path']);

		$this->cookie->setPath('/tmp/there');
		$response = new Response(new App());
		$response->setCookie('alu', 'la');
		$cookie = $response->getCookie('alu');
		$this->assertEquals('/tmp/there', $cookie['path']);
	}

	public function testCookieSecure()
	{
		$response = new Response(new App());

		$response->setCookie('foo', 'bar');
		$cookie = $response->getCookie('foo');
		$this->assertEquals(false, $cookie['secure']);

		$response->setCookie([
			'name' => 'bee',
			'value' => 'bop',
			'secure' => true,
		]);
		$cookie = $response->getCookie('bee');
		$this->assertEquals(true, $cookie['secure']);

		$this->cookie->setSecure(true);
		$response = new Response(new App());
		$response->setCookie('alu', 'la');
		$cookie = $response->getCookie('alu');
		$this->assertEquals(true, $cookie['secure']);
	}

	public function testCookieHTTPOnly()
	{
		$config   = new App();
		$response = new Response(new App());

		$response->setCookie('foo', 'bar');
		$cookie = $response->getCookie('foo');
		$this->assertEquals(false, $cookie['httponly']);

		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'httponly' => true]);
		$cookie = $response->getCookie('bee');
		$this->assertEquals(true, $cookie['httponly']);

		$this->cookie->setHTTPOnly(true);
		$response = new Response(new App());
		$response->setCookie('alu', 'la');
		$cookie = $response->getCookie('alu');
		$this->assertEquals(true, $cookie['httponly']);
	}

	public function testCookieExpiry()
	{
		$response = new Response(new App());

		$response->setCookie('foo', 'bar');
		$cookie = $response->getCookie('foo');
		$this->assertTrue($cookie['expires'] < time());

		$response = new Response(new App());
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expires' => 1000]);
		$cookie = $response->getCookie('bee');
		$this->assertTrue($cookie['expires'] > time());

		$response = new Response(new App());
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expires' => 0]);
		$cookie = $response->getCookie('bee');
		$this->assertTrue($cookie['expires'] > time());
	}

	public function testCookieDelete()
	{
		$response = new Response(new App());

		// foo is already expired, bee will stick around
		$response->setCookie('foo', 'bar');
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expires' => 1000]);
		$cookie = $response->getCookie('bee');
		$this->assertFalse($cookie['expires'] <= time());

		// delete cookie manually
		$response = new Response(new App());
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expires' => 0]);
		$cookie = $response->getCookie('bee');
		$this->assertTrue($cookie['expires'] <= time(), $cookie['expires'] . ' should be less than ' . time());

		// delete with no effect
		$response = new Response(new App());
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expires' => 1000]);
		$response->deleteCookie();
		$cookie = $response->getCookie('bee');
		$this->assertFalse($cookie['expires'] < time());

		// delete cookie for real
		$response = new Response(new App());
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expires' => 1000]);
		$response->deleteCookie('bee');
		$cookie = $response->getCookie('bee');
		$this->assertTrue($cookie['expires'] <= time(), $cookie['expires'] . ' should be less than ' . time());

		// delete cookie for real, with prefix
		$this->cookie->setPrefix('mine');
		$response = new Response(new App());
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expires' => 1000]);
		$response->deleteCookie('bee');
		$cookie = $response->getCookie('bee');
		$this->assertEquals($cookie['expires'], '', 'Expires should be an empty string');

		// delete cookie with wrong prefix?
		$this->cookie->setPrefix('mine');
		$response = new Response(new App());
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expires' => 1000]);
		$response->deleteCookie('bee', '', '', 'wrong');
		$cookie = $response->getCookie('bee');
		$this->assertFalse($cookie['expires'] <= time(), $cookie['expires'] . ' should be less than ' . time());
		$response->deleteCookie('bee', '', '', 'mine');
		$cookie = $response->getCookie('bee');
		$this->assertEquals($cookie['expires'], '', 'Expires should be an empty string');

		// delete cookie with wrong domain?
		$this->cookie->setDomain('.mine.com');
		$response = new Response(new App());
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expires' => 1000]);
		$response->deleteCookie('bee', 'wrong', '', '');
		$cookie = $response->getCookie('bee');
		$this->assertFalse($cookie['expires'] <= time(), $cookie['expires'] . ' should be less than ' . time());
		$response->deleteCookie('bee', '.mine.com', '', '');
		$cookie = $response->getCookie('bee');
		$this->assertEquals($cookie['expires'], '', 'Expires should be an empty string');

		// delete cookie with wrong path?
		$this->cookie->setPath('/whoknowswhere');
		$response = new Response(new App());
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expires' => 1000]);
		$response->deleteCookie('bee', '', '/wrong', '');
		$cookie = $response->getCookie('bee');
		$this->assertFalse($cookie['expires'] <= time(), $cookie['expires'] . ' should be less than ' . time());
		$response->deleteCookie('bee', '', '/whoknowswhere', '');
		$cookie = $response->getCookie('bee');
		$this->assertEquals($cookie['expires'], '', 'Expires should be an empty string');
	}

	public function testCookieDefaultSetSameSite()
	{
		$response = new Response(new App());
		$response->setCookie([
			'name'  => 'bar',
			'value' => 'foo',
		]);

		$allCookies = $response->getCookie();
		$this->assertEquals('Lax', $allCookies[0]['samesite']);
	}

	public function testCookieStrictSetSameSite()
	{
		$this->cookie->setSamesite('Strict');
		$response = new Response(new App());
		$response->setCookie([
			'name'  => 'bar',
			'value' => 'foo',
		]);

		$allCookies = $response->getCookie('bar');
		$this->assertEquals('Strict', $allCookies['samesite']);
	}

	public function testCookieStrictSameSite()
	{
		$config   = new App();
		$response = new Response(new App());
		$response->setCookie([
			'name'     => 'bar',
			'value'    => 'foo',
			'samesite' => 'Strict',
		]);

		$allCookies = $response->getCookie();
		$this->assertEquals(1, count($allCookies));
		$this->assertIsArray($allCookies[0]);
		$this->assertArrayHasKey('samesite', $allCookies[0]);
		$this->assertEquals('Strict', $allCookies[0]['samesite']);
	}
}
