<?php
namespace CodeIgniter\HTTP;

use Config\App;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\Exceptions\HTTPException;

class URITest extends \CodeIgniter\Test\CIUnitTestCase
{

	protected function setUp(): void
	{
		parent::setUp();
	}

	//--------------------------------------------------------------------

	public function tearDown(): void
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

	public function testSegmentOutOfRange()
	{
		$this->expectException(HTTPException::class);
		$url = 'http://abc.com/a123/b/c';
		$uri = new URI($url);
		$uri->getSegment(22);
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
		$this->assertEquals('http://' . $url, (string) $uri);
		$url = '/';
		$uri = new URI($url);
		$this->assertEquals('http://' . $url, (string) $uri);
	}

	//--------------------------------------------------------------------

	public function testMalformedUri()
	{
		$this->expectException(HTTPException::class);
		$url = 'http://abc:a123';
		$uri = new URI($url);
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
		$uri = new URI('http://' . $url);
		$uri->setScheme('x');
		$this->assertEquals('x://' . $url, (string) $uri);
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

		$errorString = lang('HTTP.invalidPort', [70000]);
		$this->assertNotEmpty($errorString);

		$this->expectException(HTTPException::class);
		$this->expectExceptionMessage(lang('HTTP.invalidPort', ['70000']));
		$uri->setPort(70000);
	}

	//--------------------------------------------------------------------

	public function testSetPortTooSmall()
	{
		$url = 'http://example.com/path';
		$uri = new URI($url);

		$this->expectException(HTTPException::class);
		$this->expectExceptionMessage(lang('HTTP.invalidPort', [-1]));
		$uri->setPort(-1);
	}

	//--------------------------------------------------------------------

	public function testSetPortZero()
	{
		$url = 'http://example.com/path';
		$uri = new URI($url);

		$this->expectException(HTTPException::class);
		$this->expectExceptionMessage(lang('HTTP.invalidPort', [0]));
		$uri->setPort(0);
	}

	//--------------------------------------------------------------------

	public function testCatchesBadPort()
	{
		$this->expectException(HTTPException::class);
		$url = 'http://username:password@hostname:90909/path?arg=value#anchor';
		$uri = new URI();
		$uri->setURI($url);
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
			'dot-segment'  => [
				'/./path/to/nowhere',
				'/path/to/nowhere',
			],
			'double-dots'  => [
				'/../path/to/nowhere',
				'/path/to/nowhere',
			],
			'start-dot'    => [
				'./path/to/nowhere',
				'/path/to/nowhere',
			],
			'start-double' => [
				'../path/to/nowhere',
				'/path/to/nowhere',
			],
			'decoded'      => [
				'../%41path',
				'/Apath',
			],
			'encoded'      => [
				'/path^here',
				'/path%5Ehere',
			],
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

		$this->expectException(HTTPException::class);
		$uri->setQuery('?key=value#fragment');
	}

	//--------------------------------------------------------------------

	public function authorityInfo()
	{
		return [
			'host-only'      => [
				'http://foo.com/bar',
				'foo.com',
			],
			'host-port'      => [
				'http://foo.com:3000/bar',
				'foo.com:3000',
			],
			'user-host'      => [
				'http://me@foo.com/bar',
				'me@foo.com',
			],
			'user-host-port' => [
				'http://me@foo.com:3000/bar',
				'me@foo.com:3000',
			],
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
			'http'  => [
				'http',
				80,
			],
			'https' => [
				'https',
				443,
			],
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
		return [
			[
				'/foo/..',
				'/',
			],
			[
				'//foo//..',
				'/',
			],
			[
				'/foo/../..',
				'/',
			],
			[
				'/foo/../.',
				'/',
			],
			[
				'/./foo/..',
				'/',
			],
			[
				'/./foo',
				'/foo',
			],
			[
				'/./foo/',
				'/foo/',
			],
			[
				'/./foo/bar/baz/pho/../..',
				'/foo/bar',
			],
			[
				'*',
				'*',
			],
			[
				'/foo',
				'/foo',
			],
			[
				'/abc/123/../foo/',
				'/abc/foo/',
			],
			[
				'/a/b/c/./../../g',
				'/a/g',
			],
			[
				'/b/c/./../../g',
				'/g',
			],
			[
				'/b/c/./../../g',
				'/g',
			],
			[
				'/c/./../../g',
				'/g',
			],
			[
				'/./../../g',
				'/g',
			],
		];
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
			[
				'g',
				'http://a/b/c/g',
			],
			[
				'g/',
				'http://a/b/c/g/',
			],
			[
				'/g',
				'http://a/g',
			],
			[
				'#s',
				'http://a/b/c/d#s',
			],
			[
				'http://abc.com/x',
				'http://abc.com/x',
			],
			[
				'?fruit=banana',
				'http://a/b/c/d?fruit=banana',
			],
		];
	}

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

	/**
	 * @dataProvider defaultResolutions
	 * @group        single
	 */
	public function testResolveRelativeURIHTTPS($rel, $expected)
	{
		$base = 'https://a/b/c/d';

		$expected = str_replace('http:', 'https:', $expected);

		$uri = new URI($base);

		$new = $uri->resolveRelativeURI($rel);

		$this->assertEquals($expected, (string) $new);
	}

	public function testResolveRelativeURIWithNoBase()
	{
		$base = 'http://a';

		$uri = new URI($base);

		$new = $uri->resolveRelativeURI('x');

		$this->assertEquals('http://a/x', (string) $new);
	}

	//--------------------------------------------------------------------

	public function testAddQueryVar()
	{
		$base = 'http://example.com/foo';

		$uri = new URI($base);

		$uri->addQuery('bar', 'baz');

		$this->assertEquals('http://example.com/foo?bar=baz', (string) $uri);
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/pull/954
	 */
	public function testSetQueryDecode()
	{
		$base = 'http://example.com/foo';

		$uri     = new URI($base);
		$encoded = urlencode('you+alice+to+the+little');

		$uri->setQuery("q={$encoded}");

		// Should NOT be double-encoded, since http_build_query
		// will encode the value again.
		$this->assertEquals("q={$encoded}", $uri->getQuery());
	}

	public function testAddQueryVarRespectsExistingQueryVars()
	{
		$base = 'http://example.com/foo?bar=baz';

		$uri = new URI($base);

		$uri->addQuery('baz', 'foz');

		$this->assertEquals('http://example.com/foo?bar=baz&baz=foz', (string) $uri);
	}

	//--------------------------------------------------------------------

	public function testStripQueryVars()
	{
		$base = 'http://example.com/foo?foo=bar&bar=baz&baz=foz';

		$uri = new URI($base);

		$uri->stripQuery('bar', 'baz');

		$this->assertEquals('http://example.com/foo?foo=bar', (string) $uri);
	}

	//--------------------------------------------------------------------

	public function testKeepQueryVars()
	{
		$base = 'http://example.com/foo?foo=bar&bar=baz&baz=foz';

		$uri = new URI($base);

		$uri->keepQuery('bar', 'baz');

		$this->assertEquals('http://example.com/foo?bar=baz&baz=foz', (string) $uri);
	}

	//--------------------------------------------------------------------

	public function testEmptyQueryVars()
	{
		$base = 'http://example.com/foo';

		$uri = new URI($base);
		$uri->setQuery('foo=&bar=baz&baz=foz');
		$this->assertEquals('http://example.com/foo?foo=&bar=baz&baz=foz', (string) $uri);
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
		$this->assertEquals('foo=bar&baz=foz', $uri->getQuery(['except' => 'bar']));
	}

	//--------------------------------------------------------------------

	public function testGetQueryWithStrings()
	{
		$base = 'http://example.com/foo?foo=bar&bar=baz&baz=foz';

		$uri = new URI($base);

		$this->assertEquals('bar=baz', $uri->getQuery(['only' => 'bar']));
	}

	//--------------------------------------------------------------------

	/**
	 * @see   https://github.com/codeigniter4/CodeIgniter4/issues/331
	 * @group single
	 */
	public function testNoExtraSlashes()
	{
		$this->assertEquals('http://entirely.different.com/subfolder', (string) (new URI('entirely.different.com/subfolder')));
		$this->assertEquals('http://localhost/subfolder', (string) (new URI('localhost/subfolder')));
		$this->assertEquals('http://localtest.me/subfolder', (string) (new URI('localtest.me/subfolder')));
	}

	//--------------------------------------------------------------------

	public function testSetSegment()
	{
		$base = 'http://example.com/foo/bar/baz';

		$uri = new URI($base);
		$uri->setSegment(2, 'banana');

		$this->assertEquals('foo/banana/baz', $uri->getPath());
	}

	//--------------------------------------------------------------------

	public function testSetSegmentFallback()
	{
		$base = 'http://example.com';

		$uri = new URI($base);
		$uri->setSegment(1, 'first');
		$uri->setSegment(3, 'third');

		$this->assertEquals('first/third', $uri->getPath());

		$uri->setSegment(2, 'second');

		$this->assertEquals('first/second', $uri->getPath());

		$uri->setSegment(3, 'third');

		$this->assertEquals('first/second/third', $uri->getPath());

		$uri->setSegment(5, 'fifth');

		$this->assertEquals('first/second/third/fifth', $uri->getPath());

		// sixth or seventh was not set
		$this->expectException(HTTPException::class);

		$uri->setSegment(8, 'eighth');
	}

	public function testSetBadSegment()
	{
		$this->expectException(HTTPException::class);
		$base = 'http://example.com/foo/bar/baz';

		$uri = new URI($base);
		$uri->setSegment(6, 'banana');
	}

	//--------------------------------------------------------------------
	// Exploratory testing, investigating https://github.com/codeigniter4/CodeIgniter4/issues/2016

	public function testBasedNoIndex()
	{
		Services::reset();

		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/ci/v4/controller/method';

		$config            = new App();
		$config->baseURL   = 'http://example.com/ci/v4';
		$config->indexPage = 'index.php';
		$request           = Services::request($config);
		$request->uri      = new URI('http://example.com/ci/v4/controller/method');

		Services::injectMock('request', $request);

		// going through request
		$this->assertEquals('http://example.com/ci/v4/controller/method', (string) $request->uri);
		$this->assertEquals('/ci/v4/controller/method', $request->uri->getPath());

		// standalone
		$uri = new URI('http://example.com/ci/v4/controller/method');
		$this->assertEquals('http://example.com/ci/v4/controller/method', (string) $uri);
		$this->assertEquals('/ci/v4/controller/method', $uri->getPath());

		$this->assertEquals($uri->getPath(), $request->uri->getPath());
	}

	public function testBasedWithIndex()
	{
		Services::reset();

		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/ci/v4/index.php/controller/method';

		$config            = new App();
		$config->baseURL   = 'http://example.com/ci/v4';
		$config->indexPage = 'index.php';
		$request           = Services::request($config);
		$request->uri      = new URI('http://example.com/ci/v4/index.php/controller/method');

		Services::injectMock('request', $request);

		// going through request
		$this->assertEquals('http://example.com/ci/v4/index.php/controller/method', (string) $request->uri);
		$this->assertEquals('/ci/v4/index.php/controller/method', $request->uri->getPath());

		// standalone
		$uri = new URI('http://example.com/ci/v4/index.php/controller/method');
		$this->assertEquals('http://example.com/ci/v4/index.php/controller/method', (string) $uri);
		$this->assertEquals('/ci/v4/index.php/controller/method', $uri->getPath());

		$this->assertEquals($uri->getPath(), $request->uri->getPath());
	}

}
