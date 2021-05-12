<?php

namespace CodeIgniter\Helpers\URLHelper;

use CodeIgniter\Config\Factories;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Router\Exceptions\RouterException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;

/**
 * @backupGlobals enabled
 */
final class BaseUrlTest extends CIUnitTestCase
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
	}

	public function tearDown(): void
	{
		parent::tearDown();

		$_SERVER = [];
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

	public function testBaseURLPathZero()
	{
		$this->assertEquals('http://example.com/0', base_url('0'));
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
}
