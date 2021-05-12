<?php

namespace CodeIgniter\Helpers\URLHelper;

use CodeIgniter\Config\Factories;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Router\Exceptions\RouterException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;

/**
 * Test cases for all URL Helper functions
 * that rely on the "current" URL.
 * Includes: current_url, uri_string, uri_is
 *
 * @backupGlobals enabled
 */
final class CurrentUrlTest extends CIUnitTestCase
{
	/**
	 * @var App
	 */
	private $config;

	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		helper('url');
	}

	protected function setUp(): void
	{
		parent::setUp();

		Services::reset(true);

		// Set a common base configuration (overriden by individual tests)
		$this->config            = new App();
		$this->config->baseURL   = 'http://example.com/';
		$this->config->indexPage = 'index.php';
		Factories::injectMock('config', 'App', $this->config);

		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/';
		$_SERVER['SCRIPT_NAME'] = '/index.php';
	}

	public function tearDown(): void
	{
		parent::tearDown();

		$_SERVER = [];
	}

	//--------------------------------------------------------------------
	// current_url
	//--------------------------------------------------------------------

	public function testCurrentURLReturnsBasicURL()
	{
		// Since we're on a CLI, we must provide our own URI
		$this->config->baseURL = 'http://example.com/public';

		$this->assertEquals('http://example.com/public/', current_url());
	}

	public function testCurrentURLReturnsObject()
	{
		// Since we're on a CLI, we must provide our own URI
		$this->config->baseURL = 'http://example.com/public';

		$url = current_url(true);

		$this->assertInstanceOf(URI::class, $url);
		$this->assertEquals('http://example.com/public/', (string) $url);
	}

	public function testCurrentURLEquivalence()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/public';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		// Since we're on a CLI, we must provide our own URI
		Factories::injectMock('config', 'App', $this->config);

		$request = Services::request($this->config);
		Services::injectMock('request', $request);

		$this->assertEquals(base_url(uri_string()), current_url());
	}

	public function testCurrentURLInSubfolder()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/foo/public/bar?baz=quip';
		$_SERVER['SCRIPT_NAME'] = '/foo/public/index.php';

		// Since we're on a CLI, we must provide our own URI
		$this->config->baseURL = 'http://example.com/foo/public';
		Factories::injectMock('config', 'App', $this->config);

		$request = Services::request($this->config);
		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com/foo/public/bar', current_url());
		$this->assertEquals('http://example.com/foo/public/bar?baz=quip', (string) current_url(true));

		$uri = current_url(true);
		$this->assertEquals(['bar'], $uri->getSegments());
		$this->assertEquals('bar', $uri->getSegment(1));
		$this->assertEquals('example.com', $uri->getHost());
		$this->assertEquals('http', $uri->getScheme());
	}

	public function testCurrentURLWithPortInSubfolder()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['SERVER_PORT'] = '8080';
		$_SERVER['REQUEST_URI'] = '/foo/public/bar?baz=quip';
		$_SERVER['SCRIPT_NAME'] = '/foo/public/index.php';

		// Since we're on a CLI, we must provide our own URI
		$this->config->baseURL = 'http://example.com:8080/foo/public';
		Factories::injectMock('config', 'App', $this->config);

		$request = Services::request($this->config);
		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com:8080/foo/public/bar', current_url());
		$this->assertEquals('http://example.com:8080/foo/public/bar?baz=quip', (string) current_url(true));

		$uri = current_url(true);
		$this->assertEquals(['bar'], $uri->getSegments());
		$this->assertEquals('bar', $uri->getSegment(1));
		$this->assertEquals('example.com', $uri->getHost());
		$this->assertEquals('http', $uri->getScheme());
		$this->assertEquals('8080', $uri->getPort());
	}

	//--------------------------------------------------------------------
	// uri_string
	//--------------------------------------------------------------------

	public function testUriStringAbsolute()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/assets/image.jpg';

		$request      = Services::request($this->config);
		$request->uri = new URI('http://example.com/assets/image.jpg');

		Services::injectMock('request', $request);

		$url = current_url();
		$this->assertEquals('/assets/image.jpg', uri_string());
	}

	public function testUriStringRelative()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/assets/image.jpg';

		$request      = Services::request($this->config);
		$request->uri = new URI('http://example.com/assets/image.jpg');

		Services::injectMock('request', $request);

		$url = current_url();
		$this->assertEquals('assets/image.jpg', uri_string(true));
	}

	public function testUriStringNoTrailingSlashAbsolute()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/assets/image.jpg';

		$this->config->baseURL = 'http://example.com';
		$request               = Services::request($this->config);
		$request->uri          = new URI('http://example.com/assets/image.jpg');

		Services::injectMock('request', $request);

		$url = current_url();
		$this->assertEquals('/assets/image.jpg', uri_string());
	}

	public function testUriStringNoTrailingSlashRelative()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/assets/image.jpg';

		$this->config->baseURL = 'http://example.com';
		$request               = Services::request($this->config);
		$request->uri          = new URI('http://example.com/assets/image.jpg');

		Services::injectMock('request', $request);

		$url = current_url();
		$this->assertEquals('assets/image.jpg', uri_string(true));
	}

	public function testUriStringEmptyAbsolute()
	{
		$request      = Services::request($this->config);
		$request->uri = new URI('http://example.com/');

		Services::injectMock('request', $request);

		$url = current_url();
		$this->assertEquals('/', uri_string());
	}

	public function testUriStringEmptyRelative()
	{
		$request      = Services::request($this->config);
		$request->uri = new URI('http://example.com/');

		Services::injectMock('request', $request);

		$url = current_url();
		$this->assertEquals('', uri_string(true));
	}

	public function testUriStringSubfolderAbsolute()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/subfolder/assets/image.jpg';

		$this->config->baseURL = 'http://example.com/subfolder/';
		$request               = Services::request($this->config);
		$request->uri          = new URI('http://example.com/subfolder/assets/image.jpg');

		Services::injectMock('request', $request);

		$url = current_url();
		$this->assertEquals('/subfolder/assets/image.jpg', uri_string());
	}

	public function testUriStringSubfolderRelative()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/assets/image.jpg';
		$_SERVER['REQUEST_URI'] = '/subfolder/assets/image.jpg';

		$this->config->baseURL = 'http://example.com/subfolder/';
		$request               = Services::request($this->config);
		$request->uri          = new URI('http://example.com/subfolder/assets/image.jpg');

		Services::injectMock('request', $request);

		$url = current_url();
		$this->assertEquals('assets/image.jpg', uri_string(true));
	}

	//--------------------------------------------------------------------
	// uri_is
	//--------------------------------------------------------------------

	public function urlIsProvider()
	{
		return [
			[
				'foo/bar',
				'foo/bar',
				true,
			],
			[
				'foo/bar',
				'foo*',
				true,
			],
			[
				'foo/bar',
				'foo',
				false,
			],
			[
				'foo/bar',
				'baz/foo/bar',
				false,
			],
			[
				'',
				'foo*',
				false,
			],
			[
				'foo/',
				'foo*',
				true,
			],
			[
				'foo/',
				'foo',
				true,
			],
		];
	}

	/**
	 * @dataProvider urlIsProvider
	 */
	public function testUrlIs(string $currentPath, string $testPath, bool $expected)
	{
		$_SERVER['HTTP_HOST'] = 'example.com';

		$request      = Services::request();
		$request->uri = new URI('http://example.com/' . $currentPath);
		Services::injectMock('request', $request);

		$this->assertEquals($expected, url_is($testPath));
	}

	/**
	 * @dataProvider urlIsProvider
	 */
	public function testUrlIsNoIndex(string $currentPath, string $testPath, bool $expected)
	{
		$_SERVER['HTTP_HOST']    = 'example.com';
		$this->config->indexPage = '';

		$request      = Services::request($this->config);
		$request->uri = new URI('http://example.com/' . $currentPath);
		Services::injectMock('request', $request);

		$this->assertEquals($expected, url_is($testPath));
	}

	/**
	 * @dataProvider urlIsProvider
	 */
	public function testUrlIsWithSubfolder(string $currentPath, string $testPath, bool $expected)
	{
		$_SERVER['HTTP_HOST']  = 'example.com';
		$this->config->baseURL = 'http://example.com/subfolder/';

		$request      = Services::request($this->config);
		$request->uri = new URI('http://example.com/subfolder/' . $currentPath);
		Services::injectMock('request', $request);

		$this->assertEquals($expected, url_is($testPath));
	}
}
