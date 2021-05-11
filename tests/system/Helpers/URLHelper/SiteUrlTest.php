<?php

namespace CodeIgniter\Helpers\URLHelper;

use CodeIgniter\Config\Factories;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Router\Exceptions\RouterException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;

final class SiteUrlTest extends CIUnitTestCase
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
				'http://example.com/',
				'index.php',
				'0',
				null,
				'http://example.com/index.php/0',
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
}
