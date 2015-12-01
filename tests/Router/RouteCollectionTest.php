<?php

use CodeIgniter\Router\RouteCollection;

class RouteCollectionTest extends PHPUnit_Framework_TestCase
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

		$collection->add('home', 'controller', 'get');

		$expects = [
			'home' => '\controller',
		];

		$routes = $collection->getRoutes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testAddIgnoresInvalidHTTPMethods()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$collection = new RouteCollection();

		$collection->add('home', 'controller', 'post');

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

	public function testAddPrefixesNamespacesWithBackslash()
	{
		$collection = new RouteCollection();
		$collection->setDefaultNamespace('CodeIgniter');

		$collection->add('home', 'controller');

		$expects = [
			'home' => '\CodeIgniter\controller',
		];

		$routes = $collection->getRoutes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testAddStoresFunctionsForMaps()
	{
		$map = function ()
		{
			return 1;
		};

		$collection = new RouteCollection();

		$collection->add('home', $map);

		$expects = [
			'home' => $map,
		];

		$routes = $collection->getRoutes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Map Tests
	//--------------------------------------------------------------------

	public function testMapAddsRoutes()
	{
		$map = [
			'one' => '\controller::index',
			'two' => '\controller::method',
		];

		$collection = new RouteCollection();

		$collection->map($map);

		$routes = $collection->getRoutes();

		$this->assertEquals($map, $routes);
	}

	//--------------------------------------------------------------------

	public function testMapAddsPrefix()
	{
		$map = [
			'one' => '\controller::index',
			'two' => '\controller::method',
		];

		$expected = [
			'my_one' => '\controller::index',
			'my_two' => '\controller::method',
		];

		$collection = new RouteCollection();

		$collection->map($map, ['prefix' => 'my_']);

		$routes = $collection->getRoutes();

		$this->assertEquals($expected, $routes);
	}

	//--------------------------------------------------------------------

	public function testMapAddsIgnoresOnBadHost()
	{
		$map = [
			'one' => '\controller::index',
			'two' => '\controller::method',
		];

		$expected = [];

		$_SERVER['SERVER_NAME'] = 'mickeymouse.com';

		$collection = new RouteCollection();

		$collection->map($map, ['hostname' => 'google.com']);

		$routes = $collection->getRoutes();

		$this->assertEquals($expected, $routes);
	}

	//--------------------------------------------------------------------

	public function testMapAddsAddsOnMatchingHost()
	{
		$map = [
			'one' => '\controller::index',
			'two' => '\controller::method',
		];

		$_SERVER['SERVER_NAME'] = 'mickeymouse.com';

		$collection = new RouteCollection();

		$collection->map($map, ['hostname' => 'mickeymouse.com']);

		$routes = $collection->getRoutes();

		$this->assertEquals($map, $routes);
	}

	//--------------------------------------------------------------------

	public function testMapAddsNamespace()
	{
		$map = [
			'one' => 'controller::index',
			'two' => 'controller::method',
		];

		$expected = [
			'one' => '\App\Controllers\controller::index',
			'two' => '\App\Controllers\controller::method',
		];

		$collection = new RouteCollection();

		$collection->map($map, ['namespace' => 'App\Controllers']);

		$routes = $collection->getRoutes();

		$this->assertEquals($expected, $routes);
	}

	//--------------------------------------------------------------------

	public function testMapAddsNamespaceWithLeadingSlash()
	{
		$map = [
			'one' => 'controller::index',
			'two' => 'controller::method',
		];

		$expected = [
			'one' => '\App\Controllers\controller::index',
			'two' => '\App\Controllers\controller::method',
		];

		$collection = new RouteCollection();

		$collection->map($map, ['namespace' => '\App\Controllers']);

		$routes = $collection->getRoutes();

		$this->assertEquals($expected, $routes);
	}

	//--------------------------------------------------------------------

	public function testMapResetsNamespace()
	{
		$map = [
			'one' => 'controller::index',
			'two' => 'controller::method',
		];

		$expected = [
			'one' => '\App\Controllers\controller::index',
			'two' => '\App\Controllers\controller::method',
		];

		$collection = new RouteCollection();

		$collection->map($map, ['namespace' => 'App\Controllers']);

		$routes = $collection->getRoutes();

		$this->assertEquals($expected, $routes);

		// Ensure it resets...
		$expected = [
			'one'   => '\App\Controllers\controller::index',
			'two'   => '\App\Controllers\controller::method',
			'three' => '\controller::index',
			'four'  => '\controller::method',
		];

		$map = [
			'three' => 'controller::index',
			'four'  => 'controller::method',
		];

		$collection->map($map);

		$routes = $collection->getRoutes();

		$this->assertEquals($expected, $routes);
	}

	//--------------------------------------------------------------------

	public function testMapWorksWithHTTPVerbs()
	{
		$map = [
			'one' => 'controller::index',
			'two' => [
				'delete' => 'controller::delete',
			],
		];

		$expected = [
			'one' => '\controller::index',
			'two' => '\controller::delete',
		];

		$_SERVER['REQUEST_METHOD'] = 'DELETE';

		$collection = new RouteCollection();

		$collection->map($map);

		$routes = $collection->getRoutes();

		$this->assertEquals($expected, $routes);
	}

	//--------------------------------------------------------------------

	public function testMapSkipsWithBadHTTPVerbs()
	{
		$map = [
			'one'    => 'controller::index',
			'delete' => [
				'two' => 'controller::delete',
			],
		];

		$expected = [
			'one' => '\controller::index',
		];

		$_SERVER['REQUEST_METHOD'] = 'GET';

		$collection = new RouteCollection();

		$collection->map($map);

		$routes = $collection->getRoutes();

		$this->assertEquals($expected, $routes);
	}

	//--------------------------------------------------------------------

	public function testReverseRoutingFindsSimpleMatch()
	{
		$map = [
			'path/(:any)/to/(:num)' => 'myController::goto/$1/$2',
		];

		$collection = new RouteCollection();

		$collection->map($map);

		$match = $collection->reverseRoute('myController::goto', 'string', 13);

		$this->assertEquals('path/string/to/13', $match);
	}

	//--------------------------------------------------------------------

	public function testReverseRoutingThrowsExceptionWithBadParamCount()
	{
		$map = [
			'path/(:any)/to/(:num)' => 'myController::goto/$1',
		];

		$collection = new RouteCollection();

		$collection->map($map);

		$this->setExpectedException('InvalidArgumentException');
		$match = $collection->reverseRoute('myController::goto', 'string', 13);
	}

	//--------------------------------------------------------------------

	public function testReverseRoutingThrowsExceptionWithNoMatch()
	{
		$map = [
			'path/(:any)/to/(:num)' => 'myController::goto/$1/$2',
		];

		$collection = new RouteCollection();

		$collection->map($map);

		$this->setExpectedException('InvalidArgumentException');
		$match = $collection->reverseRoute('myBadController::goto', 'string', 13);
	}

	//--------------------------------------------------------------------

	/**
	 * @group single
	 */
	public function testReverseRoutingThrowsExceptionWithBadParamTypes()
	{
		$map = [
			'path/(:any)/to/(:num)' => 'myController::goto/$1/$2',
		];

		$collection = new RouteCollection();

		$collection->map($map);

		$this->setExpectedException('LogicException');
		$match = $collection->reverseRoute('myController::goto', 13, 'string');
	}

	//--------------------------------------------------------------------

}