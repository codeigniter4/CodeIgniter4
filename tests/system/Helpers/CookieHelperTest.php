<?php
namespace CodeIgniter\Helpers;

use Config\App;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use Tests\Support\HTTP\MockResponse;

final class CookieHelperTest extends \CIUnitTestCase
{

	private $name;
	private $value;
	private $expire;
	private $response;

	protected function setUp()
	{
		parent::setUp();

		$this->name   = 'greetings';
		$this->value  = 'hello world';
		$this->expire = 9999;

		Services::injectMock('response', new MockResponse(new App()));
		$this->response = service('response');
		$this->request  = new IncomingRequest(new App(), new URI(), null, new UserAgent());
		Services::injectMock('request', $this->request);

		helper('cookie');
	}

	//--------------------------------------------------------------------

	public function testSetCookie()
	{
		set_cookie($this->name, $this->value, $this->expire);

		$this->assertTrue($this->response->hasCookie($this->name));

		delete_cookie($this->name);
	}

	//--------------------------------------------------------------------

	public function testSetCookieByArrayParameters()
	{
		$cookieAttr = [
			'name'   => $this->name,
			'value'  => $this->value,
			'expire' => $this->expire,
		];
		set_cookie($cookieAttr);

		$this->assertTrue($this->response->hasCookie($this->name, $this->value));

		delete_cookie($this->name);
	}

	//--------------------------------------------------------------------

	public function testSetCookieSecured()
	{
		$pre       = 'Hello, I try to';
		$pst       = 'your site';
		$unsec     = "$pre <script>alert('Hack');</script> $pst";
		$sec       = "$pre [removed]alert&#40;&#39;Hack&#39;&#41;;[removed] $pst";
		$unsecured = 'unsecured';
		$secured   = 'secured';

		set_cookie($unsecured, $unsec, $this->expire);
		set_cookie($secured, $sec, $this->expire);

		$this->assertTrue($this->response->hasCookie($unsecured, $unsec));
		$this->assertTrue($this->response->hasCookie($secured, $sec));

		delete_cookie($unsecured);
		delete_cookie($secured);
	}

	//--------------------------------------------------------------------

	public function testDeleteCookie()
	{
		$this->response->setCookie($this->name, $this->value, $this->expire);

		delete_cookie($this->name);

		$cookie = $this->response->getCookie($this->name);

		// The cookie is set to be cleared when the request is sent....
		$this->assertEquals('', $cookie['value']);
		$this->assertEquals('', $cookie['expires']);
	}

	//--------------------------------------------------------------------

	public function testGetCookie()
	{
		$_COOKIE['TEST'] = 5;

		$this->assertEquals(5, get_cookie('TEST'));
	}

}
