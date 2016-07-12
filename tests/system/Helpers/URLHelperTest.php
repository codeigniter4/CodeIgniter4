<?php namespace CodeIgniter\Helpers;

use CodeIgniter\HTTP\URI;
use Config\App;
use CodeIgniter\Services;

class URLHelperTest extends \CIUnitTestCase
{
	public function __construct()
	{
	    parent::__construct();

		helper('url');
	}

	//--------------------------------------------------------------------

	public function testCurrentURLReturnsBasicURL()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/';

		$this->assertEquals('http://example.com/', current_url());
	}

	//--------------------------------------------------------------------

	public function testCurrentURLReturnsObject()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/';

		$url = current_url(true);

		$this->assertTrue($url instanceof URI);
		$this->assertEquals('http://example.com/', (string)$url);
	}

	//--------------------------------------------------------------------

	public function testBaseURLBasics()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/';

		$this->assertEquals('http://example.com/', base_url());
	}

	//--------------------------------------------------------------------

	public function testBaseURLAttachesPath()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/';

		$this->assertEquals('http://example.com/foo', base_url('foo'));
	}

	//--------------------------------------------------------------------

	public function testBaseURLAttachesScheme()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/';

		$this->assertEquals('https://example.com/foo', base_url('foo', 'https'));
	}

	//--------------------------------------------------------------------

	public function testBaseURLHeedsBaseURL()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/';

		// Since we're on a CLI, we must provide our own URI
		$config = new App();
		$config->baseURL = 'http://example.com/public';
		$request = Services::request($config);
		$request->uri = new URI('http://example.com/public');

		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com/public', base_url());
	}

	//--------------------------------------------------------------------

	public function testSiteURLBasics()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/';

		$config = new App();
		$config->baseURL = '';
		$config->indexPage = 'index.php';
		$request = Services::request($config);
		$request->uri = new URI('http://example.com/');

		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com/index.php/', site_url());
	}

	//--------------------------------------------------------------------

	public function testSiteURLAttachesPath()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/';

		$config = new App();
		$config->baseURL = '';
		$config->indexPage = 'index.php';
		$request = Services::request($config);
		$request->uri = new URI('http://example.com/');

		Services::injectMock('request', $request);

		$this->assertEquals('http://example.com/index.php/foo', site_url('foo'));
	}

	//--------------------------------------------------------------------

	public function testSiteURLAttachesScheme()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/';

		$config = new App();
		$config->baseURL = '';
		$config->indexPage = 'index.php';
		$request = Services::request($config);
		$request->uri = new URI('http://example.com/');

		Services::injectMock('request', $request);

		$this->assertEquals('ftp://example.com/index.php/foo', site_url('foo', 'ftp'));
	}

	//--------------------------------------------------------------------

}