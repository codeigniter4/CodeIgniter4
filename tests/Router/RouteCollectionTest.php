<?php

require_once 'system/Router/RouteCollectionInterface.php';
require_once 'system/Router/RouteCollection.php';

use CodeIgniter\Router\RouteCollection;

class RouteCollectionTest extends PHPUnit_Framework_TestCase {

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
			'home' => '\my\controller'
		];

		$routes = $collection->routes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testAddPrefixesDefaultNamespaceWhenNoneExist()
	{
		$collection = new RouteCollection();

		$collection->add('home', 'controller');

		$expects = [
			'home' => '\controller'
		];

		$routes = $collection->routes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testAddIgnoresDefaultNamespaceWhenExists()
	{
		$collection = new RouteCollection();

		$collection->add('home', 'my\controller');

		$expects = [
			'home' => 'my\controller'
		];

		$routes = $collection->routes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testAddWorksWithCurrentHTTPMethods()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$collection = new RouteCollection();

		$collection->add('home', 'controller', 'get');

		$expects = [
			'home' => '\controller'
		];

		$routes = $collection->routes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testAddIgnoresInvalidHTTPMethods()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$collection = new RouteCollection();

		$collection->add('home', 'controller', 'post');

		$routes = $collection->routes();

		$this->assertEquals([], $routes);
	}

	//--------------------------------------------------------------------

	public function testAddWorksWithArrayOFHTTPMethods()
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';

		$collection = new RouteCollection();

		$collection->add('home', 'controller', ['get', 'post']);

		$expects = [
			'home' => '\controller'
		];

		$routes = $collection->routes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testAddReplacesDefaultPlaceholders()
	{
		$collection = new RouteCollection();

		$collection->add('home/(:any)', 'controller');

		$expects = [
			'home/(.*)' => '\controller'
		];

		$routes = $collection->routes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testAddReplacesCustomPlaceholders()
	{
		$collection = new RouteCollection();
		$collection->addPlaceholder('smiley', ':-)');

		$collection->add('home/(:smiley)', 'controller');

		$expects = [
			'home/(:-))' => '\controller'
		];

		$routes = $collection->routes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testAddRecognizesCustomNamespaces()
	{
		$collection = new RouteCollection();
		$collection->setDefaultNamespace('\CodeIgniter');

		$collection->add('home', 'controller');

		$expects = [
			'home' => '\CodeIgniter\controller'
		];

		$routes = $collection->routes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testAddPrefixesNamespacesWithBackslash()
	{
		$collection = new RouteCollection();
		$collection->setDefaultNamespace('CodeIgniter');

		$collection->add('home', 'controller');

		$expects = [
			'home' => '\CodeIgniter\controller'
		];

		$routes = $collection->routes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testAddStoresFunctionsForMaps()
	{
		$map = function()
		{
			return 1;
		};

		$collection = new RouteCollection();

		$collection->add('home', $map);

		$expects = [
			'home' => $map
		];

		$routes = $collection->routes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------
}