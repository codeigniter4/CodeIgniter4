<?php

namespace CodeIgniter\HTTP;

class URITest extends \CIUnitTestCase
{

	public function setUp()
	{

	}

	//--------------------------------------------------------------------

	public function tearDown()
	{

	}

	//--------------------------------------------------------------------

	public function testConstructorSetsAllParts()
	{
		$uri = new URI('http://username:password@hostname:9090/path?arg=value#anchor');

		$this->assertEquals('http', $uri->getScheme());
		$this->assertEquals('username', $uri->getUserInfo());
		$this->assertEquals('hostname', $uri->getHost());
		$this->assertEquals('/path', $uri->getPath());
		$this->assertEquals('arg=value', $uri->getQuery());
		$this->assertEquals('9090', $uri->getPort());
		$this->assertEquals('anchor', $uri->getFragment());

		// Password ignored by default for security reasons.
		$this->assertEquals('username@hostname:9090', $uri->getAuthority());

		$this->assertEquals(['path'], $uri->getSegments());
	}

	//--------------------------------------------------------------------

	public function testSegmentsIsPopulatedRightForMultipleSegments()
	{
		$uri = new URI('http://hostname/path/to/script');

		$this->assertEquals(['path', 'to', 'script'], $uri->getSegments());
		$this->assertEquals('path', $uri->getSegment(1));
		$this->assertEquals('to', $uri->getSegment(2));
		$this->assertEquals('script', $uri->getSegment(3));

		$this->assertEquals(3, $uri->getTotalSegments());
	}

	//--------------------------------------------------------------------

	public function testCanCastAsString()
	{
		$url = 'http://username:password@hostname:9090/path?arg=value#anchor';
		$uri = new URI($url);

		$expected = 'http://username@hostname:9090/path?arg=value#anchor';

		$this->assertEquals($expected, (string) $uri);
	}

	//--------------------------------------------------------------------

	public function testSimpleUri()
	{
		$url = 'http://example.com';
		$uri = new URI($url);
		$this->assertEquals($url, (string) $uri);

		$url = 'http://example.com/';
		$uri = new URI($url);
		$this->assertEquals($url, (string) $uri);
	}

	//--------------------------------------------------------------------

	public function testEmptyUri()
	{
		$url = '';
		$uri = new URI($url);
		$this->assertEquals('http://'.$url, (string) $uri);
		$url = '/';
		$uri = new URI($url);
		$this->assertEquals('http://'.$url, (string) $uri);
	}

	//--------------------------------------------------------------------

	public function testMissingScheme()
	{
		$url = 'http://foo.bar/baz';
		$uri = new URI($url);
		$this->assertEquals('http', $uri->getScheme());
		$this->assertEquals('foo.bar', $uri->getAuthority());
		$this->assertEquals('/baz', $uri->getPath());
		$this->assertEquals($url, (string) $uri);
	}

	//--------------------------------------------------------------------

	public function testSchemeSub()
	{
		$url = 'example.com';
		$uri = new URI('http://'.$url);
		$uri->setScheme('x');
		$this->assertEquals('x://'.$url, (string) $uri);
	}

	//--------------------------------------------------------------------

	public function testSetSchemeSetsValue()
	{
		$url = 'http://example.com/path';
		$uri = new URI($url);

		$expected = 'https://example.com/path';

		$uri->setScheme('https');
		$this->assertEquals('https', $uri->getScheme());
		$this->assertEquals($expected, (string) $uri);
	}

	//--------------------------------------------------------------------

	public function testSetUserInfoSetsValue()
	{
		$url = 'http://example.com/path';
		$uri = new URI($url);

		$expected = 'http://user@example.com/path';

		$uri->setUserInfo('user', 'password');
		$this->assertEquals('user', $uri->getUserInfo());
		$this->assertEquals($expected, (string) $uri);
	}

	//--------------------------------------------------------------------

	public function testUserInfoCanShowPassword()
	{
		$url = 'http://example.com/path';
		$uri = new URI($url);

		$expected = 'http://user@example.com/path';

		$uri->setUserInfo('user', 'password');
		$this->assertEquals('user', $uri->getUserInfo());
		$this->assertEquals($expected, (string) $uri);

		$uri->showPassword();

		$expected = 'http://user:password@example.com/path';

		$this->assertEquals('user:password', $uri->getUserInfo());
		$this->assertEquals($expected, (string) $uri);
	}

	//--------------------------------------------------------------------

	public function testSetHostSetsValue()
	{
		$url = 'http://example.com/path';
		$uri = new URI($url);

		$expected = 'http://another.com/path';

		$uri->setHost('another.com');
		$this->assertEquals('another.com', $uri->getHost());
		$this->assertEquals($expected, (string) $uri);
	}

	//--------------------------------------------------------------------

	public function testSetPortSetsValue()
	{
		$url = 'http://example.com/path';
		$uri = new URI($url);

		$expected = 'http://example.com:9000/path';

		$uri->setPort(9000);
		$this->assertEquals(9000, $uri->getPort());
		$this->assertEquals($expected, (string) $uri);
	}

	//--------------------------------------------------------------------

	public function testSetPortInvalidValues()
	{
		$url = 'http://example.com/path';
		$uri = new URI($url);

		$this->setExpectedException('InvalidArgumentException', 'Invalid port given.');
		$uri->setPort(70000);
	}

	//--------------------------------------------------------------------

	public function testSetPortTooSmall()
	{
		$url = 'http://example.com/path';
		$uri = new URI($url);

		$this->setExpectedException('InvalidArgumentException', 'Invalid port given.');
		$uri->setPort(-1);
	}

	//--------------------------------------------------------------------

	public function testSetPortZero()
	{
		$url = 'http://example.com/path';
		$uri = new URI($url);

		$this->setExpectedException('InvalidArgumentException', 'Invalid port given.');
		$uri->setPort(0);
	}

	//--------------------------------------------------------------------

	public function testSetPathSetsValue()
	{
		$url = 'http://example.com/path';
		$uri = new URI($url);

		$expected = 'http://example.com/somewhere/else';

		$uri->setPath('somewhere/else');
		$this->assertEquals('somewhere/else', $uri->getPath());
		$this->assertEquals($expected, (string) $uri);
	}

	//--------------------------------------------------------------------

	public function invalidPaths()
	{
		return [
			'dot-segment'	 => ['/./path/to/nowhere', '/path/to/nowhere'],
			'double-dots'	 => ['/../path/to/nowhere', '/path/to/nowhere'],
			'start-dot'		 => ['./path/to/nowhere', '/path/to/nowhere'],
			'start-double'	 => ['../path/to/nowhere', '/path/to/nowhere'],
			'decoded'		 => ['../%41path', '/Apath'],
			'encoded'		 => ['/path^here', '/path%5Ehere'],
		];
	}

	//--------------------------------------------------------------------

	/**
	 * @dataProvider invalidPaths
	 */
	public function testPathGetsFiltered($path, $expected)
	{
		$uri = new URI();
		$uri->setPath($path);
		$this->assertEquals($expected, $uri->getPath());
	}

	//--------------------------------------------------------------------

	public function testSetFragmentSetsValue()
	{
		$url = 'http://example.com/path';
		$uri = new URI($url);

		$expected = 'http://example.com/path#good-stuff';

		$uri->setFragment('#good-stuff');
		$this->assertEquals('good-stuff', $uri->getFragment());
		$this->assertEquals($expected, (string) $uri);
	}

	//--------------------------------------------------------------------

	public function testSetQuerySetsValue()
	{
		$url = 'http://example.com/path';
		$uri = new URI($url);

		$expected = 'http://example.com/path?key=value';

		$uri->setQuery('?key=value');
		$this->assertEquals('key=value', $uri->getQuery());
		$this->assertEquals($expected, (string) $uri);
	}

	//--------------------------------------------------------------------

	public function testSetQueryArraySetsValue()
	{
		$url = 'http://example.com/path';
		$uri = new URI($url);

		$expected = 'http://example.com/path?key=value';

		$uri->setQueryArray(['key' => 'value']);
		$this->assertEquals('key=value', $uri->getQuery());
		$this->assertEquals($expected, (string) $uri);
	}

	//--------------------------------------------------------------------

	public function testSetQueryThrowsErrorWhenFragmentPresent()
	{
		$url = 'http://example.com/path';
		$uri = new URI($url);

		$expected = 'http://example.com/path?key=value';

		$this->setExpectedException('InvalidArgumentException');
		$uri->setQuery('?key=value#fragment');
	}

	//--------------------------------------------------------------------

	public function authorityInfo()
	{
		return [
			'host-only'		 => ['http://foo.com/bar', 'foo.com'],
			'host-port'		 => ['http://foo.com:3000/bar', 'foo.com:3000'],
			'user-host'		 => ['http://me@foo.com/bar', 'me@foo.com'],
			'user-host-port' => ['http://me@foo.com:3000/bar', 'me@foo.com:3000'],
		];
	}

	//--------------------------------------------------------------------

	/**
	 * @dataProvider authorityInfo
	 */
	public function testAuthorityReturnsExceptedValues($url, $expected)
	{
		$uri = new URI($url);
		$this->assertEquals($expected, $uri->getAuthority());
	}

	//--------------------------------------------------------------------

	public function defaultPorts()
	{
		return [
			'http'	 => ['http', 80],
			'https'	 => ['https', 443],
		];
	}

	//--------------------------------------------------------------------

	/**
	 * @dataProvider defaultPorts
	 */
	public function testAuthorityRemovesDefaultPorts($scheme, $port)
	{
		$url = "{$scheme}://example.com:{$port}/path";
		$uri = new URI($url);

		$expected = "{$scheme}://example.com/path";

		$this->assertEquals($expected, (string) $uri);
	}

	//--------------------------------------------------------------------

	public function testSetAuthorityReconstitutes()
	{
		$authority = 'me@foo.com:3000';

		$uri = new URI();
		$uri->setAuthority($authority);

		$this->assertEquals($authority, $uri->getAuthority());
	}

	//--------------------------------------------------------------------

	public function defaultDots()
	{
		return array (
			array ('/foo/..', '/'),
			array ('//foo//..', '/'),
			array ('/foo/../..', '/'),
			array ('/foo/../.', '/'),
			array ('/./foo/..', '/'),
			array ('/./foo', '/foo'),
			array ('/./foo/', '/foo/'),
			array ('/./foo/bar/baz/pho/../..', '/foo/bar'),
			array ('*', '*'),
			array ('/foo', '/foo'),
			array ('/abc/123/../foo/', '/abc/foo/'),
			array ('/a/b/c/./../../g', '/a/g'),
			array ('/b/c/./../../g', '/g'),
			array ('/b/c/./../../g', '/g'),
			array ('/c/./../../g', '/g'),
			array ('/./../../g', '/g'),
		);
	}

	//--------------------------------------------------------------------

	/**
	 * @dataProvider defaultDots
	 */
	public function testRemoveDotSegments($path, $expected)
	{
		$uri = new URI();
		$this->assertEquals($expected, $uri->removeDotSegments($path));
	}

	//--------------------------------------------------------------------

	public function defaultResolutions()
	{
		return [
			['g', 'http://a/b/c/g'],
			['g/', 'http://a/b/c/g/'],
			['/g', 'http://a/g'],
			['#s', 'http://a/b/c/d#s'],
		];
	}

	//--------------------------------------------------------------------

	/**
	 * @dataProvider defaultResolutions
	 */
	public function testResolveRelativeURI($rel, $expected)
	{
		$base = 'http://a/b/c/d';

		$uri = new URI($base);

		$new = $uri->resolveRelativeURI($rel);

		$this->assertEquals($expected, (string) $new);
	}

	//--------------------------------------------------------------------

	public function testAddQueryVar()
	{
	    $base = 'http://example.com/foo';

		$uri = new URI($base);

		$uri->addQuery('bar', 'baz');

		$this->assertEquals('http://example.com/foo?bar=baz', (string)$uri);
	}

	//--------------------------------------------------------------------

	public function testAddQueryVarRespectsExistingQueryVars()
	{
		$base = 'http://example.com/foo?bar=baz';

		$uri = new URI($base);

		$uri->addQuery('baz', 'foz');

		$this->assertEquals('http://example.com/foo?bar=baz&baz=foz', (string)$uri);
	}

	//--------------------------------------------------------------------

	public function testStripQueryVars()
	{
		$base = 'http://example.com/foo?foo=bar&bar=baz&baz=foz';

		$uri = new URI($base);

		$uri->stripQuery('bar', 'baz');

		$this->assertEquals('http://example.com/foo?foo=bar', (string)$uri);
	}

	//--------------------------------------------------------------------

	public function testKeepQueryVars()
	{
		$base = 'http://example.com/foo?foo=bar&bar=baz&baz=foz';

		$uri = new URI($base);

		$uri->keepQuery('bar', 'baz');

		$this->assertEquals('http://example.com/foo?bar=baz&baz=foz', (string)$uri);
	}

	//--------------------------------------------------------------------

	public function testGetQueryExcept()
	{
		$base = 'http://example.com/foo?foo=bar&bar=baz&baz=foz';

		$uri = new URI($base);

		$this->assertEquals('foo=bar&baz=foz', $uri->getQuery(['except' => ['bar']]));
	}

	//--------------------------------------------------------------------

	public function testGetQueryOnly()
	{
		$base = 'http://example.com/foo?foo=bar&bar=baz&baz=foz';

		$uri = new URI($base);

		$this->assertEquals('bar=baz', $uri->getQuery(['only' => ['bar']]));
	}

	//--------------------------------------------------------------------

}
