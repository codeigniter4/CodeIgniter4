<?php namespace CodeIgniter;

use Config\App;

class CodeIgniterTest extends \CIUnitTestCase
{
	/**
	 * @var \CodeIgniter\CodeIgniter
	 */
	protected $codeigniter;

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
		$this->codeigniter->run();
		$output = ob_get_clean();

		$this->assertContains("Can't find a route for '/'.", $output);
	}

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

}
