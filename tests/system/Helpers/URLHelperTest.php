<?php

namespace CodeIgniter\Helpers;

use CodeIgniter\Config\Factories;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Router\Exceptions\RouterException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;

/**
 * @backupGlobals enabled
 */
class URLHelperTest extends CIUnitTestCase
{
	/**
	 * @var App
	 */
	protected $config;

	protected function setUp(): void
	{
		parent::setUp();

		helper('url');
		Services::reset(true);
		Factories::reset('config');

		// Set a common base configuration (overriden by individual tests)
		$this->config            = new App();
		$this->config->baseURL   = 'http://example.com/';
		$this->config->indexPage = 'index.php';
		$_SERVER['HTTP_HOST']    = 'example.com';
		$_SERVER['REQUEST_URI']  = '/';

		//Config::injectMock('App', $this->config);
	}

	public function tearDown(): void
	{
		parent::tearDown();

		$_SERVER = [];
	}

	//--------------------------------------------------------------------

	/**
	 * @dataProvider siteUrlProvider
	 */
	public function testSiteUrl($baseURL, $indexPage, $param, $protocol, $expected)
	{
		// Set the config
		$this->config->baseURL   = $baseURL;
		$this->config->indexPage = $indexPage;

		// Mock the Request
		$request      = Services::request($this->config);
		$request->uri = new URI('http://example.com/');
		Services::injectMock('request', $request);

		$this->assertEquals($expected, site_url($param, $protocol, $this->config));
	}

	public function siteUrlProvider()
	{
		// baseURL, indexPage, param, protocol, expected
		return [
			[
				'http://example.com/',
				'index.php',
				'',
				null,
				'http://example.com/index.php',
			],
			[
				'http://example.com',
				'index.php',
				'',
				null,
				'http://example.com/index.php',
			],
			[
				'http://example.com/',
				'',
				'',
				null,
				'http://example.com/',
			],
			[
				'http://example.com/',
				'banana.php',
				'',
				null,
				'http://example.com/banana.php',
			],
			[
				'http://example.com/',
				'',
				'abc',
				null,
				'http://example.com/abc',
			],
			[
				'http://example.com/public/',
				'index.php',
				'',
				null,
				'http://example.com/public/index.php',
			],
			[
				'http://example.com/public/',
				'',
				'',
				null,
				'http://example.com/public/',
			],
			[
				'http://example.com/public',
				'',
				'',
				null,
				'http://example.com/public/',
			],
			[
				'http://example.com/public',
				'index.php',
				'/',
				null,
				'http://example.com/public/index.php/',
			],
			[
				'http://example.com/public/',
				'index.php',
				'/',
				null,
				'http://example.com/public/index.php/',
			],
			[
				'http://example.com/',
				'index.php',
				'foo',
				null,
				'http://example.com/index.php/foo',
			],
			[
				'http://example.com/public',
				'index.php',
				'foo',
				null,
				'http://example.com/public/index.php/foo',
			],
			[
				'http://example.com/',
				'index.php',
				'foo',
				'ftp',
				'ftp://example.com/index.php/foo',
			],
			[
				'http://example.com/',
				'index.php',
				'news/local/123',
				null,
				'http://example.com/index.php/news/local/123',
			],
			[
				'http://example.com/',
				'index.php',
				[
					'news',
					'local',
					'123',
				],                null,
				'http://example.com/index.php/news/local/123',
			],
		];
	}

	public function testSiteURLHTTPS()
	{
		$_SERVER['HTTPS'] = 'on';

		$request      = Services::request($this->config);
		$request->uri = new URI('http://example.com/');

		Services::injectMock('request', $request);

		$this->assertEquals('https://example.com/index.php', site_url('', null, $this->config));
	}

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/240
	 */
	public function testSiteURLWithSegments()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/test';

		// Since we're on a CLI, we must provide our own URI
		$request      = Services::request($this->config, false);
		$request->uri = new URI('http://example.com/test');

		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com/index.php', site_url());
	}

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/240
	 */
	public function testSiteURLWithSegmentsAgain()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/test/page';

		// Since we're on a CLI, we must provide our own URI
		$request      = Services::request($this->config, false);
		$request->uri = new URI('http://example.com/test/page');

		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com/index.php', site_url());
		$this->assertEquals('http://example.com/index.php/profile', site_url('profile'));
	}

	//--------------------------------------------------------------------
	// Test base_url

	public function testBaseURLBasics()
	{
		$this->assertEquals('http://example.com', base_url());
	}

	public function testBaseURLAttachesPath()
	{
		$this->assertEquals('http://example.com/foo', base_url('foo'));
	}

	public function testBaseURLAttachesPathArray()
	{
		$this->assertEquals('http://example.com/foo/bar', base_url(['foo', 'bar']));
	}

	public function testBaseURLAttachesScheme()
	{
		$this->assertEquals('https://example.com/foo', base_url('foo', 'https'));
	}

	public function testBaseURLHeedsBaseURL()
	{
		// Since we're on a CLI, we must provide our own URI
		$this->config->baseURL = 'http://example.com/public';
		$request               = Services::request($this->config);
		$request->uri          = new URI('http://example.com/public');

		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com/public', base_url());
	}

	public function testBaseURLNoTrailingSlash()
	{
		// Since we're on a CLI, we must provide our own URI
		$this->config->baseURL = 'http://example.com';
		$request               = Services::request($this->config);
		$request->uri          = new URI('http://example.com/foobar');

		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com', base_url());
	}

	public function testBaseURLExample()
	{
		$this->assertEquals('http://example.com/blog/post/123', base_url('blog/post/123'));
	}

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/240
	 */
	public function testBaseURLWithSegments()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/test';

		// Since we're on a CLI, we must provide our own URI
		$request      = Services::request($this->config, false);
		$request->uri = new URI('http://example.com/test');

		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com', base_url());
	}

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/867
	 */
	public function testBaseURLHTTPS()
	{
		$_SERVER['HTTPS'] = 'on';

		$this->assertEquals('https://example.com/blog/post/123', base_url('blog/post/123'));
	}

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/240
	 */
	public function testBaseURLWithSegmentsAgain()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/test/page';

		// Since we're on a CLI, we must provide our own URI
		$request      = Services::request($this->config, false);
		$request->uri = new URI('http://example.com/test/page');

		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com', base_url());
		$this->assertEquals('http://example.com/profile', base_url('profile'));
	}

	public function testBaseURLHasSubfolder()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/subfolder/test';
		$_SERVER['SCRIPT_NAME'] = '/subfolder/index.php';

		// Since we're on a CLI, we must provide our own URI
		$this->config->baseURL = 'http://example.com/subfolder/';
		Factories::injectMock('config', 'App', $this->config);

		$request = Services::request($this->config, false);
		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com/subfolder/foo', base_url('foo'));
		$this->assertEquals('http://example.com/subfolder', base_url());
	}

	public function testBaseURLNoTrailingSlashHasSubfolder()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/subfolder/test';
		$_SERVER['SCRIPT_NAME'] = '/subfolder/index.php';

		// Since we're on a CLI, we must provide our own URI
		$this->config->baseURL = 'http://example.com/subfolder';
		Factories::injectMock('config', 'App', $this->config);

		$request = Services::request($this->config, false);
		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com/subfolder/foo', base_url('foo'));
		$this->assertEquals('http://example.com/subfolder', base_url());
	}

	//--------------------------------------------------------------------
	// Test current_url

	public function testCurrentURLReturnsBasicURL()
	{
		// Since we're on a CLI, we must provide our own URI
		$this->config->baseURL = 'http://example.com/public';
		$request               = Services::request($this->config);
		$request->uri          = new URI('http://example.com/public');

		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com/public', current_url());
	}

	public function testCurrentURLReturnsObject()
	{
		// Since we're on a CLI, we must provide our own URI
		$this->config->baseURL = 'http://example.com/public';
		$request               = Services::request($this->config);
		$request->uri          = new URI('http://example.com/public');

		Services::injectMock('request', $request);

		$url = current_url(true);

		$this->assertInstanceOf(URI::class, $url);
		$this->assertEquals('http://example.com/public', (string) $url);
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
	// Test previous_url

	public function testPreviousURLUsesSessionFirst()
	{
		$uri1 = 'http://example.com/one?two';
		$uri2 = 'http://example.com/two?foo';

		$_SERVER['HTTP_REFERER']      = $uri1;
		$_SESSION['_ci_previous_url'] = $uri2;

		// Since we're on a CLI, we must provide our own URI
		$this->config->baseURL = 'http://example.com/public';
		$request               = Services::request($this->config);
		$request->uri          = new URI('http://example.com/public');

		Services::injectMock('request', $request);

		$this->assertEquals($uri2, previous_url());
	}

	//--------------------------------------------------------------------

	public function testPreviousURLUsesRefererIfNeeded()
	{
		$uri1 = 'http://example.com/one?two';
		$uri2 = 'http://example.com/two?foo';

		$_SERVER['HTTP_REFERER'] = $uri1;

		// Since we're on a CLI, we must provide our own URI
		$this->config->baseURL = 'http://example.com/public';
		$request               = Services::request($this->config);
		$request->uri          = new URI('http://example.com/public');

		Services::injectMock('request', $request);

		$this->assertEquals($uri1, previous_url());
	}

	//--------------------------------------------------------------------
	// Test uri_string

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
	// Test index_page

	public function testIndexPage()
	{
		$request      = Services::request($this->config);
		$request->uri = new URI('http://example.com/');

		Services::injectMock('request', $request);

		$this->assertEquals('index.php', index_page());
	}

	public function testIndexPageAlt()
	{
		$this->config->indexPage = 'banana.php';
		$request                 = Services::request($this->config);
		$request->uri            = new URI('http://example.com/');

		Services::injectMock('request', $request);

		$this->assertEquals('banana.php', index_page($this->config));
	}

	//--------------------------------------------------------------------
	// Test anchor

	public function anchorNormalPatterns()
	{
		return [
			'normal01' => [
				'<a href="http://example.com/index.php">http://example.com/index.php</a>',
				'',
			],
			'normal02' => [
				'<a href="http://example.com/index.php">Bananas</a>',
				'/',
				'Bananas',
			],
			'normal03' => [
				'<a href="http://example.com/index.php" fruit="peach">http://example.com/index.php</a>',
				'/',
				'',
				'fruit="peach"',
			],
			'normal04' => [
				'<a href="http://example.com/index.php" fruit=peach>Bananas</a>',
				'/',
				'Bananas',
				'fruit=peach',
			],
			'normal05' => [
				'<a href="http://example.com/index.php" fruit="peach">http://example.com/index.php</a>',
				'/',
				'',
				['fruit' => 'peach'],
			],
			'normal06' => [
				'<a href="http://example.com/index.php" fruit="peach">Bananas</a>',
				'/',
				'Bananas',
				['fruit' => 'peach'],
			],
			'normal07' => [
				'<a href="http://example.com/index.php">http://example.com/index.php</a>',
				'/',
			],
		];
	}

	/**
	 * @dataProvider anchorNormalPatterns
	 */
	public function testAnchor($expected = '', $uri = '', $title = '', $attributes = '')
	{
		$request      = Services::request($this->config);
		$request->uri = new URI('http://example.com/');

		Services::injectMock('request', $request);
		$this->assertEquals($expected, anchor($uri, $title, $attributes, $this->config));
	}

	public function anchorNoindexPatterns()
	{
		return [
			'noindex01' => [
				'<a href="http://example.com">http://example.com</a>',
				'',
			],
			'noindex02' => [
				'<a href="http://example.com">Bananas</a>',
				'',
				'Bananas',
			],
			'noindex03' => [
				'<a href="http://example.com" fruit="peach">http://example.com</a>',
				'',
				'',
				'fruit="peach"',
			],
			'noindex04' => [
				'<a href="http://example.com" fruit=peach>Bananas</a>',
				'',
				'Bananas',
				'fruit=peach',
			],
			'noindex05' => [
				'<a href="http://example.com" fruit="peach">http://example.com</a>',
				'',
				'',
				['fruit' => 'peach'],
			],
			'noindex06' => [
				'<a href="http://example.com" fruit="peach">Bananas</a>',
				'',
				'Bananas',
				['fruit' => 'peach'],
			],
			'noindex07' => [
				'<a href="http://example.com">http://example.com</a>',
				'/',
			],
			'noindex08' => [
				'<a href="http://example.com" class="btn btn-primary">http://example.com</a>',
				'',
				'',
				['class' => 'btn btn-primary'],
			],
		];
	}

	/**
	 * @dataProvider anchorNoindexPatterns
	 */
	public function testAnchorNoindex($expected = '', $uri = '', $title = '', $attributes = '')
	{
		$this->config->indexPage = '';
		$request                 = Services::request($this->config);
		$request->uri            = new URI('http://example.com/');

		Services::injectMock('request', $request);
		$this->assertEquals($expected, anchor($uri, $title, $attributes, $this->config));
	}

	public function anchorSubpagePatterns()
	{
		return [
			'subpage01' => [
				'<a href="http://example.com/mush">http://example.com/mush</a>',
				'/mush',
			],
			'subpage02' => [
				'<a href="http://example.com/mush">Bananas</a>',
				'/mush',
				'Bananas',
			],
			'subpage03' => [
				'<a href="http://example.com/mush" fruit="peach">http://example.com/mush</a>',
				'/mush',
				'',
				'fruit="peach"',
			],
			'subpage04' => [
				'<a href="http://example.com/mush" fruit=peach>Bananas</a>',
				'/mush',
				'Bananas',
				'fruit=peach',
			],
			'subpage05' => [
				'<a href="http://example.com/mush" fruit="peach">http://example.com/mush</a>',
				'/mush',
				'',
				['fruit' => 'peach'],
			],
			'subpage06' => [
				'<a href="http://example.com/mush" fruit="peach">Bananas</a>',
				'/mush',
				'Bananas',
				['fruit' => 'peach'],
			],
		];
	}

	/**
	 * @dataProvider anchorSubpagePatterns
	 */
	public function testAnchorTargetted($expected = '', $uri = '', $title = '', $attributes = '')
	{
		$this->config->indexPage = '';
		$request                 = Services::request($this->config);
		$request->uri            = new URI('http://example.com/');

		Services::injectMock('request', $request);
		$this->assertEquals($expected, anchor($uri, $title, $attributes, $this->config));
	}

	public function anchorExamplePatterns()
	{
		return [
			'egpage01' => [
				'<a href="http://example.com/index.php/news/local/123" title="News title">My News</a>',
				'news/local/123',
				'My News',
				'title="News title"',
			],
			'egpage02' => [
				'<a href="http://example.com/index.php/news/local/123" title="The best news!">My News</a>',
				'news/local/123',
				'My News',
				['title' => 'The best news!'],
			],
			'egpage03' => [
				'<a href="http://example.com/index.php">Click here</a>',
				'',
				'Click here',
			],
			'egpage04' => [
				'<a href="http://example.com/index.php">Click here</a>',
				'/',
				'Click here',
			],
		];
	}

	/**
	 * @dataProvider anchorExamplePatterns
	 */
	public function testAnchorExamples($expected = '', $uri = '', $title = '', $attributes = '')
	{
		$request      = Services::request($this->config);
		$request->uri = new URI('http://example.com/');

		Services::injectMock('request', $request);
		$this->assertEquals($expected, anchor($uri, $title, $attributes, $this->config));
	}

	//--------------------------------------------------------------------
	// Test anchor_popup

	public function anchorPopupPatterns()
	{
		return [
			'normal01' => [
				'<a href="http://example.com/index.php" onclick="window.open(\'http://example.com/index.php\', \'_blank\'); return false;">http://example.com/index.php</a>',
				'',
			],
			'normal02' => [
				'<a href="http://example.com/index.php" onclick="window.open(\'http://example.com/index.php\', \'_blank\'); return false;">Bananas</a>',
				'/',
				'Bananas',
			],
			'normal07' => [
				'<a href="http://example.com/index.php" onclick="window.open(\'http://example.com/index.php\', \'_blank\'); return false;">http://example.com/index.php</a>',
				'/',
			],
			'normal08' => [
				'<a href="http://example.com/index.php/news/local/123" onclick="window.open(\'http://example.com/index.php/news/local/123\', \'_blank\', \'width=800,height=600,scrollbars=yes,menubar=no,status=yes,resizable=yes,screenx=0,screeny=0\'); return false;">Click Me!</a>',
				'news/local/123',
				'Click Me!',
				[
					'width'       => 800,
					'height'      => 600,
					'scrollbars'  => 'yes',
					'status'      => 'yes',
					'resizable'   => 'yes',
					'screenx'     => 0,
					'screeny'     => 0,
					'window_name' => '_blank',
				],
			],
			'normal09' => [
				'<a href="http://example.com/index.php/news/local/123" onclick="window.open(\'http://example.com/index.php/news/local/123\', \'_blank\', \'width=800,height=600,scrollbars=yes,menubar=no,status=yes,resizable=yes,screenx=0,screeny=0\'); return false;">Click Me!</a>',
				'news/local/123',
				'Click Me!',
				[],
			],
		];
	}

	/**
	 * @dataProvider anchorPopupPatterns
	 */
	public function testAnchorPopup($expected = '', $uri = '', $title = '', $attributes = false)
	{
		$request      = Services::request($this->config);
		$request->uri = new URI('http://example.com/');

		Services::injectMock('request', $request);
		$this->assertEquals($expected, anchor_popup($uri, $title, $attributes, $this->config));
	}

	//--------------------------------------------------------------------
	// Test mailto

	public function mailtoPatterns()
	{
		return [
			'page01' => [
				'<a href="mailto:me@my-site.com">Click Here to Contact Me</a>',
				'me@my-site.com',
				'Click Here to Contact Me',
			],
			'page02' => [
				'<a href="mailto:me@my-site.com" title="Mail me">Contact Me</a>',
				'me@my-site.com',
				'Contact Me',
				['title' => 'Mail me'],
			],
			'page03' => [
				'<a href="mailto:me@my-site.com">me@my-site.com</a>',
				'me@my-site.com',
			],
		];
	}

	/**
	 * @dataProvider mailtoPatterns
	 */
	public function testMailto($expected = '', $email = '', $title = '', $attributes = '')
	{
		$request      = Services::request($this->config);
		$request->uri = new URI('http://example.com/');

		Services::injectMock('request', $request);

		$this->assertEquals($expected, mailto($email, $title, $attributes));
	}

	//--------------------------------------------------------------------
	// Test safe_mailto

	public function safeMailtoPatterns()
	{
		return [
			'page01' => [
				"<script type=\"text/javascript\">var l=new Array();l[0] = '>';l[1] = 'a';l[2] = '/';l[3] = '<';l[4] = '|101';l[5] = '|77';l[6] = '|32';l[7] = '|116';l[8] = '|99';l[9] = '|97';l[10] = '|116';l[11] = '|110';l[12] = '|111';l[13] = '|67';l[14] = '|32';l[15] = '|111';l[16] = '|116';l[17] = '|32';l[18] = '|101';l[19] = '|114';l[20] = '|101';l[21] = '|72';l[22] = '|32';l[23] = '|107';l[24] = '|99';l[25] = '|105';l[26] = '|108';l[27] = '|67';l[28] = '>';l[29] = '\"';l[30] = '|109';l[31] = '|111';l[32] = '|99';l[33] = '|46';l[34] = '|101';l[35] = '|116';l[36] = '|105';l[37] = '|115';l[38] = '|45';l[39] = '|121';l[40] = '|109';l[41] = '|64';l[42] = '|101';l[43] = '|109';l[44] = ':';l[45] = 'o';l[46] = 't';l[47] = 'l';l[48] = 'i';l[49] = 'a';l[50] = 'm';l[51] = '\"';l[52] = '=';l[53] = 'f';l[54] = 'e';l[55] = 'r';l[56] = 'h';l[57] = ' ';l[58] = 'a';l[59] = '<';for (var i = l.length-1; i >= 0; i=i-1) {if (l[i].substring(0, 1) === '|') document.write(\"&#\"+unescape(l[i].substring(1))+\";\");else document.write(unescape(l[i]));}</script>",
				'me@my-site.com',
				'Click Here to Contact Me',
			],
			'page02' => [
				"<script type=\"text/javascript\">var l=new Array();l[0] = '>';l[1] = 'a';l[2] = '/';l[3] = '<';l[4] = '|101';l[5] = '|77';l[6] = '|32';l[7] = '|116';l[8] = '|99';l[9] = '|97';l[10] = '|116';l[11] = '|110';l[12] = '|111';l[13] = '|67';l[14] = '>';l[15] = '\"';l[16] = '|101';l[17] = '|109';l[18] = '|32';l[19] = '|108';l[20] = '|105';l[21] = '|97';l[22] = '|77';l[23] = ' title=\"';l[24] = '\"';l[25] = '|109';l[26] = '|111';l[27] = '|99';l[28] = '|46';l[29] = '|101';l[30] = '|116';l[31] = '|105';l[32] = '|115';l[33] = '|45';l[34] = '|121';l[35] = '|109';l[36] = '|64';l[37] = '|101';l[38] = '|109';l[39] = ':';l[40] = 'o';l[41] = 't';l[42] = 'l';l[43] = 'i';l[44] = 'a';l[45] = 'm';l[46] = '\"';l[47] = '=';l[48] = 'f';l[49] = 'e';l[50] = 'r';l[51] = 'h';l[52] = ' ';l[53] = 'a';l[54] = '<';for (var i = l.length-1; i >= 0; i=i-1) {if (l[i].substring(0, 1) === '|') document.write(\"&#\"+unescape(l[i].substring(1))+\";\");else document.write(unescape(l[i]));}</script>",
				'me@my-site.com',
				'Contact Me',
				['title' => 'Mail me'],
			],
			'page03' => [
				"<script type=\"text/javascript\">var l=new Array();l[0] = '>';l[1] = 'a';l[2] = '/';l[3] = '<';l[4] = '|109';l[5] = '|111';l[6] = '|99';l[7] = '|46';l[8] = '|101';l[9] = '|116';l[10] = '|105';l[11] = '|115';l[12] = '|45';l[13] = '|121';l[14] = '|109';l[15] = '|64';l[16] = '|101';l[17] = '|109';l[18] = '>';l[19] = '\"';l[20] = '|109';l[21] = '|111';l[22] = '|99';l[23] = '|46';l[24] = '|101';l[25] = '|116';l[26] = '|105';l[27] = '|115';l[28] = '|45';l[29] = '|121';l[30] = '|109';l[31] = '|64';l[32] = '|101';l[33] = '|109';l[34] = ':';l[35] = 'o';l[36] = 't';l[37] = 'l';l[38] = 'i';l[39] = 'a';l[40] = 'm';l[41] = '\"';l[42] = '=';l[43] = 'f';l[44] = 'e';l[45] = 'r';l[46] = 'h';l[47] = ' ';l[48] = 'a';l[49] = '<';for (var i = l.length-1; i >= 0; i=i-1) {if (l[i].substring(0, 1) === '|') document.write(\"&#\"+unescape(l[i].substring(1))+\";\");else document.write(unescape(l[i]));}</script>",
				'me@my-site.com',
			],
		];
	}

	/**
	 * @dataProvider safeMailtoPatterns
	 */
	public function testSafeMailto($expected = '', $email = '', $title = '', $attributes = '')
	{
		$request      = Services::request($this->config);
		$request->uri = new URI('http://example.com/');

		Services::injectMock('request', $request);

		$this->assertEquals($expected, safe_mailto($email, $title, $attributes));
	}

	//--------------------------------------------------------------------
	// Test auto_link

	public function autolinkUrls()
	{
		return [
			'test01' => [
				'www.codeigniter.com test',
				'<a href="http://www.codeigniter.com">www.codeigniter.com</a> test',
			],
			'test02' => [
				'This is my noreply@codeigniter.com test',
				'This is my noreply@codeigniter.com test',
			],
			'test03' => [
				'<br />www.google.com',
				'<br /><a href="http://www.google.com">www.google.com</a>',
			],
			'test04' => [
				'Download CodeIgniter at www.codeigniter.com. Period test.',
				'Download CodeIgniter at <a href="http://www.codeigniter.com">www.codeigniter.com</a>. Period test.',
			],
			'test05' => [
				'Download CodeIgniter at www.codeigniter.com, comma test',
				'Download CodeIgniter at <a href="http://www.codeigniter.com">www.codeigniter.com</a>, comma test',
			],
			'test06' => [
				'This one: ://codeigniter.com must not break this one: http://codeigniter.com',
				'This one: <a href="://codeigniter.com">://codeigniter.com</a> must not break this one: <a href="http://codeigniter.com">http://codeigniter.com</a>',
			],
			'test07' => [
				'Visit example.com or email foo@bar.com',
				'Visit example.com or email foo@bar.com',
			],
			'test08' => [
				'Visit www.example.com or email foo@bar.com',
				'Visit <a href="http://www.example.com">www.example.com</a> or email foo@bar.com',
			],
		];
	}

	/**
	 * @dataProvider autolinkUrls
	 */
	public function testAutoLinkUrl($in, $out)
	{
		$this->assertEquals($out, auto_link($in, 'url'));
	}

	public function autolinkEmails()
	{
		return [
			'test01' => [
				'www.codeigniter.com test',
				'www.codeigniter.com test',
			],
			'test02' => [
				'This is my noreply@codeigniter.com test',
				"This is my <script type=\"text/javascript\">var l=new Array();l[0] = '>';l[1] = 'a';l[2] = '/';l[3] = '<';l[4] = '|109';l[5] = '|111';l[6] = '|99';l[7] = '|46';l[8] = '|114';l[9] = '|101';l[10] = '|116';l[11] = '|105';l[12] = '|110';l[13] = '|103';l[14] = '|105';l[15] = '|101';l[16] = '|100';l[17] = '|111';l[18] = '|99';l[19] = '|64';l[20] = '|121';l[21] = '|108';l[22] = '|112';l[23] = '|101';l[24] = '|114';l[25] = '|111';l[26] = '|110';l[27] = '>';l[28] = '\"';l[29] = '|109';l[30] = '|111';l[31] = '|99';l[32] = '|46';l[33] = '|114';l[34] = '|101';l[35] = '|116';l[36] = '|105';l[37] = '|110';l[38] = '|103';l[39] = '|105';l[40] = '|101';l[41] = '|100';l[42] = '|111';l[43] = '|99';l[44] = '|64';l[45] = '|121';l[46] = '|108';l[47] = '|112';l[48] = '|101';l[49] = '|114';l[50] = '|111';l[51] = '|110';l[52] = ':';l[53] = 'o';l[54] = 't';l[55] = 'l';l[56] = 'i';l[57] = 'a';l[58] = 'm';l[59] = '\"';l[60] = '=';l[61] = 'f';l[62] = 'e';l[63] = 'r';l[64] = 'h';l[65] = ' ';l[66] = 'a';l[67] = '<';for (var i = l.length-1; i >= 0; i=i-1) {if (l[i].substring(0, 1) === '|') document.write(\"&#\"+unescape(l[i].substring(1))+\";\");else document.write(unescape(l[i]));}</script> test",
			],
			'test03' => [
				'<br />www.google.com',
				'<br />www.google.com',
			],
			'test04' => [
				'Download CodeIgniter at www.codeigniter.com. Period test.',
				'Download CodeIgniter at www.codeigniter.com. Period test.',
			],
			'test05' => [
				'Download CodeIgniter at www.codeigniter.com, comma test',
				'Download CodeIgniter at www.codeigniter.com, comma test',
			],
			'test06' => [
				'This one: ://codeigniter.com must not break this one: http://codeigniter.com',
				'This one: ://codeigniter.com must not break this one: http://codeigniter.com',
			],
			'test07' => [
				'Visit example.com or email foo@bar.com',
				"Visit example.com or email <script type=\"text/javascript\">var l=new Array();l[0] = '>';l[1] = 'a';l[2] = '/';l[3] = '<';l[4] = '|109';l[5] = '|111';l[6] = '|99';l[7] = '|46';l[8] = '|114';l[9] = '|97';l[10] = '|98';l[11] = '|64';l[12] = '|111';l[13] = '|111';l[14] = '|102';l[15] = '>';l[16] = '\"';l[17] = '|109';l[18] = '|111';l[19] = '|99';l[20] = '|46';l[21] = '|114';l[22] = '|97';l[23] = '|98';l[24] = '|64';l[25] = '|111';l[26] = '|111';l[27] = '|102';l[28] = ':';l[29] = 'o';l[30] = 't';l[31] = 'l';l[32] = 'i';l[33] = 'a';l[34] = 'm';l[35] = '\"';l[36] = '=';l[37] = 'f';l[38] = 'e';l[39] = 'r';l[40] = 'h';l[41] = ' ';l[42] = 'a';l[43] = '<';for (var i = l.length-1; i >= 0; i=i-1) {if (l[i].substring(0, 1) === '|') document.write(\"&#\"+unescape(l[i].substring(1))+\";\");else document.write(unescape(l[i]));}</script>",
			],
			'test08' => [
				'Visit www.example.com or email foo@bar.com',
				"Visit www.example.com or email <script type=\"text/javascript\">var l=new Array();l[0] = '>';l[1] = 'a';l[2] = '/';l[3] = '<';l[4] = '|109';l[5] = '|111';l[6] = '|99';l[7] = '|46';l[8] = '|114';l[9] = '|97';l[10] = '|98';l[11] = '|64';l[12] = '|111';l[13] = '|111';l[14] = '|102';l[15] = '>';l[16] = '\"';l[17] = '|109';l[18] = '|111';l[19] = '|99';l[20] = '|46';l[21] = '|114';l[22] = '|97';l[23] = '|98';l[24] = '|64';l[25] = '|111';l[26] = '|111';l[27] = '|102';l[28] = ':';l[29] = 'o';l[30] = 't';l[31] = 'l';l[32] = 'i';l[33] = 'a';l[34] = 'm';l[35] = '\"';l[36] = '=';l[37] = 'f';l[38] = 'e';l[39] = 'r';l[40] = 'h';l[41] = ' ';l[42] = 'a';l[43] = '<';for (var i = l.length-1; i >= 0; i=i-1) {if (l[i].substring(0, 1) === '|') document.write(\"&#\"+unescape(l[i].substring(1))+\";\");else document.write(unescape(l[i]));}</script>",
			],
		];
	}

	/**
	 * @dataProvider autolinkEmails
	 */
	public function testAutoLinkEmail($in, $out)
	{
		$this->assertEquals($out, auto_link($in, 'email'));
	}

	public function autolinkBoth()
	{
		return [
			'test01' => [
				'www.codeigniter.com test',
				'<a href="http://www.codeigniter.com">www.codeigniter.com</a> test',
			],
			'test02' => [
				'This is my noreply@codeigniter.com test',
				"This is my <script type=\"text/javascript\">var l=new Array();l[0] = '>';l[1] = 'a';l[2] = '/';l[3] = '<';l[4] = '|109';l[5] = '|111';l[6] = '|99';l[7] = '|46';l[8] = '|114';l[9] = '|101';l[10] = '|116';l[11] = '|105';l[12] = '|110';l[13] = '|103';l[14] = '|105';l[15] = '|101';l[16] = '|100';l[17] = '|111';l[18] = '|99';l[19] = '|64';l[20] = '|121';l[21] = '|108';l[22] = '|112';l[23] = '|101';l[24] = '|114';l[25] = '|111';l[26] = '|110';l[27] = '>';l[28] = '\"';l[29] = '|109';l[30] = '|111';l[31] = '|99';l[32] = '|46';l[33] = '|114';l[34] = '|101';l[35] = '|116';l[36] = '|105';l[37] = '|110';l[38] = '|103';l[39] = '|105';l[40] = '|101';l[41] = '|100';l[42] = '|111';l[43] = '|99';l[44] = '|64';l[45] = '|121';l[46] = '|108';l[47] = '|112';l[48] = '|101';l[49] = '|114';l[50] = '|111';l[51] = '|110';l[52] = ':';l[53] = 'o';l[54] = 't';l[55] = 'l';l[56] = 'i';l[57] = 'a';l[58] = 'm';l[59] = '\"';l[60] = '=';l[61] = 'f';l[62] = 'e';l[63] = 'r';l[64] = 'h';l[65] = ' ';l[66] = 'a';l[67] = '<';for (var i = l.length-1; i >= 0; i=i-1) {if (l[i].substring(0, 1) === '|') document.write(\"&#\"+unescape(l[i].substring(1))+\";\");else document.write(unescape(l[i]));}</script> test",
			],
			'test03' => [
				'<br />www.google.com',
				'<br /><a href="http://www.google.com">www.google.com</a>',
			],
			'test04' => [
				'Download CodeIgniter at www.codeigniter.com. Period test.',
				'Download CodeIgniter at <a href="http://www.codeigniter.com">www.codeigniter.com</a>. Period test.',
			],
			'test05' => [
				'Download CodeIgniter at www.codeigniter.com, comma test',
				'Download CodeIgniter at <a href="http://www.codeigniter.com">www.codeigniter.com</a>, comma test',
			],
			'test06' => [
				'This one: ://codeigniter.com must not break this one: http://codeigniter.com',
				'This one: <a href="://codeigniter.com">://codeigniter.com</a> must not break this one: <a href="http://codeigniter.com">http://codeigniter.com</a>',
			],
			'test07' => [
				'Visit example.com or email foo@bar.com',
				"Visit example.com or email <script type=\"text/javascript\">var l=new Array();l[0] = '>';l[1] = 'a';l[2] = '/';l[3] = '<';l[4] = '|109';l[5] = '|111';l[6] = '|99';l[7] = '|46';l[8] = '|114';l[9] = '|97';l[10] = '|98';l[11] = '|64';l[12] = '|111';l[13] = '|111';l[14] = '|102';l[15] = '>';l[16] = '\"';l[17] = '|109';l[18] = '|111';l[19] = '|99';l[20] = '|46';l[21] = '|114';l[22] = '|97';l[23] = '|98';l[24] = '|64';l[25] = '|111';l[26] = '|111';l[27] = '|102';l[28] = ':';l[29] = 'o';l[30] = 't';l[31] = 'l';l[32] = 'i';l[33] = 'a';l[34] = 'm';l[35] = '\"';l[36] = '=';l[37] = 'f';l[38] = 'e';l[39] = 'r';l[40] = 'h';l[41] = ' ';l[42] = 'a';l[43] = '<';for (var i = l.length-1; i >= 0; i=i-1) {if (l[i].substring(0, 1) === '|') document.write(\"&#\"+unescape(l[i].substring(1))+\";\");else document.write(unescape(l[i]));}</script>",
			],
			'test08' => [
				'Visit www.example.com or email foo@bar.com',
				"Visit <a href=\"http://www.example.com\">www.example.com</a> or email <script type=\"text/javascript\">var l=new Array();l[0] = '>';l[1] = 'a';l[2] = '/';l[3] = '<';l[4] = '|109';l[5] = '|111';l[6] = '|99';l[7] = '|46';l[8] = '|114';l[9] = '|97';l[10] = '|98';l[11] = '|64';l[12] = '|111';l[13] = '|111';l[14] = '|102';l[15] = '>';l[16] = '\"';l[17] = '|109';l[18] = '|111';l[19] = '|99';l[20] = '|46';l[21] = '|114';l[22] = '|97';l[23] = '|98';l[24] = '|64';l[25] = '|111';l[26] = '|111';l[27] = '|102';l[28] = ':';l[29] = 'o';l[30] = 't';l[31] = 'l';l[32] = 'i';l[33] = 'a';l[34] = 'm';l[35] = '\"';l[36] = '=';l[37] = 'f';l[38] = 'e';l[39] = 'r';l[40] = 'h';l[41] = ' ';l[42] = 'a';l[43] = '<';for (var i = l.length-1; i >= 0; i=i-1) {if (l[i].substring(0, 1) === '|') document.write(\"&#\"+unescape(l[i].substring(1))+\";\");else document.write(unescape(l[i]));}</script>",
			],
		];
	}

	/**
	 * @dataProvider autolinkBoth
	 */
	public function testAutolinkBoth($in, $out)
	{
		$this->assertEquals($out, auto_link($in));
	}

	public function autolinkPopup()
	{
		return [
			'test01' => [
				'www.codeigniter.com test',
				'<a href="http://www.codeigniter.com" target="_blank">www.codeigniter.com</a> test',
			],
			'test02' => [
				'This is my noreply@codeigniter.com test',
				'This is my noreply@codeigniter.com test',
			],
			'test03' => [
				'<br />www.google.com',
				'<br /><a href="http://www.google.com" target="_blank">www.google.com</a>',
			],
			'test04' => [
				'Download CodeIgniter at www.codeigniter.com. Period test.',
				'Download CodeIgniter at <a href="http://www.codeigniter.com" target="_blank">www.codeigniter.com</a>. Period test.',
			],
			'test05' => [
				'Download CodeIgniter at www.codeigniter.com, comma test',
				'Download CodeIgniter at <a href="http://www.codeigniter.com" target="_blank">www.codeigniter.com</a>, comma test',
			],
			'test06' => [
				'This one: ://codeigniter.com must not break this one: http://codeigniter.com',
				'This one: <a href="://codeigniter.com" target="_blank">://codeigniter.com</a> must not break this one: <a href="http://codeigniter.com" target="_blank">http://codeigniter.com</a>',
			],
			'test07' => [
				'Visit example.com or email foo@bar.com',
				'Visit example.com or email foo@bar.com',
			],
			'test08' => [
				'Visit www.example.com or email foo@bar.com',
				'Visit <a href="http://www.example.com" target="_blank">www.example.com</a> or email foo@bar.com',
			],
		];
	}

	/**
	 * @dataProvider autolinkPopup
	 */
	public function testAutoLinkPopup($in, $out)
	{
		$this->assertEquals($out, auto_link($in, 'url', true));
	}

	//--------------------------------------------------------------------
	// Test prep_url

	public function prepUrlProvider()
	{
		// input, expected, secure
		return [
			[
				'',
				'',
				false,
			],
			[
				'//',
				'',
				false,
			],
			[
				'//codeigniter.com',
				'http://codeigniter.com',
				false,
			],
			[
				'codeigniter.com',
				'http://codeigniter.com',
				false,
			],
			[
				'www.codeigniter.com',
				'http://www.codeigniter.com',
				false,
			],
			[
				'http://www.codeigniter.com',
				'http://www.codeigniter.com',
				false,
			],
			[
				'https://www.codeigniter.com',
				'https://www.codeigniter.com',
				false,
			],
			[
				'',
				'',
				true,
			],
			[
				'//',
				'',
				true,
			],
			[
				'//codeigniter.com',
				'https://codeigniter.com',
				true,
			],
			[
				'codeigniter.com',
				'https://codeigniter.com',
				true,
			],
			[
				'www.codeigniter.com',
				'https://www.codeigniter.com',
				true,
			],
			[
				'http://www.codeigniter.com',
				'https://www.codeigniter.com',
				true,
			],
			[
				'https://www.codeigniter.com',
				'https://www.codeigniter.com',
				true,
			],
		];
	}

	/**
	 * @dataProvider prepUrlProvider
	 */
	public function testPrepUrl(string $input, string $expected, bool $secure)
	{
		$this->assertSame($expected, prep_url($input, $secure));
	}

	//--------------------------------------------------------------------
	// Test url_title

	public function testUrlTitle()
	{
		$words = [
			'foo bar /'       => 'foo-bar',
			'\  testing 12'   => 'testing-12',
			'Éléphant de PHP' => 'éléphant-de-php',
		];

		foreach ($words as $in => $out)
		{
			$this->assertEquals($out, url_title($in, '-', true));
		}
	}

	public function testUrlTitleExtraDashes()
	{
		$words = [
			'_foo bar_'                 => 'foo_bar',
			'_What\'s wrong with CSS?_' => 'Whats_wrong_with_CSS',
			'Éléphant de PHP'           => 'Éléphant_de_PHP',
		];

		foreach ($words as $in => $out)
		{
			$this->assertEquals($out, url_title($in, '_'));
		}
	}

	//--------------------------------------------------------------------
	// Test mb_url_title

	public function testMbUrlTitle()
	{
		$words = [
			'foo bar /'       => 'foo-bar',
			'\  testing 12'   => 'testing-12',
			'Éléphant de PHP' => 'elephant-de-php',
			'ä ö ü Ĝ β ę'     => 'ae-oe-ue-g-v-e',
		];

		foreach ($words as $in => $out)
		{
			$this->assertEquals($out, mb_url_title($in, '-', true));
		}
	}

	public function testMbUrlTitleExtraDashes()
	{
		$words = [
			'_foo bar_'                 => 'foo_bar',
			'_What\'s wrong with CSS?_' => 'Whats_wrong_with_CSS',
			'Éléphant de PHP'           => 'Elephant_de_PHP',
			'ä ö ü Ĝ β ę'               => 'ae_oe_ue_G_v_e',
		];

		foreach ($words as $in => $out)
		{
			$this->assertEquals($out, mb_url_title($in, '_'));
		}
	}

	//--------------------------------------------------------------------

	public function testBasedNoIndex()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/ci/v4/x/y';

		$this->config->baseURL = 'http://example.com/ci/v4/';
		$request               = Services::request($this->config);
		$request->uri          = new URI('http://example.com/ci/v4/x/y');

		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com/ci/v4/index.php/controller/method', site_url('controller/method', null, $this->config));
		$this->assertEquals('http://example.com/ci/v4/controller/method', base_url('controller/method', null, $this->config));
	}

	public function testBasedNoTrailingSlash()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/ci/v4/x/y';

		$this->config->baseURL = 'http://example.com/ci/v4';
		$request               = Services::request($this->config);
		$request->uri          = new URI('http://example.com/ci/v4/x/y');

		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com/ci/v4/index.php/controller/method', site_url('controller/method', null, $this->config));
		$this->assertEquals('http://example.com/ci/v4/controller/method', base_url('controller/method', null, $this->config));
	}

	public function testBasedWithIndex()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/ci/v4/index.php/x/y';

		$this->config->baseURL = 'http://example.com/ci/v4/';
		$request               = Services::request($this->config);
		$request->uri          = new URI('http://example.com/ci/v4/index.php/x/y');

		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com/ci/v4/index.php/controller/method', site_url('controller/method', null, $this->config));
		$this->assertEquals('http://example.com/ci/v4/controller/method', base_url('controller/method', null, $this->config));
	}

	public function testBasedWithoutIndex()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/ci/v4/x/y';

		$this->config->baseURL   = 'http://example.com/ci/v4/';
		$this->config->indexPage = '';
		$request                 = Services::request($this->config);
		$request->uri            = new URI('http://example.com/ci/v4/x/y');

		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com/ci/v4/controller/method', site_url('controller/method', null, $this->config));
		$this->assertEquals('http://example.com/ci/v4/controller/method', base_url('controller/method', null, $this->config));
	}

	public function testBasedWithOtherIndex()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/ci/v4/x/y';

		$this->config->baseURL   = 'http://example.com/ci/v4/';
		$this->config->indexPage = 'fc.php';
		$request                 = Services::request($this->config);
		$request->uri            = new URI('http://example.com/ci/v4/x/y');

		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com/ci/v4/fc.php/controller/method', site_url('controller/method', null, $this->config));
		$this->assertEquals('http://example.com/ci/v4/controller/method', base_url('controller/method', null, $this->config));
	}

	/**
	 * @dataProvider urlToProvider
	 */
	public function testUrlTo(string $expected, string $input, ...$args)
	{
		$_SERVER['HTTP_HOST'] = 'example.com';

		$routes = service('routes');
		$routes->add('path/(:any)/to/(:num)', 'myController::goto/$1/$2', ['as' => 'gotoPage']);
		$routes->add('route/(:any)/to/(:num)', 'myOtherController::goto/$1/$2');

		$this->assertEquals($expected, url_to($input, ...$args));
	}

	/**
	 * @dataProvider urlToMissingRoutesProvider
	 */
	public function testUrlToThrowsOnEmptyOrMissingRoute(string $route)
	{
		$this->expectException(RouterException::class);

		url_to($route);
	}

	public function urlToProvider()
	{
		if (config('App')->indexPage !== '')
		{
			$page = config('App')->indexPage . '/';
		}
		else
		{
			$page = '';
		}

		return [
			[
				"http://example.com/{$page}path/string/to/13",
				'gotoPage',
				'string',
				13,
			],
			[
				"http://example.com/{$page}route/string/to/13",
				'myOtherController::goto',
				'string',
				13,
			],
		];
	}

	public function urlToMissingRoutesProvider()
	{
		return [
			[
				'',
			],
			[
				'Nope::doesNotExist',
			],
		];
	}

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
