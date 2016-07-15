<?php

namespace CodeIgniter\Helpers;

use CodeIgniter\HTTP\URI;
use Config\App;
use CodeIgniter\Services;

class URLHelperTest extends \CIUnitTestCase
{

	public function setUp()
	{
		helper('url');
	}

	//--------------------------------------------------------------------
	// Test site_url

	public function testSiteURLBasics()
	{
		$_SERVER['HTTP_HOST']	 = 'example.com';
		$_SERVER['REQUEST_URI']	 = '/';

		$config				 = new App();
		$config->baseURL	 = '';
		$config->indexPage	 = 'index.php';
		$request			 = Services::request($config);
		$request->uri		 = new URI('http://example.com/');

		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com/index.php/', site_url('', null, $config));
	}

	public function testSiteURLNoIndex()
	{
		$_SERVER['HTTP_HOST']	 = 'example.com';
		$_SERVER['REQUEST_URI']	 = '/';

		$config				 = new App();
		$config->baseURL	 = '';
		$config->indexPage	 = '';
		$request			 = Services::request($config);
		$request->uri		 = new URI('http://example.com/');

		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com/', site_url('', null, $config));
	}

	public function testSiteURLDifferentIndex()
	{
		$_SERVER['HTTP_HOST']	 = 'example.com';
		$_SERVER['REQUEST_URI']	 = '/';

		$config				 = new App();
		$config->baseURL	 = '';
		$config->indexPage	 = 'banana.php';
		$request			 = Services::request($config);
		$request->uri		 = new URI('http://example.com/');

		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com/banana.php/', site_url('', null, $config));
	}

	public function testSiteURLNoIndexButPath()
	{
		$_SERVER['HTTP_HOST']	 = 'example.com';
		$_SERVER['REQUEST_URI']	 = '/';

		$config				 = new App();
		$config->baseURL	 = '';
		$config->indexPage	 = '';
		$request			 = Services::request($config);
		$request->uri		 = new URI('http://example.com/');

		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com/abc', site_url('abc', null, $config));
	}

	public function testSiteURLAttachesPath()
	{
		$_SERVER['HTTP_HOST']	 = 'example.com';
		$_SERVER['REQUEST_URI']	 = '/';

		$config				 = new App();
		$config->baseURL	 = '';
		$config->indexPage	 = 'index.php';
		$request			 = Services::request($config);
		$request->uri		 = new URI('http://example.com/');

		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com/index.php/foo', site_url('foo', null, $config));
	}

	public function testSiteURLAttachesScheme()
	{
		$_SERVER['HTTP_HOST']	 = 'example.com';
		$_SERVER['REQUEST_URI']	 = '/';

		$config				 = new App();
		$config->baseURL	 = '';
		$config->indexPage	 = 'index.php';
		$request			 = Services::request($config);
		$request->uri		 = new URI('http://example.com/');

		Services::injectMock('request', $request);

		$this->assertEquals('ftp://example.com/index.php/foo', site_url('foo', 'ftp', $config));
	}

	public function testSiteURLExample()
	{
		$_SERVER['HTTP_HOST']	 = 'example.com';
		$_SERVER['REQUEST_URI']	 = '/';

		$config				 = new App();
		$config->baseURL	 = '';
		$config->indexPage	 = 'index.php';
		$request			 = Services::request($config);
		$request->uri		 = new URI('http://example.com/');

		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com/index.php/news/local/123', site_url('news/local/123', null, $config));
	}

	public function testSiteURLSegments()
	{
		$_SERVER['HTTP_HOST']	 = 'example.com';
		$_SERVER['REQUEST_URI']	 = '/';

		$config				 = new App();
		$config->baseURL	 = '';
		$config->indexPage	 = 'index.php';
		$request			 = Services::request($config);
		$request->uri		 = new URI('http://example.com/');

		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com/index.php/news/local/123', site_url(['news', 'local', '123'], null, $config));
	}

	//--------------------------------------------------------------------
	// Test base_url

	public function testBaseURLBasics()
	{
		$_SERVER['HTTP_HOST']	 = 'example.com';
		$_SERVER['REQUEST_URI']	 = '/';

		$this->assertEquals('http://example.com/', base_url());
	}

	public function testBaseURLAttachesPath()
	{
		$_SERVER['HTTP_HOST']	 = 'example.com';
		$_SERVER['REQUEST_URI']	 = '/';

		$this->assertEquals('http://example.com/foo', base_url('foo'));
	}

	public function testBaseURLAttachesScheme()
	{
		$_SERVER['HTTP_HOST']	 = 'example.com';
		$_SERVER['REQUEST_URI']	 = '/';

		$this->assertEquals('https://example.com/foo', base_url('foo', 'https'));
	}

	public function testBaseURLHeedsBaseURL()
	{
		$_SERVER['HTTP_HOST']	 = 'example.com';
		$_SERVER['REQUEST_URI']	 = '/';

		// Since we're on a CLI, we must provide our own URI
		$config			 = new App();
		$config->baseURL = 'http://example.com/public';
		$request		 = Services::request($config);
		$request->uri	 = new URI('http://example.com/public');

		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com/public', base_url());
	}

	public function testBaseURLExample()
	{
		$_SERVER['HTTP_HOST']	 = 'example.com';
		$_SERVER['REQUEST_URI']	 = '/';

		$this->assertEquals('http://example.com/blog/post/123', base_url('blog/post/123'));
	}

	//--------------------------------------------------------------------
	// Test current_url

	public function testCurrentURLReturnsBasicURL()
	{
		$_SERVER['HTTP_HOST']	 = 'example.com';
		$_SERVER['REQUEST_URI']	 = '/';

		// Since we're on a CLI, we must provide our own URI
		$config			 = new App();
		$config->baseURL = 'http://example.com/public';
		$request		 = Services::request($config);
		$request->uri	 = new URI('http://example.com/public');

		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com/public', current_url());
	}

	public function testCurrentURLReturnsObject()
	{
		$_SERVER['HTTP_HOST']	 = 'example.com';
		$_SERVER['REQUEST_URI']	 = '/';

		// Since we're on a CLI, we must provide our own URI
		$config			 = new App();
		$config->baseURL = 'http://example.com/public';
		$request		 = Services::request($config);
		$request->uri	 = new URI('http://example.com/public');

		Services::injectMock('request', $request);

		$url = current_url(true);

		$this->assertTrue($url instanceof URI);
		$this->assertEquals('http://example.com/public', (string) $url);
	}

	public function testCurrentURLEquivalence()
	{
		$_SERVER['HTTP_HOST']	 = 'example.com';
		$_SERVER['REQUEST_URI']	 = '/';

		// Since we're on a CLI, we must provide our own URI
		$config			 = new App();
		$config->baseURL = 'http://example.com/';
		$request		 = Services::request($config);
		$request->uri	 = new URI('http://example.com/public');

		Services::injectMock('request', $request);

		$this->assertEquals(base_url(uri_string()), current_url());
	}

	//--------------------------------------------------------------------
	// Test uri_string

	public function testUriString()
	{
		$_SERVER['HTTP_HOST']	 = 'example.com';
		$_SERVER['REQUEST_URI']	 = '/';

		$config				 = new App();
		$config->baseURL	 = '';
		$config->indexPage	 = 'index.php';
		$request			 = Services::request($config);
		$request->uri		 = new URI('http://example.com/');

		Services::injectMock('request', $request);

		$url = current_url();
		$this->assertEquals('/', uri_string());
	}

	public function testUriStringExample()
	{

		$_SERVER['HTTP_HOST']	 = 'example.com';
		$_SERVER['REQUEST_URI']	 = '/assets/image.jpg';

		$config				 = new App();
		$config->baseURL	 = '';
		$config->indexPage	 = 'index.php';
		$request			 = Services::request($config);
		$request->uri		 = new URI('http://example.com/assets/image.jpg');

		Services::injectMock('request', $request);

		$url = current_url();
		$this->assertEquals('/assets/image.jpg', uri_string());
	}

	//--------------------------------------------------------------------
	// Test index_page

	public function testIndexPage()
	{
		$config				 = new App();
		$config->baseURL	 = '';
		$config->indexPage	 = 'index.php';
		$request			 = Services::request($config);
		$request->uri		 = new URI('http://example.com/');

		Services::injectMock('request', $request);

		$this->assertEquals('index.php', index_page());
	}

	public function testIndexPageAlt()
	{
		$config				 = new App();
		$config->baseURL	 = '';
		$config->indexPage	 = 'banana.php';
		$request			 = Services::request($config);
		$request->uri		 = new URI('http://example.com/');

		Services::injectMock('request', $request);

		$this->assertEquals('banana.php', index_page($config));
	}

	//--------------------------------------------------------------------
	// Test anchor

	public function anchorNormalPatterns()
	{
		return [
			'normal01'	 => ['<a href="http://example.com/index.php">http://example.com/index.php</a>', ''],
			'normal02'	 => ['<a href="http://example.com/index.php">Bananas</a>', '/', 'Bananas'],
			'normal03'	 => ['<a href="http://example.com/index.php" fruit="peach">http://example.com/index.php</a>', '/', '', 'fruit="peach"'],
			'normal04'	 => ['<a href="http://example.com/index.php" fruit=peach>Bananas</a>', '/', 'Bananas', 'fruit=peach'],
			'normal05'	 => ['<a href="http://example.com/index.php" fruit="peach">http://example.com/index.php</a>', '/', '', ['fruit' => 'peach']],
			'normal06'	 => ['<a href="http://example.com/index.php" fruit="peach">Bananas</a>', '/', 'Bananas', ['fruit' => 'peach']],
			'normal07'	 => ['<a href="http://example.com/index.php">http://example.com/index.php</a>', '/'],
		];
	}

	/**
	 * @dataProvider anchorNormalPatterns
	 */
	public function testAnchor($expected = '', $uri = '', $title = '', $attributes = '')
	{
		$_SERVER['HTTP_HOST']	 = 'example.com';
		$_SERVER['REQUEST_URI']	 = '/';

		$config				 = new App();
		$config->baseURL	 = '';
		$config->indexPage	 = 'index.php';
		$request			 = Services::request($config);
		$request->uri		 = new URI('http://example.com/');

		Services::injectMock('request', $request);
		$this->assertEquals($expected, anchor($uri, $title, $attributes, $config));
	}

	public function anchorNoindexPatterns()
	{
		return [
			'noindex01'	 => ['<a href="http://example.com">http://example.com</a>', ''],
			'noindex02'	 => ['<a href="http://example.com">Bananas</a>', '', 'Bananas'],
			'noindex03'	 => ['<a href="http://example.com" fruit="peach">http://example.com</a>', '', '', 'fruit="peach"'],
			'noindex04'	 => ['<a href="http://example.com" fruit=peach>Bananas</a>', '', 'Bananas', 'fruit=peach'],
			'noindex05'	 => ['<a href="http://example.com" fruit="peach">http://example.com</a>', '', '', ['fruit' => 'peach']],
			'noindex06'	 => ['<a href="http://example.com" fruit="peach">Bananas</a>', '', 'Bananas', ['fruit' => 'peach']],
			'noindex07'	 => ['<a href="http://example.com">http://example.com</a>', '/'],
		];
	}

	/**
	 * @dataProvider anchorNoindexPatterns
	 */
	public function testAnchorNoindex($expected = '', $uri = '', $title = '', $attributes = '')
	{
		$_SERVER['HTTP_HOST']	 = 'example.com';
		$_SERVER['REQUEST_URI']	 = '/';

		$config				 = new App();
		$config->baseURL	 = '';
		$config->indexPage	 = '';
		$request			 = Services::request($config);
		$request->uri		 = new URI('http://example.com/');

		Services::injectMock('request', $request);
		$this->assertEquals($expected, anchor($uri, $title, $attributes, $config));
	}

	public function anchorSubpagePatterns()
	{
		return [
			'subpage01'	 => ['<a href="http://example.com/mush">http://example.com/mush</a>', '/mush'],
			'subpage02'	 => ['<a href="http://example.com/mush">Bananas</a>', '/mush', 'Bananas'],
			'subpage03'	 => ['<a href="http://example.com/mush" fruit="peach">http://example.com/mush</a>', '/mush', '', 'fruit="peach"'],
			'subpage04'	 => ['<a href="http://example.com/mush" fruit=peach>Bananas</a>', '/mush', 'Bananas', 'fruit=peach'],
			'subpage05'	 => ['<a href="http://example.com/mush" fruit="peach">http://example.com/mush</a>', '/mush', '', ['fruit' => 'peach']],
			'subpage06'	 => ['<a href="http://example.com/mush" fruit="peach">Bananas</a>', '/mush', 'Bananas', ['fruit' => 'peach']],
		];
	}

	/**
	 * @dataProvider anchorSubpagePatterns
	 */
	public function testAnchorTargetted($expected = '', $uri = '', $title = '', $attributes = '')
	{
		$_SERVER['HTTP_HOST']	 = 'example.com';
		$_SERVER['REQUEST_URI']	 = '/';

		$config				 = new App();
		$config->baseURL	 = '';
		$config->indexPage	 = '';
		$request			 = Services::request($config);
		$request->uri		 = new URI('http://example.com/');

		Services::injectMock('request', $request);
		$this->assertEquals($expected, anchor($uri, $title, $attributes, $config));
	}

	public function anchorExamplePatterns()
	{
		return [
			'egpage01'	 => ['<a href="http://example.com/index.php/news/local/123" title="News title">My News</a>', 'news/local/123', 'My News', 'title="News title"'],
			'egpage02'	 => ['<a href="http://example.com/index.php/news/local/123" title="The best news!">My News</a>', 'news/local/123', 'My News', array ('title' => 'The best news!')],
			'egpage03'	 => ['<a href="http://example.com/index.php">Click here</a>', '', 'Click here'],
			'egpage04'	 => ['<a href="http://example.com/index.php">Click here</a>', '/', 'Click here'],
		];
	}

	/**
	 * @dataProvider anchorExamplePatterns
	 */
	public function testAnchorExamples($expected = '', $uri = '', $title = '', $attributes = '')
	{
		$_SERVER['HTTP_HOST']	 = 'example.com';
		$_SERVER['REQUEST_URI']	 = '/';

		$config				 = new App();
		$config->baseURL	 = '';
		$config->indexPage	 = 'index.php';
		$request			 = Services::request($config);
		$request->uri		 = new URI('http://example.com/');

		Services::injectMock('request', $request);
		$this->assertEquals($expected, anchor($uri, $title, $attributes, $config));
	}

	//--------------------------------------------------------------------
	// Test anchor_popup

	public function anchorPopupPatterns()
	{
		return [
			'normal01'	 => ['<a href="http://example.com/index.php" onclick="window.open(\'http://example.com/index.php\', \'_blank\'); return false;">http://example.com/index.php</a>', ''],
			'normal02'	 => ['<a href="http://example.com/index.php" onclick="window.open(\'http://example.com/index.php\', \'_blank\'); return false;">Bananas</a>', '/', 'Bananas'],
			'normal07'	 => ['<a href="http://example.com/index.php" onclick="window.open(\'http://example.com/index.php\', \'_blank\'); return false;">http://example.com/index.php</a>', '/'],
			'normal08'	 => ['<a href="http://example.com/index.php/news/local/123" onclick="window.open(\'http://example.com/index.php/news/local/123\', \'_blank\', \'width=800,height=600,scrollbars=yes,menubar=no,status=yes,resizable=yes,screenx=0,screeny=0\'); return false;">Click Me!</a>',
				'news/local/123', 'Click Me!', array (
					'width'			 => 800,
					'height'		 => 600,
					'scrollbars'	 => 'yes',
					'status'		 => 'yes',
					'resizable'		 => 'yes',
					'screenx'		 => 0,
					'screeny'		 => 0,
					'window_name'	 => '_blank'
				)],
			'normal09'	 => [
				'<a href="http://example.com/index.php/news/local/123" onclick="window.open(\'http://example.com/index.php/news/local/123\', \'_blank\', \'width=800,height=600,scrollbars=yes,menubar=no,status=yes,resizable=yes,screenx=0,screeny=0\'); return false;">Click Me!</a>',
				'news/local/123',
				'Click Me!',
				array ()],
		];
	}

	/**
	 * @dataProvider anchorPopupPatterns
	 */
	public function testAnchorPopup($expected = '', $uri = '', $title = '', $attributes = false)
	{
		$_SERVER['HTTP_HOST']	 = 'example.com';
		$_SERVER['REQUEST_URI']	 = '/';

		$config				 = new App();
		$config->baseURL	 = '';
		$config->indexPage	 = 'index.php';
		$request			 = Services::request($config);
		$request->uri		 = new URI('http://example.com/');

		Services::injectMock('request', $request);
		$this->assertEquals($expected, anchor_popup($uri, $title, $attributes, $config));
	}

	//--------------------------------------------------------------------
	// Test mailto
	//--------------------------------------------------------------------
	// Test safe_mailto
	//--------------------------------------------------------------------
	// Test auto_link

	public function testAutoLinkUrl()
	{
		$strings = array (
			'www.codeigniter.com test'														 => '<a href="http://www.codeigniter.com">www.codeigniter.com</a> test',
			'This is my noreply@codeigniter.com test'										 => 'This is my noreply@codeigniter.com test',
			'<br />www.google.com'															 => '<br /><a href="http://www.google.com">www.google.com</a>',
			'Download CodeIgniter at www.codeigniter.com. Period test.'						 => 'Download CodeIgniter at <a href="http://www.codeigniter.com">www.codeigniter.com</a>. Period test.',
			'Download CodeIgniter at www.codeigniter.com, comma test'						 => 'Download CodeIgniter at <a href="http://www.codeigniter.com">www.codeigniter.com</a>, comma test',
			'This one: ://codeigniter.com must not break this one: http://codeigniter.com'	 => 'This one: <a href="://codeigniter.com">://codeigniter.com</a> must not break this one: <a href="http://codeigniter.com">http://codeigniter.com</a>'
		);

		foreach ($strings as $in => $out)
		{
			$this->assertEquals($out, auto_link($in, 'url'));
		}
	}

	public function testPull675()
	{
		$strings = array (
			'<br />www.google.com' => '<br /><a href="http://www.google.com">www.google.com</a>',
		);

		foreach ($strings as $in => $out)
		{
			$this->assertEquals($out, auto_link($in, 'url'));
		}
	}

	//--------------------------------------------------------------------
	// Test prep_url

	public function testPrepUrl()
	{
		$this->assertEquals('http://codeigniter.com', prep_url('codeigniter.com'));
		$this->assertEquals('http://www.codeigniter.com', prep_url('www.codeigniter.com'));
	}

	//--------------------------------------------------------------------
	// Test url_title

	public function testUrlTitle()
	{
		$words = array (
			'foo bar /'		 => 'foo-bar',
			'\  testing 12'	 => 'testing-12'
		);

		foreach ($words as $in => $out)
		{
			$this->assertEquals($out, url_title($in, 'dash', TRUE));
		}
	}

	public function testUrlTitleExtraDashes()
	{
		$words = array (
			'_foo bar_'					 => 'foo_bar',
			'_What\'s wrong with CSS?_'	 => 'Whats_wrong_with_CSS'
		);

		foreach ($words as $in => $out)
		{
			$this->assertEquals($out, url_title($in, 'underscore'));
		}
	}

}
