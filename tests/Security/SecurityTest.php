<?php

use CodeIgniter\Security\Security;

require_once dirname(__FILE__) .'/../_support/Config/MockAppConfig.php';

class MockSecurity extends Security
{
	public function CSRFSetCookie(\CodeIgniter\HTTP\RequestInterface $request)
	{
		$_COOKIE['csrf_cookie_name'] = $this->CSRFHash;

	    return $this;
	}

	//--------------------------------------------------------------------


}

//--------------------------------------------------------------------

class SecurityTest extends PHPUnit_Framework_TestCase {

	public function setUp()
	{
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
			'csrf_cookie_name' => '8b9218a55906f9dcc1dc263dce7f005a'
		];

		$security = new Security(new MockAppConfig());

		$this->assertEquals('8b9218a55906f9dcc1dc263dce7f005a', $security->getCSRFHash());
	}

	//--------------------------------------------------------------------

	public function testCSRFVerifySetsCookieWhenNotPOST()
	{
		$security = new MockSecurity(new MockAppConfig());

		$_SERVER['REQUEST_METHOD'] = 'GET';

		$security->CSRFVerify(new \CodeIgniter\HTTP\Request(new MockAppConfig()));

		$this->assertEquals($_COOKIE['csrf_cookie_name'], $security->getCSRFHash());
	}

	//--------------------------------------------------------------------


}