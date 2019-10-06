<?php namespace CodeIgniter;

use Config\App;
use Tests\Support\MockCodeIgniter;
use CodeIgniter\Router\RouteCollection;
use \CodeIgniter\Config\Services;

/**
 * @backupGlobals enabled
 */
class CodeIgniterTest extends \CIUnitTestCase
{
	/**
	 * @var \CodeIgniter\CodeIgniter
	 */
	protected $codeigniter;

	protected $routes;

	//--------------------------------------------------------------------

	protected function setUp(): void
	{
		parent::setUp();

		Services::reset();

		$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

		$config            = new App();
		$this->codeigniter = new MockCodeIgniter($config);
	}

	public function tearDown(): void
	{
		parent::tearDown();

		if (count( ob_list_handlers() ) > 1)
		{
			ob_end_clean();
		}
	}

	//--------------------------------------------------------------------

	public function testRunDefaultRoute()
	{
		$_SERVER['argv'] = [
			'index.php',
			'/',
		];
		$_SERVER['argc'] = 2;

		ob_start();
		$this->codeigniter->useSafeOutput(true)->run();
		$output = ob_get_clean();

		$this->assertContains('<h1>Welcome to CodeIgniter</h1>', $output);
	}

	//--------------------------------------------------------------------

	public function testRunEmptyDefaultRoute()
	{
		$_SERVER['argv'] = [
			'index.php',
		];
		$_SERVER['argc'] = 1;

		ob_start();
		$this->codeigniter->useSafeOutput(true)->run();
		$output = ob_get_clean();

		$this->assertContains('<h1>Welcome to CodeIgniter</h1>', $output);
	}

	//--------------------------------------------------------------------

	public function testRunClosureRoute()
	{
		$_SERVER['argv']        = [
			'index.php',
			'pages/about',
		];
		$_SERVER['argc']        = 2;
		$_SERVER['REQUEST_URI'] = '/pages/about';

		// Inject mock router.
		$routes = Services::routes();
		$routes->add('pages/(:segment)', function ($segment) {
			echo 'You want to see "' . esc($segment) . '" page.';
		});
		$router = Services::router($routes, Services::request());
		Services::injectMock('router', $router);

		ob_start();
		$this->codeigniter->useSafeOutput(true)->run();
		$output = ob_get_clean();

		$this->assertContains('You want to see "about" page.', $output);
	}

	//--------------------------------------------------------------------

	public function testRun404Override()
	{
		$_SERVER['argv'] = [
			'index.php',
			'/',
		];
		$_SERVER['argc'] = 2;

		// Inject mock router.
		$routes = Services::routes();
		$routes->setAutoRoute(false);
		$routes->set404Override('Home::index');
		$router = Services::router($routes, Services::request());
		Services::injectMock('router', $router);

		ob_start();
		$this->codeigniter->useSafeOutput(true)->run();
		$output = ob_get_clean();

		$this->assertContains('<h1>Welcome to CodeIgniter</h1>', $output);
	}

	//--------------------------------------------------------------------

	public function testRun404OverrideByClosure()
	{
		$_SERVER['argv'] = [
			'index.php',
			'/',
		];
		$_SERVER['argc'] = 2;

		// Inject mock router.
		$routes = new RouteCollection(Services::locator(), new \Config\Modules());
		$routes->setAutoRoute(false);
		$routes->set404Override(function () {
			echo '404 Override by Closure.';
		});
		$router = Services::router($routes, Services::request());
		Services::injectMock('router', $router);

		ob_start();
		$this->codeigniter->useSafeOutput(true)->run($routes);
		$output = ob_get_clean();

		$this->assertContains('404 Override by Closure.', $output);
	}

	//--------------------------------------------------------------------

	public function testControllersCanReturnString()
	{
		$_SERVER['argv']        = [
			'index.php',
			'pages/about',
		];
		$_SERVER['argc']        = 2;
		$_SERVER['REQUEST_URI'] = '/pages/about';

		// Inject mock router.
		$routes = Services::routes();
		$routes->add('pages/(:segment)', function ($segment) {
			return 'You want to see "' . esc($segment) . '" page.';
		});
		$router = Services::router($routes, Services::request());
		Services::injectMock('router', $router);

		ob_start();
		$this->codeigniter->useSafeOutput(true)->run();
		$output = ob_get_clean();

		$this->assertContains('You want to see "about" page.', $output);
	}

	//--------------------------------------------------------------------

	public function testControllersCanReturnResponseObject()
	{
		$_SERVER['argv']        = [
			'index.php',
			'pages/about',
		];
		$_SERVER['argc']        = 2;
		$_SERVER['REQUEST_URI'] = '/pages/about';

		// Inject mock router.
		$routes = Services::routes();
		$routes->add('pages/(:segment)', function ($segment) {
			$response = Services::response();
			$string   = "You want to see 'about' page.";
			return $response->setBody($string);
		});
		$router = Services::router($routes, Services::request());
		Services::injectMock('router', $router);

		ob_start();
		$this->codeigniter->useSafeOutput(true)->run();
		$output = ob_get_clean();

		$this->assertContains("You want to see 'about' page.", $output);
	}

	//--------------------------------------------------------------------

	public function testResponseConfigEmpty()
	{
		$_SERVER['argv'] = [
			'index.php',
			'/',
		];
		$_SERVER['argc'] = 2;

		$response = Config\Services::response(null, false);

		$this->assertInstanceOf('\CodeIgniter\HTTP\Response', $response);
	}

	//--------------------------------------------------------------------

	public function testRoutesIsEmpty()
	{
		$_SERVER['argv'] = [
			'index.php',
			'/',
		];
		$_SERVER['argc'] = 2;

		// Inject mock router.
		$router = Services::router(null, Services::request(), false);
		Services::injectMock('router', $router);

		ob_start();
		$this->codeigniter->useSafeOutput(true)->run();
		$output = ob_get_clean();

		$this->assertContains('<h1>Welcome to CodeIgniter</h1>', $output);
	}

	public function testTransfersCorrectHTTPVersion()
	{
		$_SERVER['argv']            = [
			'index.php',
			'/',
		];
		$_SERVER['argc']            = 2;
		$_SERVER['SERVER_PROTOCOL'] = 'HTTP/2';

		ob_start();
		$this->codeigniter->useSafeOutput(true)->run();
		$output = ob_get_clean();

		$response = $this->getPrivateProperty($this->codeigniter, 'response');

		$this->assertEquals(2, $response->getProtocolVersion());
	}

	public function testIgnoringErrorSuppressedByAt()
	{
		$_SERVER['argv'] = [
			'index.php',
			'/',
		];
		$_SERVER['argc'] = 2;

		ob_start();
		@unlink('inexistent-file');
		$this->codeigniter->useSafeOutput(true)->run();
		$output = ob_get_clean();

		$this->assertContains('<h1>Welcome to CodeIgniter</h1>', $output);
	}
}
