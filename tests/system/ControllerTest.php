<?php namespace CodeIgniter;

use CodeIgniter\Log\Logger;
use Config\App;
use CodeIgniter\HTTP\UserAgent;
use Tests\Support\MockCodeIgniter;

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
	 *
	 * @var \CodeIgniter\HTTP\Request
	 */
	protected $request;

	/**
	 * Current response.
	 *
	 * @var \CodeIgniter\HTTP\Response
	 */
	protected $response;
	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	//--------------------------------------------------------------------

	protected function setUp(): void
	{
		parent::setUp();

		$this->config      = new App();
		$this->request     = new \CodeIgniter\HTTP\IncomingRequest($this->config, new \CodeIgniter\HTTP\URI('https://somwhere.com'), null, new UserAgent());
		$this->response    = new \CodeIgniter\HTTP\Response($this->config);
		$this->logger      = \Config\Services::logger();
		$this->codeigniter = new MockCodeIgniter($this->config);
	}

	//--------------------------------------------------------------------

	public function testConstructor()
	{
		// make sure we can instantiate one
		$this->controller = new Controller();
		$this->controller->initController($this->request, $this->response, $this->logger);
		$this->assertInstanceOf(Controller::class, $this->controller);
	}

	public function testConstructorHTTPS()
	{
		$original = $_SERVER;
		$_SERVER  = ['HTTPS' => 'on'];
		// make sure we can instantiate one
		$this->controller = new Class() extends Controller
		{
			protected $forceHTTPS = 1;
		};
		$this->controller->initController($this->request, $this->response, $this->logger);

		$this->assertInstanceOf(Controller::class, $this->controller);
		$_SERVER = $original; // restore so code coverage doesn't break
	}

	//--------------------------------------------------------------------
	public function testCachePage()
	{
		$this->controller = new Controller();
		$this->controller->initController($this->request, $this->response, $this->logger);

		$method = $this->getPrivateMethodInvoker($this->controller, 'cachePage');
		$this->assertNull($method(10));
	}

	public function testValidate()
	{
		// make sure we can instantiate one
		$this->controller = new Controller();
		$this->controller->initController($this->request, $this->response, $this->logger);

		// and that we can attempt validation, with no rules
		$method = $this->getPrivateMethodInvoker($this->controller, 'validate');
		$this->assertFalse($method([]));
	}

	//--------------------------------------------------------------------
	public function testHelpers()
	{
		$this->controller = new Class() extends Controller
		{
			protected $helpers = [
				'cookie',
				'text',
			];
		};
		$this->controller->initController($this->request, $this->response, $this->logger);

		$this->assertInstanceOf(Controller::class, $this->controller);
	}

}
