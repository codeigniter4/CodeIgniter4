<?php

use App\Config\AppConfig;
use CodeIgniter\HTTP\URI;

class IncomingRequestTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var \CodeIgniter\HTTP\IncomingRequest
	 */
	protected $request;

	public function setUp()
	{
	    $this->request = new \CodeIgniter\HTTP\IncomingRequest(new AppConfig(), new URI());
	}

	//--------------------------------------------------------------------

	public function testCanGrabGetVars()
	{
	    $_GET['TEST'] = 5;

		$this->assertEquals(5, $this->request->getGet('TEST'));
		$this->assertEquals(null, $this->request->getGEt('TESTY'));
	}

	//--------------------------------------------------------------------

	public function testCanGrabPostVars()
	{
		$_POST['TEST'] = 5;

		$this->assertEquals(5, $this->request->getPost('TEST'));
		$this->assertEquals(null, $this->request->getPost('TESTY'));
	}

	//--------------------------------------------------------------------

	public function testCanGrabPostBeforeGet()
	{
		$_POST['TEST'] = 5;
		$_GET['TEST'] = 3;

		$this->assertEquals(5, $this->request->getPostGet('TEST'));
		$this->assertEquals(3, $this->request->getGetPost('TEST'));
	}

	//--------------------------------------------------------------------

}
