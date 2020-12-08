<?php

namespace CodeIgniter\Security;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Security\Exceptions\SecurityException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockAppConfig;
use CodeIgniter\Test\Mock\MockSecurityConfig;
use CodeIgniter\Test\Mock\MockSecurity;

/**
 * @backupGlobals enabled
 */
class SecurityTest extends CIUnitTestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		$_COOKIE = [];
	}

	//--------------------------------------------------------------------

	public function testBasicConfigIsSaved()
	{
		$security = new Security(new MockSecurityConfig());

		$hash = $security->getHash();

		$this->assertEquals(32, strlen($hash));
		$this->assertEquals('csrf_test_name', $security->getTokenName());
	}

	//--------------------------------------------------------------------

	public function testHashIsReadFromCookie()
	{
		$_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005a';

		$security = new Security(new MockSecurityConfig());

		$this->assertEquals('8b9218a55906f9dcc1dc263dce7f005a', $security->getHash());
	}

	//--------------------------------------------------------------------

	public function testCSRFVerifySetsCookieWhenNotPOST()
	{
		$security = new MockSecurity(new MockSecurityConfig());

		$_SERVER['REQUEST_METHOD'] = 'GET';

		$security->verify(new Request(new MockAppConfig()));

		$this->assertEquals($_COOKIE['csrf_cookie_name'], $security->getHash());
	}

	//--------------------------------------------------------------------

	public function testCSRFVerifyPostThrowsExceptionOnNoMatch()
	{
		$security = new MockSecurity(new MockSecurityConfig());
		$request  = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());

		$_SERVER['REQUEST_METHOD']   = 'POST';
		$_POST['csrf_test_name']     = '8b9218a55906f9dcc1dc263dce7f005a';
		$_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005b';

		$this->expectException('CodeIgniter\Security\Exceptions\SecurityException');
		$security->verify($request);
	}

	//--------------------------------------------------------------------

	public function testCSRFVerifyPostReturnsSelfOnMatch()
	{
		$security = new MockSecurity(new MockSecurityConfig());
		$request  = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());

		$_SERVER['REQUEST_METHOD']   = 'POST';
		$_POST['foo']                = 'bar';
		$_POST['csrf_test_name']     = '8b9218a55906f9dcc1dc263dce7f005a';
		$_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005a';

		$this->assertInstanceOf('CodeIgniter\Security\Security', $security->verify($request));
		$this->assertLogged('info', 'CSRF token verified.');

		$this->assertTrue(count($_POST) === 1);
	}

	//--------------------------------------------------------------------

	public function testCSRFVerifyHeaderThrowsExceptionOnNoMatch()
	{
		$security = new MockSecurity(new MockSecurityConfig());
		$request  = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());

		$request->setHeader('X-CSRF-TOKEN', '8b9218a55906f9dcc1dc263dce7f005a');

		$_SERVER['REQUEST_METHOD']   = 'POST';
		$_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005b';

		$this->expectException('CodeIgniter\Security\Exceptions\SecurityException');
		$security->verify($request);
	}

	//--------------------------------------------------------------------

	public function testCSRFVerifyHeaderReturnsSelfOnMatch()
	{
		$security = new MockSecurity(new MockSecurityConfig());
		$request  = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());

		$request->setHeader('X-CSRF-TOKEN', '8b9218a55906f9dcc1dc263dce7f005a');

		$_SERVER['REQUEST_METHOD']   = 'POST';
		$_POST['foo']                = 'bar';
		$_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005a';

		$this->assertInstanceOf('CodeIgniter\Security\Security', $security->verify($request));
		$this->assertLogged('info', 'CSRF token verified.');

		$this->assertTrue(count($_POST) === 1);
	}

	//--------------------------------------------------------------------

	public function testCSRFVerifyJsonThrowsExceptionOnNoMatch()
	{
		$security = new MockSecurity(new MockSecurityConfig());
		$request  = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());

		$request->setBody('{"csrf_test_name":"8b9218a55906f9dcc1dc263dce7f005a"}');

		$_SERVER['REQUEST_METHOD']   = 'POST';
		$_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005b';

		$this->expectException('CodeIgniter\Security\Exceptions\SecurityException');
		$security->verify($request);
	}

	//--------------------------------------------------------------------

	public function testCSRFVerifyJsonReturnsSelfOnMatch()
	{
		$security = new MockSecurity(new MockSecurityConfig());
		$request  = new IncomingRequest(new MockAppConfig(), new URI('http://badurl.com'), null, new UserAgent());

		$request->setBody('{"csrf_test_name":"8b9218a55906f9dcc1dc263dce7f005a","foo":"bar"}');

		$_SERVER['REQUEST_METHOD']   = 'POST';
		$_COOKIE['csrf_cookie_name'] = '8b9218a55906f9dcc1dc263dce7f005a';

		$this->assertInstanceOf('CodeIgniter\Security\Security', $security->verify($request));
		$this->assertLogged('info', 'CSRF token verified.');

		$this->assertTrue($request->getBody() === '{"foo":"bar"}');
	}

	//--------------------------------------------------------------------

	public function testSanitizeFilename()
	{
		$security = new MockSecurity(new MockSecurityConfig());

		$filename = './<!--foo-->';

		$this->assertEquals('foo', $security->sanitizeFilename($filename));
	}
}
