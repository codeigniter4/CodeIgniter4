<?php namespace CodeIgniter;

use CodeIgniter\Router\RouteCollection;
use Config\App;

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
		$this->codeigniter = new MockCodeIgniter(memory_get_usage(), microtime(true), $config);
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

	public function testRunDefaultRouteNoAutoRoute()
	{
		$_SERVER['argv'] = [
			'index.php',
			'/',
		];
		$_SERVER['argc'] = 2;

		// Inject mock router.
		$routes = Services::routes();
		$routes->setAutoRoute(false);
		$router = Services::router($routes);
		Services::injectMock('router', $router);

		ob_start();
		$this->codeigniter->run($routes);
		$output = ob_get_clean();

		$this->assertContains("Can't find a route for '/'.", $output);
	}

	//--------------------------------------------------------------------

	/**
	 * @group route
	 */
	public function testRunClosureRoute()
	{
		$_SERVER['argv'] = [
			'index.php',
			'pages/about',
		];
		$_SERVER['argc'] = 2;

		// Inject mock router.
		$routes = Services::routes();
		$routes->add('pages/(:segment)', function($segment)
		{
			echo 'You want to see "'.esc($segment).'" page.';
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
		$routes = new RouteCollection();
		$routes->setAutoRoute(false);
		$routes->set404Override(function()
		{
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

}
