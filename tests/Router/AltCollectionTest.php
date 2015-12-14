<?php

use CodeIgniter\Router\AltCollection as RouteCollection;

class AltCollectionTest extends PHPUnit_Framework_TestCase
{

	public function setUp()
	{
	}

	//--------------------------------------------------------------------

	public function tearDown()
	{
	}

	//--------------------------------------------------------------------

	public function testBasicAdd()
	{
		$collection = new RouteCollection();

		$collection->add('home', '\my\controller');

		$expects = [
			'home' => '\my\controller',
		];

		$routes = $collection->getRoutes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testAddPrefixesDefaultNamespaceWhenNoneExist()
	{
		$collection = new RouteCollection();

		$collection->add('home', 'controller');

		$expects = [
			'home' => '\controller',
		];

		$routes = $collection->getRoutes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testAddIgnoresDefaultNamespaceWhenExists()
	{
		$collection = new RouteCollection();

		$collection->add('home', 'my\controller');

		$expects = [
			'home' => '\my\controller',
		];

		$routes = $collection->getRoutes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testAddWorksWithCurrentHTTPMethods()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$collection = new RouteCollection();

		$collection->match(['get'], 'home', 'controller');

		$expects = [
			'home' => '\controller',
		];

		$routes = $collection->getRoutes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testMatchIgnoresInvalidHTTPMethods()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$collection = new RouteCollection();

		$collection->match(['put'], 'home', 'controller');

		$routes = $collection->getRoutes();

		$this->assertEquals([], $routes);
	}

	//--------------------------------------------------------------------

	public function testAddWorksWithArrayOFHTTPMethods()
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';

		$collection = new RouteCollection();

		$collection->add('home', 'controller', ['get', 'post']);

		$expects = [
			'home' => '\controller',
		];

		$routes = $collection->getRoutes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testAddReplacesDefaultPlaceholders()
	{
		$collection = new RouteCollection();

		$collection->add('home/(:any)', 'controller');

		$expects = [
			'home/(.*)' => '\controller',
		];

		$routes = $collection->getRoutes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testAddReplacesCustomPlaceholders()
	{
		$collection = new RouteCollection();
		$collection->addPlaceholder('smiley', ':-)');

		$collection->add('home/(:smiley)', 'controller');

		$expects = [
			'home/(:-))' => '\controller',
		];

		$routes = $collection->getRoutes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testAddRecognizesCustomNamespaces()
	{
		$collection = new RouteCollection();
		$collection->setDefaultNamespace('\CodeIgniter');

		$collection->add('home', 'controller');

		$expects = [
			'home' => '\CodeIgniter\controller',
		];

		$routes = $collection->getRoutes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testSetDefaultControllerStoresIt()
	{
	    $collection = new RouteCollection();
		$collection->setDefaultController('godzilla');

		$this->assertEquals('godzilla', $collection->getDefaultController());
	}

	//--------------------------------------------------------------------

	public function testSetDefaultMethodStoresIt()
	{
		$collection = new RouteCollection();
		$collection->setDefaultMethod('biggerBox');

		$this->assertEquals('biggerBox', $collection->getDefaultMethod());
	}

	//--------------------------------------------------------------------

	public function testTranslateURIDashesWorks()
	{
	    $collection = new RouteCollection();
		$collection->setTranslateURIDashes(true);

		$this->assertEquals(true, $collection->shouldTranslateURIDashes());
	}

	//--------------------------------------------------------------------

	public function testAutoRouteStoresIt()
	{
		$collection = new RouteCollection();
		$collection->setAutoRoute(true);

		$this->assertEquals(true, $collection->shouldAutoRoute());
	}

	//--------------------------------------------------------------------

	public function testGroupingWorks()
	{
	    $collection = new RouteCollection();

		$collection->group('admin', function($collection)
		{
			$collection->add('users/list', '\Users::list');
		});

		$expected = [
			'admin/users/list' => '\Users::list'
		];

		$this->assertEquals($expected, $collection->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testGroupGetsSanitized()
	{
		$collection = new RouteCollection();

		$collection->group('<script>admin', function($collection)
		{
			$collection->add('users/list', '\Users::list');
		});

		$expected = [
				'admin/users/list' => '\Users::list'
		];

		$this->assertEquals($expected, $collection->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testResourcesScaffoldsCorrectly()
	{
	    $routes = new RouteCollection();

		$_SERVER['REQUEST_METHOD'] = 'GET';
		$routes->resources('photos');

		$expected = [
			'photos' => '\Photos::list_all',
		    'photos/(.*)' => '\Photos::show/$1'
		];

		$this->assertEquals($expected, $routes->getRoutes());

		$routes = new RouteCollection();
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$routes->resources('photos');

		$expected = [
				'photos' => '\Photos::create'
		];

		$this->assertEquals($expected, $routes->getRoutes());

		$routes = new RouteCollection();
		$_SERVER['REQUEST_METHOD'] = 'PUT';
		$routes->resources('photos');

		$expected = [
				'photos/(.*)' => '\Photos::update/$1'
		];

		$this->assertEquals($expected, $routes->getRoutes());

		$routes = new RouteCollection();
		$_SERVER['REQUEST_METHOD'] = 'DELETE';
		$routes->resources('photos');

		$expected = [
				'photos/(.*)' => '\Photos::delete/$1'
		];

		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testResourcesWithCustomController()
	{
		$routes = new RouteCollection();

		$_SERVER['REQUEST_METHOD'] = 'GET';
		$routes->resources('photos', ['controller' => '<script>gallery']);

		$expected = [
				'photos' => '\Gallery::list_all',
				'photos/(.*)' => '\Gallery::show/$1'
		];

		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testResourcesWithCustomPlaceholder()
	{
		$routes = new RouteCollection();

		$_SERVER['REQUEST_METHOD'] = 'GET';
		$routes->resources('photos', ['placeholder' => ':num']);

		$expected = [
				'photos' => '\Photos::list_all',
				'photos/([0-9]+)' => '\Photos::show/$1'
		];

		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

}