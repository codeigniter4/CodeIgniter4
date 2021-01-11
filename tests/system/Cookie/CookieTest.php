<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Cookie;

use CodeIgniter\Cookie\Collection\Cookie;
use CodeIgniter\HTTP\Request;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Cookie as CookieConfig;

class CookieTest extends CIUnitTestCase
{
	/**
	 * Cookie instance
	 * 
	 * @var Cookie
	 */
	protected $cookie;

	protected function setUp(): void
	{
		$this->cookie = new Cookie(new CookieConfig());
	}

	public function testGetCookieWhenArrayReturns()
	{
		$this->cookie->set('foo', 'bar');

		$this->assertIsArray($this->cookie->get('foo'));
		$this->assertTrue($this->cookie->has('foo'));
	}

	public function testGetCookieWhenNullReturns()
	{
		$this->cookie->set('foo', 'bar');

		$this->assertEquals(null, $this->cookie->get('baz'));
		$this->assertFalse($this->cookie->has('baz'));
	}

	public function testGetAllCookies()
	{
		$this->cookie->set('foo', 'bar')->set('baz', 'qux');

		$items = $this->cookie->get();

		$this->assertEquals(2, count($items));
		$this->assertTrue($this->cookie->has('foo'));
		$this->assertTrue($this->cookie->has('baz'));
		$this->assertFalse($this->cookie->has('quux'));
	}

	public function testCookieMatch()
	{
		$this->cookie->set('foo', 'bar');

		$this->assertEquals($this->cookie->has('foo'), $this->cookie->has('foo'));
	}

	public function testRemoveCookie()
	{
		$this->cookie->set([
			'name' => 'foo',
			'value' => 'bar',
			'expires' => 0,
		])->remove('foo');

		$this->assertFalse($this->cookie->has('foo'));
	}

	public function testRemoveCookieExpired()
	{
		$this->cookie->set('foo', 'bar')->set([
			'name' => 'foo',
			'value' => 'bar',
			'expires' => -1000,
		]);

		$item = $this->cookie->get('foo');
		
		$this->assertTrue($item['expires'] <= time());
	}

	public function testRemoveCookieWithRealPrefix()
	{
		$this->cookie->setPrefix('mk_')->set([
			'name' => 'foo',
			'value' => 'bar',
		])->remove('foo', 'mk_');

		$this->assertFalse($this->cookie->has('foo', 'mk_'));
	}

	public function testRemoveCookieWithWrongPrefix()
	{
		$this->cookie->setPrefix('mk_')->set([
			'name' => 'foo',
			'value' => 'bar',
		])->remove('foo', 'ma_');

		$this->assertTrue($this->cookie->has('foo', 'mk_'));
	}

	public function testCookieWithRealPrefix()
	{
		$this->cookie->set('foo', 'bar', 0, '', '', 'mk_');

		$this->assertTrue($this->cookie->has('foo', 'mk_'));
	}

	public function testCookieWithWrongPrefix()
	{
		$this->cookie->set('foo', 'bar', 0, '', '', 'mk_');

		$this->assertFalse($this->cookie->has('foo', '_mk'));
	}

	public function testCookieDefaultPrefix()
	{
		$this->cookie->set('foo', 'bar');

		$this->assertTrue($this->cookie->has('foo'));
	}

	public function testCookiePrefixViaArray()
	{
		$this->cookie->set([
			'name' => 'foo',
			'value' => 'bar',
			'prefix' => 'mk_',
		]);

		$item = $this->cookie->get('foo', 'mk_');

		$this->assertTrue($this->cookie->has('foo', 'mk_'));
		$this->assertEquals('mk_foo', $item['name']);
	}

	public function testCookiePrefixViaConfig()
	{
		$this->cookie->setPrefix('mk_')->set('foo', 'bar');

		$item = $this->cookie->get('foo', 'mk_');

		$this->assertTrue($this->cookie->has('foo', 'mk_'));
		$this->assertEquals('mk_foo', $item['name']);
	}

	public function testCookieDefaultPath()
	{
		$this->cookie->set('foo', 'bar');

		$item = $this->cookie->get('foo');

		$this->assertEquals('/', $item['path']);
	}

	public function testCookiePathViaArray()
	{
		$this->cookie->set([
			'name' => 'foo',
			'value' => 'bar',
			'path' => '/mk/baz',
		]);

		$item = $this->cookie->get('foo');

		$this->assertEquals('/mk/baz', $item['path']);
	}

	public function testCookiePathViaConfig()
	{
		$this->cookie->setPath('/mk/baz')->set('foo', 'bar');

		$item = $this->cookie->get('foo');
		
		$this->assertEquals('/mk/baz', $item['path']);
	}

	public function testCookieDefaultDomain()
	{
		$this->cookie->set('foo', 'bar');

		$item = $this->cookie->get('foo');

		$this->assertEquals('', $item['domain']);
	}

	public function testCookieDomainViaArray()
	{
		$this->cookie->set([
			'name' => 'foo',
			'value' => 'bar',
			'domain' => '.baz.mk',
		]);

		$item = $this->cookie->get('foo');

		$this->assertEquals('.baz.mk', $item['domain']);
	}

	public function testCookieDomainViaConfig()
	{
		$this->cookie->setDomain('.baz.mk')->set('foo', 'bar');

		$item = $this->cookie->get('foo');

		$this->assertEquals('.baz.mk', $item['domain']);
	}

	public function testCookieDefaultSecure()
	{
		$this->cookie->set('foo', 'bar');

		$item = $this->cookie->get('foo');

		$this->assertFalse($item['secure']);
		$this->assertEquals(false, $item['secure']);
	}

	public function testCookieSecureFalseViaArray()
	{
		$this->cookie->set([
			'name' => 'foo',
			'value' => 'bar',
			'secure' => false,
		]);

		$item = $this->cookie->get('foo');

		$this->assertFalse($item['secure']);
		$this->assertEquals(false, $item['secure']);
	}

	public function testCookieSecureTrueViaArray()
	{
		$this->cookie->set([
			'name' => 'foo',
			'value' => 'bar',
			'secure' => true,
		]);

		$item = $this->cookie->get('foo');

		$this->assertTrue($item['secure']);
		$this->assertEquals(true, $item['secure']);
	}

	public function testCookieSecureFalseViaConfig()
	{
		$this->cookie->setSecure(false)->set('foo', 'bar');

		$item = $this->cookie->get('foo');

		$this->assertFalse($item['secure']);
		$this->assertEquals(false, $item['secure']);
	}

	public function testCookieSecureTrueViaConfig()
	{
		$this->cookie->setSecure(true)->set('foo', 'bar');

		$item = $this->cookie->get('foo');

		$this->assertTrue($item['secure']);
		$this->assertEquals(true, $item['secure']);
	}

	public function testCookieDefaultHTTPOnly()
	{
		$this->cookie->set('foo', 'bar');

		$item = $this->cookie->get('foo');

		$this->assertFalse($item['httponly']);
		$this->assertEquals(false, $item['httponly']);
	}

	public function testCookieHTTPOnlyFalseViaArray()
	{
		$this->cookie->set([
			'name' => 'foo',
			'value' => 'bar',
			'httponly' => false,
		]);

		$item = $this->cookie->get('foo');

		$this->assertFalse($item['httponly']);
		$this->assertEquals(false, $item['httponly']);
	}

	public function testCookieHTTPOnlyTrueViaArray()
	{
		$this->cookie->set([
			'name' => 'foo',
			'value' => 'bar',
			'httponly' => true,
		]);

		$item = $this->cookie->get('foo');

		$this->assertTrue($item['httponly']);
		$this->assertEquals(true, $item['httponly']);
	}

	public function testCookieHTTPOnlyFalseViaConfig()
	{
		$this->cookie->setHTTPOnly(false)->set('foo', 'bar');

		$item = $this->cookie->get('foo');

		$this->assertFalse($item['httponly']);
		$this->assertEquals(false, $item['httponly']);
	}

	public function testCookieHTTPOnlyTrueViaConfig()
	{
		$this->cookie->setHTTPOnly(true)->set('foo', 'bar');

		$item = $this->cookie->get('foo');

		$this->assertTrue($item['httponly']);
		$this->assertEquals(true, $item['httponly']);
	}

	public function testCookieDefaultSameSite()
	{
		$this->cookie->set('foo', 'foo');

		$item = $this->cookie->get('foo');

		$this->assertArrayHasKey('samesite', $item);
		$this->assertEquals('Lax', $item['samesite']);
	}

	public function testCookieNoneSameSite()
	{
		$this->cookie->set('foo', 'bar', 0, '/', '', '', false, false, 'None');

		$item = $this->cookie->get('foo');

		$this->assertArrayHasKey('samesite', $item);
		$this->assertEquals('None', $item['samesite']);
	}

	public function testCookieNoneSameSiteViaArray()
	{
		$this->cookie->set([
			'name'     => 'foo',
			'value'    => 'bar',
			'samesite' => 'None',
		]);

		$item = $this->cookie->get('foo');

		$this->assertArrayHasKey('samesite', $item);
		$this->assertEquals('None', $item['samesite']);
	}

	public function testCookieNoneSameSiteViaConfig()
	{
		$this->cookie->setSamesite('None')->set('foo', 'bar');

		$item = $this->cookie->get('foo');

		$this->assertArrayHasKey('samesite', $item);
		$this->assertEquals('None', $item['samesite']);
	}

	public function testCookieLaxSameSite()
	{
		$this->cookie->set('foo', 'bar', 0, '', '', '', false, false, 'Lax');

		$item = $this->cookie->get('foo');

		$this->assertArrayHasKey('samesite', $item);
		$this->assertEquals('Lax', $item['samesite']);
	}

	public function testCookieLaxSameSiteViaArray()
	{
		$this->cookie->set([
			'name'     => 'foo',
			'value'    => 'bar',
			'samesite' => 'Lax',
		]);

		$item = $this->cookie->get('foo');

		$this->assertArrayHasKey('samesite', $item);
		$this->assertEquals('Lax', $item['samesite']);
	}

	public function testCookieLaxSameSiteViaConfig()
	{
		$this->cookie->setSamesite('Lax')->set('foo', 'bar');

		$item = $this->cookie->get('foo');

		$this->assertArrayHasKey('samesite', $item);
		$this->assertEquals('Lax', $item['samesite']);
	}

	public function testCookieStrictSameSite()
	{
		$this->cookie->set('foo', 'bar', 0, '', '', '', false, false, 'Strict');

		$item = $this->cookie->get('foo');

		$this->assertArrayHasKey('samesite', $item);
		$this->assertEquals('Strict', $item['samesite']);
	}

	public function testCookieStrictSameSiteViaArray()
	{
		$this->cookie->set([
			'name'     => 'foo',
			'value'    => 'bar',
			'samesite' => 'Strict',
		]);

		$item = $this->cookie->get('foo');

		$this->assertArrayHasKey('samesite', $item);
		$this->assertEquals('Strict', $item['samesite']);
	}

	public function testCookieStrictSameSiteViaConfig()
	{
		$this->cookie->setSamesite('Strict')->set('foo', 'bar');

		$item = $this->cookie->get('foo');

		$this->assertArrayHasKey('samesite', $item);
		$this->assertEquals('Strict', $item['samesite']);
	}

	public function testSetCookiePrefix()
	{
		$this->assertEquals('mk_', $this->cookie->setPrefix('mk_')->getPrefix());
	}

	public function testSetCookiePath()
	{
		$this->assertEquals('/mk/baz', $this->cookie->setPath('/mk/baz')->getPath());
	}

	public function testSetCookieDomain()
	{
		$this->assertEquals('.baz.mk', $this->cookie->setDomain('.baz.mk')->getDomain());
	}

	public function testSetCookieSecure()
	{
		$this->assertFalse($this->cookie->setSecure(false)->isSecure());
		$this->assertEquals(false, $this->cookie->isSecure());
		$this->assertTrue($this->cookie->setSecure(true)->isSecure());
		$this->assertEquals(true, $this->cookie->isSecure());
	}

	public function testSetCookieHTTPOnly()
	{
		$this->assertFalse($this->cookie->setHTTPOnly(false)->isHTTPOnly());
		$this->assertEquals(false, $this->cookie->isHTTPOnly());
		$this->assertTrue($this->cookie->setHTTPOnly(true)->isHTTPOnly());
		$this->assertEquals(true, $this->cookie->isHTTPOnly());
	}
}
