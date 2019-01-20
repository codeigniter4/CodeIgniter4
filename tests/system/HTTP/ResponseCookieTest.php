<?php
namespace CodeIgniter\HTTP;

use Config\App;

class ResponseCookieTest extends \CIUnitTestCase
{

	protected function setUp()
	{
		parent::setUp();
		$this->server = $_SERVER;
	}

	public function tearDown()
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
		$this->assertTrue($cookie['expires'] <= time(), $cookie['expires'] . ' should be less than ' . time());

		// delete cookie with wrong prefix?
		$config->cookiePrefix = 'mine';
		$response             = new Response($config);
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => 1000]);
		$response->deleteCookie('bee', '', '', 'wrong');
		$cookie = $response->getCookie('bee');
		$this->assertFalse($cookie['expires'] <= time(), $cookie['expires'] . ' should be less than ' . time());
		$response->deleteCookie('bee', '', '', 'mine');
		$cookie = $response->getCookie('bee');
		$this->assertTrue($cookie['expires'] <= time(), $cookie['expires'] . ' should be less than ' . time());

		// delete cookie with wrong domain?
		$config->cookieDomain = '.mine.com';
		$response             = new Response($config);
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => 1000]);
		$response->deleteCookie('bee', 'wrong', '', '');
		$cookie = $response->getCookie('bee');
		$this->assertFalse($cookie['expires'] <= time(), $cookie['expires'] . ' should be less than ' . time());
		$response->deleteCookie('bee', '.mine.com', '', '');
		$cookie = $response->getCookie('bee');
		$this->assertTrue($cookie['expires'] <= time(), $cookie['expires'] . ' should be less than ' . time());

		// delete cookie with wrong path?
		$config->cookiePath = '/whoknowswhere';
		$response           = new Response($config);
		$response->setCookie(['name' => 'bee', 'value' => 'bop', 'expire' => 1000]);
		$response->deleteCookie('bee', '', '/wrong', '');
		$cookie = $response->getCookie('bee');
		$this->assertFalse($cookie['expires'] <= time(), $cookie['expires'] . ' should be less than ' . time());
		$response->deleteCookie('bee', '', '/whoknowswhere', '');
		$cookie = $response->getCookie('bee');
		$this->assertTrue($cookie['expires'] <= time(), $cookie['expires'] . ' should be less than ' . time());
	}

}
