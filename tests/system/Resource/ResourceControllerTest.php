<?php namespace CodeIgniter\Resource;

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
class ResourceControllerTest extends \CIUnitTestCase
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

	protected function setUp()
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
		$this->controller = new ResourceController();
		$this->controller->initController($this->request, $this->response, $this->logger);
		$this->assertInstanceOf(ResourceController::class, $this->controller);
	}

}
