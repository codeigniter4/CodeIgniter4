<?php

require_once 'system/Config/BaseConfig.php';
require_once 'application/config/AppConfig.php';
require_once 'system/HTTPLite/URI.php';
require_once 'system/HTTPLite/Request.php';
require_once 'system/HTTPLite/IncomingRequest.php';

use App\Config\AppConfig;
use CodeIgniter\HTTPLite\URI;

class IncomingRequestTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var \CodeIgniter\HTTPLite\IncomingRequest
	 */
	protected $request;

	public function setUp()
	{
	    $this->request = new \CodeIgniter\HTTPLite\IncomingRequest(new AppConfig(), new URI());
	}

	//--------------------------------------------------------------------

	public function testCanGrabGetVars()
	{
	    $_GET['TEST'] = 5;

		$this->assertEquals(5, $this->request->get('TEST'));
		$this->assertEquals(null, $this->request->get('TESTY'));
	}

	//--------------------------------------------------------------------

	public function testCanGrabPostVars()
	{
		$_POST['TEST'] = 5;

		$this->assertEquals(5, $this->request->post('TEST'));
		$this->assertEquals(null, $this->request->post('TESTY'));
	}

	//--------------------------------------------------------------------

	public function testCanGrabPostBeforeGet()
	{
		$_POST['TEST'] = 5;
		$_GET['TEST'] = 3;

		$this->assertEquals(5, $this->request->postGet('TEST'));
		$this->assertEquals(3, $this->request->getPost('TEST'));
	}

	//--------------------------------------------------------------------

}
