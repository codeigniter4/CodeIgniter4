<?php
namespace CodeIgniter\HTTP;

use CodeIgniter\HTTP\Exceptions\HTTPException;
use Config\App;

class ResponseCookieTest extends \CodeIgniter\Test\CIUnitTestCase
{

	protected function setUp(): void
	{
		parent::setUp();
		$this->server = $_SERVER;
	}

	public function tearDown(): void
	{
		$_SERVER = $this->server;
	}

	public function testCookiePrefixed()
	{
		$config               = new App();
		$config->cookiePrefix = 'mine';
		$response             = new Response($config);
		$response->setCookie('foo', 'bar');

		$this->assertTrue(is_array($response->getCookie('foo')));
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

		$allCookies = $response->getCookie();
		$this->assertEquals(2, count($allCookies));
		$this->assertTrue($response->hasCookie('foo'));
		$this->assertTrue($response->hasCookie('bee'));
	}

	public function testCookieGet()
	{
		$config   = new App();
		$response = new Response($config);
		$response->setCookie('foo', 'bar');
		$response->setCookie('bee', 'bop');

		$allCookies = $response->getCookie();
		$this->assertEquals(2, count($allCookies));
		$this->assertEquals(null, $response->getCookie('bogus'));
	}

	public function testCookieDomain()
	{
		$config   = new App();
		$response = new Response($config);

		$response->setCookie('foo', 'bar');
		$cookie = $response->getCookie('foo');
		$this->assertEquals('', $cookie['domain']);

		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'domain' => 'somewhere.com']);
		$cookie = $response->getCookie('bee');
		$this->assertEquals('somewhere.com', $cookie['domain']);

		$config->cookieDomain = 'mine.com';
		$response             = new Response($config);
		$response->setCookie('alu', 'la');
		$cookie = $response->getCookie('alu');
		$this->assertEquals('mine.com', $cookie['domain']);
	}

	public function testCookiePath()
	{
		$config   = new App();
		$response = new Response($config);

		$response->setCookie('foo', 'bar');
		$cookie = $response->getCookie('foo');
		$this->assertEquals('/', $cookie['path']);

		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'path' => '/tmp/here']);
		$cookie = $response->getCookie('bee');
		$this->assertEquals('/tmp/here', $cookie['path']);

		$config->cookiePath = '/tmp/there';
		$response           = new Response($config);
		$response->setCookie('alu', 'la');
		$cookie = $response->getCookie('alu');
		$this->assertEquals('/tmp/there', $cookie['path']);
	}

	public function testCookieSecure()
	{
		$config   = new App();
		$response = new Response($config);

		$response->setCookie('foo', 'bar');
		$cookie = $response->getCookie('foo');
		$this->assertEquals(false, $cookie['secure']);

		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'secure' => true]);
		$cookie = $response->getCookie('bee');
		$this->assertEquals(true, $cookie['secure']);

		$config->cookieSecure = true;
		$response             = new Response($config);
		$response->setCookie('alu', 'la');
		$cookie = $response->getCookie('alu');
		$this->assertEquals(true, $cookie['secure']);
	}

	public function testCookieHTTPOnly()
	{
		$config   = new App();
		$response = new Response($config);

		$response->setCookie('foo', 'bar');
		$cookie = $response->getCookie('foo');
		$this->assertEquals(false, $cookie['httponly']);

		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'httponly' => true]);
		$cookie = $response->getCookie('bee');
		$this->assertEquals(true, $cookie['httponly']);

		$config->cookieHTTPOnly = true;
		$response               = new Response($config);
		$response->setCookie('alu', 'la');
		$cookie = $response->getCookie('alu');
		$this->assertEquals(true, $cookie['httponly']);
	}

	public function testCookieExpiry()
	{
		$config   = new App();
		$response = new Response($config);

		$response->setCookie('foo', 'bar');
		$cookie = $response->getCookie('foo');
		$this->assertTrue($cookie['expires'] < time());

		$response = new Response($config);
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => 1000]);
		$cookie = $response->getCookie('bee');
		$this->assertFalse($cookie['expires'] < time());

		$response = new Response($config);
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => 'oops']);
		$cookie = $response->getCookie('bee');
		$this->assertTrue($cookie['expires'] < time());

		$response = new Response($config);
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => -1000]);
		$cookie = $response->getCookie('bee');
		$this->assertEquals(0, $cookie['expires']);
	}

	public function testCookieDelete()
	{
		$config   = new App();
		$response = new Response($config);

		// foo is already expired, bee will stick around
		$response->setCookie('foo', 'bar');
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => 1000]);
		$cookie = $response->getCookie('bee');
		$this->assertFalse($cookie['expires'] <= time());

		// delete cookie manually
		$response = new Response($config);
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => '']);
		$cookie = $response->getCookie('bee');
		$this->assertTrue($cookie['expires'] <= time(), $cookie['expires'] . ' should be less than ' . time());

		// delete with no effect
		$response = new Response($config);
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => 1000]);
		$response->deleteCookie();
		$cookie = $response->getCookie('bee');
		$this->assertFalse($cookie['expires'] < time());

		// delete cookie for real
		$response = new Response($config);
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => 1000]);
		$response->deleteCookie('bee');
		$cookie = $response->getCookie('bee');
		$this->assertTrue($cookie['expires'] <= time(), $cookie['expires'] . ' should be less than ' . time());

		// delete cookie for real, with prefix
		$config->cookiePrefix = 'mine';
		$response             = new Response($config);
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => 1000]);
		$response->deleteCookie('bee');
		$cookie = $response->getCookie('bee');
		$this->assertEquals($cookie['expires'], '', 'Expires should be an empty string');

		// delete cookie with wrong prefix?
		$config->cookiePrefix = 'mine';
		$response             = new Response($config);
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => 1000]);
		$response->deleteCookie('bee', '', '', 'wrong');
		$cookie = $response->getCookie('bee');
		$this->assertFalse($cookie['expires'] <= time(), $cookie['expires'] . ' should be less than ' . time());
		$response->deleteCookie('bee', '', '', 'mine');
		$cookie = $response->getCookie('bee');
		$this->assertEquals($cookie['expires'], '', 'Expires should be an empty string');

		// delete cookie with wrong domain?
		$config->cookieDomain = '.mine.com';
		$response             = new Response($config);
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => 1000]);
		$response->deleteCookie('bee', 'wrong', '', '');
		$cookie = $response->getCookie('bee');
		$this->assertFalse($cookie['expires'] <= time(), $cookie['expires'] . ' should be less than ' . time());
		$response->deleteCookie('bee', '.mine.com', '', '');
		$cookie = $response->getCookie('bee');
		$this->assertEquals($cookie['expires'], '', 'Expires should be an empty string');

		// delete cookie with wrong path?
		$config->cookiePath = '/whoknowswhere';
		$response           = new Response($config);
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => 1000]);
		$response->deleteCookie('bee', '', '/wrong', '');
		$cookie = $response->getCookie('bee');
		$this->assertFalse($cookie['expires'] <= time(), $cookie['expires'] . ' should be less than ' . time());
		$response->deleteCookie('bee', '', '/whoknowswhere', '');
		$cookie = $response->getCookie('bee');
		$this->assertEquals($cookie['expires'], '', 'Expires should be an empty string');
	}

	public function testCookieDefaultSetSameSite()
	{
		$config   = new App();
		$response = new Response($config);
		$response->setCookie([
			'name'  => 'bar',
			'value' => 'foo',
		]);

		$allCookies = $response->getCookie();
		$this->assertEquals(1, count($allCookies));
		$this->assertIsArray($allCookies[0]);
		$this->assertArrayHasKey('samesite', $allCookies[0]);
		$this->assertEquals('Lax', $allCookies[0]['samesite']);
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

		$allCookies = $response->getCookie();
		$this->assertEquals(1, count($allCookies));
		$this->assertIsArray($allCookies[0]);
		$this->assertArrayHasKey('samesite', $allCookies[0]);
		$this->assertEquals('Strict', $allCookies[0]['samesite']);
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

		$allCookies = $response->getCookie();
		$this->assertEquals(1, count($allCookies));
		$this->assertIsArray($allCookies[0]);
		$this->assertArrayNotHasKey('samesite', $allCookies[0]);
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

		$allCookies = $response->getCookie();
		$this->assertEquals(1, count($allCookies));
		$this->assertIsArray($allCookies[0]);
		$this->assertArrayHasKey('samesite', $allCookies[0]);
		$this->assertEquals('Strict', $allCookies[0]['samesite']);
	}

	public function testCookieInvalidSameSite()
	{
		$config   = new App();
		$response = new Response($config);

		$this->expectException(HTTPException::class);
		$this->expectExceptionMessage(lang('HTTP.invalidSameSiteSetting', ['Invalid']));

		$response->setCookie([
			'name'     => 'bar',
			'value'    => 'foo',
			'samesite' => 'Invalid',
		]);
	}

}
