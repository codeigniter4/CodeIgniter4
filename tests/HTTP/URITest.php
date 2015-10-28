<?php

use CodeIgniter\HTTP\URI;

class URITest extends PHPUnit_Framework_TestCase
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

		$this->assertEquals('http', $uri->scheme());
		$this->assertEquals('username', $uri->userInfo());
		$this->assertEquals('hostname', $uri->host());
		$this->assertEquals('/path', $uri->path());
		$this->assertEquals('arg=value', $uri->query());
		$this->assertEquals('9090', $uri->port());
		$this->assertEquals('anchor', $uri->fragment());

		// Password ignored by default for security reasons.
		$this->assertEquals('username@hostname:9090', $uri->authority());

		$this->assertEquals(['path'], $uri->segments());
	}

	//--------------------------------------------------------------------

	public function testSegmentsIsPopulatedRightForMultipleSegments()
	{
		$uri = new URI('http://hostname/path/to/script');

		$this->assertEquals(['path', 'to', 'script'], $uri->segments());
		$this->assertEquals('path', $uri->segment(1));
		$this->assertEquals('to', $uri->segment(2));
		$this->assertEquals('script', $uri->segment(3));

		$this->assertEquals(3, $uri->totalSegments());
	}

	//--------------------------------------------------------------------

	public function testCanCastAsString()
	{
		$url = 'http://username:password@hostname:9090/path?arg=value#anchor';
		$uri = new URI($url);

		$expected = 'http://username@hostname:9090/path?arg=value#anchor';

		$this->assertEquals($expected, (string)$uri);
	}

	//--------------------------------------------------------------------

	public function testSetSchemeSetsValue()
	{
		$url = 'http://example.com/path';
		$uri = new URI($url);

		$expected = 'https://example.com/path';

		$uri->setScheme('https');
		$this->assertEquals('https', $uri->scheme());
		$this->assertEquals($expected, (string)$uri);
	}

	//--------------------------------------------------------------------

	public function testSetUserInfoSetsValue()
	{
		$url = 'http://example.com/path';
		$uri = new URI($url);

		$expected = 'http://user@example.com/path';

		$uri->setUserInfo('user', 'password');
		$this->assertEquals('user', $uri->userInfo());
		$this->assertEquals($expected, (string)$uri);
	}

	//--------------------------------------------------------------------

	public function testUserInfoCanShowPassword()
	{
		$url = 'http://example.com/path';
		$uri = new URI($url);

		$expected = 'http://user@example.com/path';

		$uri->setUserInfo('user', 'password');
		$this->assertEquals('user', $uri->userInfo());
		$this->assertEquals($expected, (string)$uri);

		$uri->showPassword();

		$expected = 'http://user:password@example.com/path';

		$this->assertEquals('user:password', $uri->userInfo());
		$this->assertEquals($expected, (string)$uri);
	}

	//--------------------------------------------------------------------

	public function testSetHostSetsValue()
	{
		$url = 'http://example.com/path';
		$uri = new URI($url);

		$expected = 'http://another.com/path';

		$uri->setHost('another.com');
		$this->assertEquals('another.com', $uri->host());
		$this->assertEquals($expected, (string)$uri);
	}

	//--------------------------------------------------------------------

	public function testSetPortSetsValue()
	{
		$url = 'http://example.com/path';
		$uri = new URI($url);

		$expected = 'http://example.com:9000/path';

		$uri->setPort(9000);
		$this->assertEquals(9000, $uri->port());
		$this->assertEquals($expected, (string)$uri);
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
		$this->assertEquals('somewhere/else', $uri->path());
		$this->assertEquals($expected, (string)$uri);
	}

	//--------------------------------------------------------------------

	public function invalidPaths()
	{
		return [
			'dot-segment'  => ['/./path/to/nowhere', '/path/to/nowhere'],
			'double-dots'  => ['/../path/to/nowhere', '/path/to/nowhere'],
			'start-dot'    => ['./path/to/nowhere', '/path/to/nowhere'],
			'start-double' => ['../path/to/nowhere', '/path/to/nowhere'],
			'decoded'      => ['../%41path', '/Apath'],
		    'encoded'      => ['/path^here', '/path%5Ehere'],
		];
	}

	//--------------------------------------------------------------------

	/**
	 * @dataProvider invalidPaths
	 * @group        single
	 */
	public function testPathGetsFiltered($path, $expected)
	{
		$uri = new URI();
		$uri->setPath($path);
		$this->assertEquals($expected, $uri->path());
	}

	//--------------------------------------------------------------------

	public function testSetFragmentSetsValue()
	{
		$url = 'http://example.com/path';
		$uri = new URI($url);

		$expected = 'http://example.com/path#good-stuff';

		$uri->setFragment('#good-stuff');
		$this->assertEquals('good-stuff', $uri->fragment());
		$this->assertEquals($expected, (string)$uri);
	}

	//--------------------------------------------------------------------

	public function testSetQuerySetsValue()
	{
		$url = 'http://example.com/path';
		$uri = new URI($url);

		$expected = 'http://example.com/path?key=value';

		$uri->setQuery('?key=value');
		$this->assertEquals('key=value', $uri->query());
		$this->assertEquals($expected, (string)$uri);
	}

	//--------------------------------------------------------------------

	public function testSetQueryArraySetsValue()
	{
		$url = 'http://example.com/path';
		$uri = new URI($url);

		$expected = 'http://example.com/path?key=value';

		$uri->setQueryArray(['key' => 'value']);
		$this->assertEquals('key=value', $uri->query());
		$this->assertEquals($expected, (string)$uri);
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
			'host-only'      => ['http://foo.com/bar', 'foo.com'],
			'host-port'      => ['http://foo.com:3000/bar', 'foo.com:3000'],
			'user-host'      => ['http://me@foo.com/bar', 'me@foo.com'],
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
		$this->assertEquals($expected, $uri->authority());
	}

	//--------------------------------------------------------------------

	public function defaultPorts()
	{
		return [
			'http'  => ['http', 80],
			'https' => ['https', 443],
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

		$this->assertEquals($expected, (string)$uri);
	}

	//--------------------------------------------------------------------

}
