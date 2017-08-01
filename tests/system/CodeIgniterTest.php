<?php namespace CodeIgniter;

use CodeIgniter\Autoloader\MockFileLocator;
use CodeIgniter\Router\RouteCollection;
use Config\App;

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

	public function setUp()
	{
		Services::reset();

		$config = new App();
		$this->codeigniter = new MockCodeIgniter($config);
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
		$this->codeigniter->run();
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
		$this->codeigniter->run();
		$output = ob_get_clean();

		$this->assertContains('<h1>Welcome to CodeIgniter</h1>', $output);
	}

	//--------------------------------------------------------------------

	/**
	 * Well, crikey. Since we've changed the error template
	 * to use STDOUT and STDERR, there's no great way to test
	 * this any more. Need to think on this a bit....
	 */
//	public function testRunDefaultRouteNoAutoRoute()
//	{
//		$_SERVER['argv'] = [
//			'index.php',
//			'/',
//		];
//		$_SERVER['argc'] = 2;
//
//		// Inject mock router.
//		$routes = Services::routes();
//		$routes->setAutoRoute(false);
//		$router = Services::router($routes);
//		Services::injectMock('router', $router);
//
//		ob_start();
//		$this->codeigniter->run($routes);
//		$output = ob_get_clean();
//
//		$this->assertTrue(strpos($output, "Can't find a route for") !== false);
//	}
	//--------------------------------------------------------------------

	public function testRunClosureRoute()
	{
		$_SERVER['argv'] = [
			'index.php',
			'pages/about',
		];
		$_SERVER['argc'] = 2;

		// Inject mock router.
		$routes = Services::routes();
		$routes->add('pages/(:segment)', function($segment) {
			echo 'You want to see "' . esc($segment) . '" page.';
		});
		$router = Services::router($routes);
		Services::injectMock('router', $router);

		ob_start();
		$this->codeigniter->run();
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
		$router = Services::router($routes);
		Services::injectMock('router', $router);

		ob_start();
		$this->codeigniter->run();
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
		$routes = new RouteCollection(new MockFileLocator(new \Config\Autoload()));
		$routes->setAutoRoute(false);
		$routes->set404Override(function() {
			echo '404 Override by Closure.';
		});
		$router = Services::router($routes);
		Services::injectMock('router', $router);

		ob_start();
		$this->codeigniter->run($routes);
		$output = ob_get_clean();

		$this->assertContains('404 Override by Closure.', $output);
	}

	//--------------------------------------------------------------------

	public function testControllersCanReturnString()
	{
		$_SERVER['argv'] = [
			'index.php',
			'pages/about',
		];
		$_SERVER['argc'] = 2;

		// Inject mock router.
		$routes = Services::routes();
		$routes->add('pages/(:segment)', function($segment) {
			return 'You want to see "' . esc($segment) . '" page.';
		});
		$router = Services::router($routes);
		Services::injectMock('router', $router);

		ob_start();
		$this->codeigniter->run();
		$output = ob_get_clean();

		$this->assertContains('You want to see "about" page.', $output);
	}

	public function testControllersCanReturnResponseObject()
	{
		$_SERVER['argv'] = [
			'index.php',
			'pages/about',
		];
		$_SERVER['argc'] = 2;

		// Inject mock router.
		$routes = Services::routes();
		$routes->add('pages/(:segment)', function($segment) {
			$response = Services::response();
			$string = "You want to see 'about' page.";
			return $response->setBody($string);
		});
		$router = Services::router($routes);
		Services::injectMock('router', $router);

		ob_start();
		$this->codeigniter->run();
		$output = ob_get_clean();

		$this->assertContains("You want to see 'about' page.", $output);
	}

	//--------------------------------------------------------------------

    public function testResposeConfigEmpty()
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
		$router = Services::router(null, false);
		Services::injectMock('router', $router);

		ob_start();
		$this->codeigniter->run();
		$output = ob_get_clean();

		$this->assertContains('<h1>Welcome to CodeIgniter</h1>', $output);
	}

}
