<?php
namespace CodeIgniter\RESTful;

use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Log\Logger;
use CodeIgniter\Router\RouteCollection;
use Config\App;
use Tests\Support\MockCodeIgniter;

/**
 * Exercise our core Controller class.
 * Not a lot of business logic, so concentrate on making sure
 * we can exercise everything without blowing up :-/
 *
 * @runInSeparateProcess
 * @preserveGlobalState  disabled
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

		Services::reset();
		$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
		$this->config               = new App();
		$this->request              = new \CodeIgniter\HTTP\IncomingRequest($this->config, new \CodeIgniter\HTTP\URI('https://somwhere.com'), null, new UserAgent());
		$this->response             = new \CodeIgniter\HTTP\Response($this->config);
		$this->logger               = \Config\Services::logger();
		$this->codeigniter          = new MockCodeIgniter($this->config);
		//  }
		//
		//  protected function getCollector(array $config = [], array $files = [], $moduleConfig = null)
		//  {
		$config       = [];
		$files        = [];
		$moduleConfig = null;

		$defaults = [
			'Config' => APPPATH . 'Config',
			'App'    => APPPATH,
		];
		$config   = array_merge($config, $defaults);

		Services::autoloader()->addNamespace($config);

		$loader = Services::locator();

		if ($moduleConfig === null)
		{
			$moduleConfig          = new \Config\Modules();
			$moduleConfig->enabled = false;
		}

		//      return new RouteCollection($loader, $moduleConfig);
		$routes = new RouteCollection($loader, $moduleConfig);

		// Inject mock router.
		$routes->resource('work', ['controller' => '\Tests\Support\RESTful\Worker']);
		$router = Services::router($routes, $this->request);
		Services::injectMock('router', $router);
	}

	//--------------------------------------------------------------------

	public function testConstructor()
	{
		// make sure we can instantiate one
		$this->controller = new ResourceController();
		$this->controller->initController($this->request, $this->response, $this->logger);
		$this->assertInstanceOf(ResourceController::class, $this->controller);
	}

	//--------------------------------------------------------------------

	public function testIndex()
	{
		$_SERVER['argv']           = [
			'index.php',
			'work',
		];
		$_SERVER['argc']           = 2;
		$_SERVER['REQUEST_URI']    = '/work';
		$_SERVER['REQUEST_METHOD'] = 'GET';

		//      // Inject mock router.
		//      $routes = $this->getCollector();
		//      $routes->resource('work', ['controller' => '\Tests\Support\RESTful\Worker']);
		//      $router = Services::router($routes,$this->request);
		//      Services::injectMock('router', $router);

		ob_start();
		$this->codeigniter->useSafeOutput(true)->run();
		$output = ob_get_clean();

		$this->assertContains('index: Action not implemented', $output);
	}

	public function testShow()
	{
		$_SERVER['argv']           = [
			'index.php',
			'work',
			'show',
			'1',
		];
		$_SERVER['argc']           = 4;
		$_SERVER['REQUEST_URI']    = '/work/show/1';
		$_SERVER['REQUEST_METHOD'] = 'GET';

		//      // Inject mock router.
		//      $routes = $this->getCollector();
		//      $routes->resource('work', ['controller' => '\Tests\Support\RESTful\Worker']);
		//      $router = Services::router($routes);
		//      Services::injectMock('router', $router);

		ob_start();
		$this->codeigniter->useSafeOutput(true)->run();
		$output = ob_get_clean();

		$this->assertEquals('show: Action not implemented', $output);
	}

	public function testNew()
	{
		$_SERVER['argv']           = [
			'index.php',
			'work',
			'new',
		];
		$_SERVER['argc']           = 3;
		$_SERVER['REQUEST_URI']    = '/work/new';
		$_SERVER['REQUEST_METHOD'] = 'GET';

		//      // Inject mock router.
		//      $routes = $this->getCollector();
		//      $routes->resource('work', ['controller' => '\Tests\Support\RESTful\Worker']);
		//      $router = Services::router($routes);
		//      Services::injectMock('router', $router);

		ob_start();
		$this->codeigniter->useSafeOutput(true)->run();
		$output = ob_get_clean();

		$this->assertEquals('new: Action not implemented', $output);
	}

	public function testCreate()
	{
		$_SERVER['argv']           = [
			'index.php',
			'work',
			'create',
		];
		$_SERVER['argc']           = 3;
		$_SERVER['REQUEST_URI']    = '/work/create';
		$_SERVER['REQUEST_METHOD'] = 'POST';

		//      // Inject mock router.
		//      $routes = $this->getCollector();
		//      $routes->resource('work', ['controller' => '\Tests\Support\RESTful\Worker']);
		//      $router = Services::router($routes);
		//      Services::injectMock('router', $router);

		ob_start();
		$this->codeigniter->useSafeOutput(true)->run();
		$output = ob_get_clean();

		$this->assertEquals('create: Action not implemented', $output);
	}

	public function testEdit()
	{
		$_SERVER['argv']           = [
			'index.php',
			'work',
			'edit',
			'1',
		];
		$_SERVER['argc']           = 4;
		$_SERVER['REQUEST_URI']    = '/work/edit/1';
		$_SERVER['REQUEST_METHOD'] = 'GET';

		//      // Inject mock router.
		//      $routes = $this->getCollector();
		//      $routes->resource('work', ['controller' => '\Tests\Support\RESTful\Worker']);
		//      $router = Services::router($routes);
		//      Services::injectMock('router', $router);
		$routes->resource('work/edit', ['controller' => '\Tests\Support\RESTful\Worker::edit']);

		ob_start();
		$this->codeigniter->useSafeOutput(true)->run();
		$output = ob_get_clean();

		$this->assertEquals('edit: Action not implemented', $output);
	}

	public function testUpdate()
	{
		$_SERVER['argv']           = [
			'index.php',
			'work',
			'update',
			'1',
		];
		$_SERVER['argc']           = 4;
		$_SERVER['REQUEST_URI']    = '/work/update/1';
		$_SERVER['REQUEST_METHOD'] = 'PUT';

		//      // Inject mock router.
		//      $routes = $this->getCollector();
		//      $routes->resource('work', ['controller' => '\Tests\Support\RESTful\Worker']);
		//      $router = Services::router($routes);
		//      Services::injectMock('router', $router);

		ob_start();
		$this->codeigniter->useSafeOutput(true)->run();
		$output = ob_get_clean();

		$this->assertEquals('update: Action not implemented', $output);
	}

	public function testDelete()
	{
		$_SERVER['argv']           = [
			'index.php',
			'work',
			'delete',
			'1',
		];
		$_SERVER['argc']           = 4;
		$_SERVER['REQUEST_URI']    = '/work/delete/1';
		$_SERVER['REQUEST_METHOD'] = 'DELETE';

		//      // Inject mock router.
		//      $routes = $this->getCollector();
		//      $routes->resource('work', ['controller' => '\Tests\Support\RESTful\Worker']);
		//      $router = Services::router($routes);
		//      Services::injectMock('router', $router);

		ob_start();
		$this->codeigniter->useSafeOutput(true)->run();
		$output = ob_get_clean();

		$this->assertEquals('delete: Action not implemented', $output);
	}

}
