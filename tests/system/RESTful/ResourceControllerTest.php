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

		$this->config      = new App();
		$this->request     = new \CodeIgniter\HTTP\IncomingRequest($this->config, new \CodeIgniter\HTTP\URI('https://somwhere.com'), null, new UserAgent());
		$this->response    = new \CodeIgniter\HTTP\Response($this->config);
		$this->logger      = \Config\Services::logger();
		$this->codeigniter = new MockCodeIgniter($this->config);
	}

	protected function getCollector(array $config = [], array $files = [], $moduleConfig = null)
	{
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

		return new RouteCollection($loader, $moduleConfig);
	}

	//--------------------------------------------------------------------

	public function testConstructor()
	{
		// make sure we can instantiate one
		$this->controller = new ResourceController();
		$this->controller->setModel('\Tests\Support\Models\UserModel');
		$this->controller->initController($this->request, $this->response, $this->logger);
		$this->assertInstanceOf(ResourceController::class, $this->controller);
	}

	public function testRoutes()
	{
		$_SERVER['argv']           = [
			'index.php',
			'work',
		];
		$_SERVER['argc']           = 2;
		$_SERVER['REQUEST_URI']    = '/work';
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$routes = $this->getCollector();
		$routes->setHTTPVerb('get');

		$routes->resource('work', ['controller' => '\Tests\Support\Resource\Worker']);
		$router = Services::router($routes, $this->request);
		Services::injectMock('router', $router);

		$expected = [
			'work'           => '\Tests\Support\Resource\Worker::index',
			'work/new'       => '\Tests\Support\Resource\Worker::new',
			'work/(.*)/edit' => '\Tests\Support\Resource\Worker::edit/$1',
			'work/(.*)'      => '\Tests\Support\Resource\Worker::show/$1',
		];
		$this->assertEquals($expected, $routes->getRoutes('GET'));
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

		// Inject mock router.
		$routes = $this->getCollector();
		$routes->setHTTPVerb('get');
		$routes->resource('work', ['controller' => '\Tests\Support\Resource\Worker']);
		$router = Services::router($routes);
		Services::injectMock('router', $router);

		ob_start();
		$this->codeigniter->useSafeOutput(true)->run();
		$output = ob_get_clean();

		$this->assertContains('Action not implemented', $output);
	}

	//  public function testShow()
	//  {
	//      $_SERVER['argv'] = [
	//          'index.php',
	//          'work',
	//          'show',
	//          '1',
	//      ];
	//      $_SERVER['argc'] = 4;
	//      $_SERVER['REQUEST_URI'] = '/work/show/1';
	//      $_SERVER['REQUEST_METHOD'] = 'GET';
	//
	//      ob_start();
	//      $this->codeigniter->useSafeOutput(true)->run();
	//      $output = ob_get_clean();
	//
	//      $this->assertContains('Action not implemented', $output);
	//  }
	//
	//  public function testNew()
	//  {
	//      $_SERVER['argv'] = [
	//          'index.php',
	//          'work',
	//          'new',
	//      ];
	//      $_SERVER['argc'] = 3;
	//      $_SERVER['REQUEST_URI'] = '/work/new';
	//      $_SERVER['REQUEST_METHOD'] = 'GET';
	//
	//      ob_start();
	//      $this->codeigniter->useSafeOutput(true)->run();
	//      $output = ob_get_clean();
	//
	//      $this->assertContains('Action not implemented', $output);
	//  }
	//
	//  public function testCreate()
	//  {
	//      $_SERVER['argv'] = [
	//          'index.php',
	//          'work',
	//          'create',
	//      ];
	//      $_SERVER['argc'] = 3;
	//      $_SERVER['REQUEST_URI'] = '/work/create';
	//      $_SERVER['REQUEST_METHOD'] = 'POST';
	//
	//      ob_start();
	//      $this->codeigniter->useSafeOutput(true)->run();
	//      $output = ob_get_clean();
	//
	//      $this->assertContains('Action not implemented', $output);
	//  }
	//
	//  public function testEdit()
	//  {
	//      $_SERVER['argv'] = [
	//          'index.php',
	//          'work',
	//          '1',
	//          'edit',
	//      ];
	//      $_SERVER['argc'] = 4;
	//      $_SERVER['REQUEST_URI'] = '/work/1/edit';
	//      $_SERVER['REQUEST_METHOD'] = 'GET';
	//
	//      ob_start();
	//      $this->codeigniter->useSafeOutput(true)->run();
	//      $output = ob_get_clean();
	//
	//      $this->assertContains('Action not implemented', $output);
	//  }
	//
	//  public function testUpdate()
	//  {
	//      $_SERVER['argv'] = [
	//          'index.php',
	//          'work',
	//          'update',
	//          '1',
	//      ];
	//      $_SERVER['argc'] = 4;
	//      $_SERVER['REQUEST_URI'] = '/work/update/1';
	//      $_SERVER['REQUEST_METHOD'] = 'PUT';
	//
	//      ob_start();
	//      $this->codeigniter->useSafeOutput(true)->run();
	//      $output = ob_get_clean();
	//
	//      $this->assertContains('Action not implemented', $output);
	//  }
	//
	//  public function testDelete()
	//  {
	//      $_SERVER['argv'] = [
	//          'index.php',
	//          'work',
	//          'delete',
	//          '1',
	//      ];
	//      $_SERVER['argc'] = 4;
	//      $_SERVER['REQUEST_URI'] = '/work/delete/1';
	//      $_SERVER['REQUEST_METHOD'] = 'DELETE';
	//
	//      ob_start();
	//      $this->codeigniter->useSafeOutput(true)->run();
	//      $output = ob_get_clean();
	//
	//      $this->assertContains('Action not implemented', $output);
	//  }
}
