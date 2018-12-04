<?php namespace CodeIgniter\Security;

use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\HTTP\IncomingRequest;
use Tests\Support\Config\MockAppConfig;
use CodeIgniter\Security\Exceptions\SecurityException;
use Tests\Support\Security\MockSecurity;

//--------------------------------------------------------------------

/**
 * @backupGlobals enabled
 */
class SecurityTest extends \CIUnitTestCase {

	protected function setUp()
	{
		parent::setUp();

		$_COOKIE = [];
	}

	//--------------------------------------------------------------------

	public function testBasicConfigIsSaved()
	{
		$security = new Security(new MockAppConfig());

		$hash = $security->getCSRFHash();

		$this->assertEquals(32, strlen($hash));
		$this->assertEquals('csrf_test_name', $security->getCSRFTokenName());
	}

	//--------------------------------------------------------------------

	public function testHashIsReadFromCookie()
	{
		$_COOKIE = [
			'csrf_cookie_name' => '8b9218a55906f9dcc1dc263dce7f005a',
		];

		$security = new Security(new MockAppConfig());

		$this->assertEquals('8b9218a55906f9dcc1dc263dce7f005a', $security->getCSRFHash());
	}

	//--------------------------------------------------------------------

	public function testCSRFVerifySetsCookieWhenNotPOST()
	{
		$security = new MockSecurity(new MockAppConfig());

		$_SERVER['REQUEST_METHOD'] = 'GET';

		$security->CSRFVerify(new Request(new MockAppConfig()));

		$this->assertEquals($_COOKIE['csrf_cookie_name'], $security->getCSRFHash());
	}

	//--------------------------------------------------------------------

	public function testCSRFVerifyThrowsExceptionOnNoMatch()
	{
		$security = new MockSecurity(new MockAppConfig());
		$request  = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());

		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST['csrf_test_name']   = '8b9218a55906f9dcc1dc263dce7f005a';
		$_COOKIE                   = [
			'csrf_cookie_name' => '8b9218a55906f9dcc1dc263dce7f005b',
		];

		$this->expectException(SecurityException::class);
		$security->CSRFVerify($request);
	}

	//--------------------------------------------------------------------

	public function testCSRFVerifyReturnsSelfOnMatch()
	{
		$security = new MockSecurity(new MockAppConfig());
		$request  = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());

		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST['csrf_test_name']   = '8b9218a55906f9dcc1dc263dce7f005a';
		$_COOKIE                   = [
			'csrf_cookie_name' => '8b9218a55906f9dcc1dc263dce7f005a',
		];

		$this->assertInstanceOf('CodeIgniter\Security\Security', $security->CSRFVerify($request));
		$this->assertLogged('info', 'CSRF token verified');
	}

	//--------------------------------------------------------------------

	public function testSanitizeFilename()
	{
		$security = new MockSecurity(new MockAppConfig());

		$filename = './<!--foo-->';

		$this->assertEquals('foo', $security->sanitizeFilename($filename));
	}

	//--------------------------------------------------------------------

}
