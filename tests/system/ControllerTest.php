<?php namespace CodeIgniter;

use CodeIgniter\HTTP;
use Config\App;

/**
 * Exercise our core Controller class.
 * Not a lot of business logic, so concentrate on making sure
 * we can exercise everything without blowing up :-/
 * 
 * @backupGlobals enabled
 */
class ControllerTest extends \CIUnitTestCase
{

	/**
	 * @var \CodeIgniter\CodeIgniter
	 */
	protected $codeigniter;

	/**
	 * @var \CodeIgniter\Controller
	 */
	protected $controller;

	/**
	 * Current request.
	 * @var \CodeIgniter\HTTP\Request
	 */
	protected $request;

	/**
	 * Current response.
	 * @var \CodeIgniter\HTTP\Response
	 */
	protected $response;

	//--------------------------------------------------------------------

	public function setUp()
	{
		Services::reset();

		$this->config = new App();
		$this->request = new \CodeIgniter\HTTP\IncomingRequest($this->config, new \CodeIgniter\HTTP\URI('https://somwhere.com'));
		$this->response = new \CodeIgniter\HTTP\Response($this->config);
		$this->codeigniter = new MockCodeIgniter($this->config);
	}

	//--------------------------------------------------------------------

	public function testConstructor()
	{
		// make sure we can instantiate one
		$this->controller = new Controller($this->request, $this->response);
		$this->assertTrue($this->controller instanceof Controller);
	}

	public function testConstructorHTTPS()
	{
		$original = $_SERVER;
		$_SERVER = ['HTTPS' => 'on'];
		// make sure we can instantiate one
		$this->controller = new Class($this->request, $this->response) extends Controller
		{

			protected $forceHTTPS = 1;
		};
		$this->assertTrue($this->controller instanceof Controller);
		$_SERVER = $original; // restore so code coverage doesn't break
	}

	//--------------------------------------------------------------------
	public function testCachePage()
	{
		$this->controller = new Controller($this->request, $this->response);
		$this->assertNull($this->controller->cachePage(10));
	}

	public function testValidate()
	{
		// make sure we can instantiate one
		$this->controller = new Controller($this->request, $this->response);
		// and that we can attempt validation, with no rules
		$this->assertFalse($this->controller->validate([]));
	}

	//--------------------------------------------------------------------
	public function testHelpers()
	{
		$this->controller = new Class($this->request, $this->response) extends Controller
		{

			protected $helpers = ['cookie', 'text'];
		};
		$this->assertTrue($this->controller instanceof Controller);
	}

}
