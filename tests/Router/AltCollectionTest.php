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
		$routes = new RouteCollection();

		$routes->add('home', '\my\controller');

		$expects = [
			'home' => '\my\controller',
		];

		$routes = $routes->getRoutes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testAddPrefixesDefaultNamespaceWhenNoneExist()
	{
		$routes = new RouteCollection();

		$routes->add('home', 'controller');

		$expects = [
			'home' => '\controller',
		];

		$routes = $routes->getRoutes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testAddIgnoresDefaultNamespaceWhenExists()
	{
		$routes = new RouteCollection();

		$routes->add('home', 'my\controller');

		$expects = [
			'home' => '\my\controller',
		];

		$routes = $routes->getRoutes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testAddWorksWithCurrentHTTPMethods()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$routes = new RouteCollection();

		$routes->match(['get'], 'home', 'controller');

		$expects = [
			'home' => '\controller',
		];

		$routes = $routes->getRoutes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testMatchIgnoresInvalidHTTPMethods()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$routes = new RouteCollection();

		$routes->match(['put'], 'home', 'controller');

		$routes = $routes->getRoutes();

		$this->assertEquals([], $routes);
	}

	//--------------------------------------------------------------------

	public function testAddWorksWithArrayOFHTTPMethods()
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';

		$routes = new RouteCollection();

		$routes->add('home', 'controller', ['get', 'post']);

		$expects = [
			'home' => '\controller',
		];

		$routes = $routes->getRoutes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testAddReplacesDefaultPlaceholders()
	{
		$routes = new RouteCollection();

		$routes->add('home/(:any)', 'controller');

		$expects = [
			'home/(.*)' => '\controller',
		];

		$routes = $routes->getRoutes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testAddReplacesCustomPlaceholders()
	{
		$routes = new RouteCollection();
		$routes->addPlaceholder('smiley', ':-)');

		$routes->add('home/(:smiley)', 'controller');

		$expects = [
			'home/(:-))' => '\controller',
		];

		$routes = $routes->getRoutes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testAddRecognizesCustomNamespaces()
	{
		$routes = new RouteCollection();
		$routes->setDefaultNamespace('\CodeIgniter');

		$routes->add('home', 'controller');

		$expects = [
			'home' => '\CodeIgniter\controller',
		];

		$routes = $routes->getRoutes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testSetDefaultControllerStoresIt()
	{
	    $routes = new RouteCollection();
		$routes->setDefaultController('godzilla');

		$this->assertEquals('godzilla', $routes->getDefaultController());
	}

	//--------------------------------------------------------------------

	public function testSetDefaultMethodStoresIt()
	{
		$routes = new RouteCollection();
		$routes->setDefaultMethod('biggerBox');

		$this->assertEquals('biggerBox', $routes->getDefaultMethod());
	}

	//--------------------------------------------------------------------

	public function testTranslateURIDashesWorks()
	{
	    $routes = new RouteCollection();
		$routes->setTranslateURIDashes(true);

		$this->assertEquals(true, $routes->shouldTranslateURIDashes());
	}

	//--------------------------------------------------------------------

	public function testAutoRouteStoresIt()
	{
		$routes = new RouteCollection();
		$routes->setAutoRoute(true);

		$this->assertEquals(true, $routes->shouldAutoRoute());
	}

	//--------------------------------------------------------------------

	public function testGroupingWorks()
	{
	    $routes = new RouteCollection();

		$routes->group('admin', function($routes)
		{
			$routes->add('users/list', '\Users::list');
		});

		$expected = [
			'admin/users/list' => '\Users::list'
		];

		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testGroupGetsSanitized()
	{
		$routes = new RouteCollection();

		$routes->group('<script>admin', function($routes)
		{
			$routes->add('users/list', '\Users::list');
		});

		$expected = [
				'admin/users/list' => '\Users::list'
		];

		$this->assertEquals($expected, $routes->getRoutes());
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

	public function testMatchSupportsMultipleMethods()
	{
		$routes = new RouteCollection();

		$expected = ['here' => '\there'];

		$_SERVER['REQUEST_METHOD'] = 'GET';
		$routes->match(['get', 'post'], 'here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());

		$routes = new RouteCollection();
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$routes->match(['get', 'post'], 'here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testGet()
	{
		$routes = new RouteCollection();

		$expected = ['here' => '\there'];

		$_SERVER['REQUEST_METHOD'] = 'GET';
		$routes->get('here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testPost()
	{
		$routes = new RouteCollection();

		$expected = ['here' => '\there'];

		$_SERVER['REQUEST_METHOD'] = 'POST';
		$routes->post('here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testGetDoesntAllowOtherMethods()
	{
		$routes = new RouteCollection();

		$expected = ['here' => '\there'];

		$_SERVER['REQUEST_METHOD'] = 'GET';
		$routes->get('here', 'there');
		$routes->post('from', 'to');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testPut()
	{
		$routes = new RouteCollection();

		$expected = ['here' => '\there'];

		$_SERVER['REQUEST_METHOD'] = 'PUT';
		$routes->put('here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testDelete()
	{
		$routes = new RouteCollection();

		$expected = ['here' => '\there'];

		$_SERVER['REQUEST_METHOD'] = 'DELETE';
		$routes->delete('here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testHead()
	{
		$routes = new RouteCollection();

		$expected = ['here' => '\there'];

		$_SERVER['REQUEST_METHOD'] = 'HEAD';
		$routes->head('here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testPatch()
	{
		$routes = new RouteCollection();

		$expected = ['here' => '\there'];

		$_SERVER['REQUEST_METHOD'] = 'PATCH';
		$routes->patch('here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testOptions()
	{
		$routes = new RouteCollection();

		$expected = ['here' => '\there'];

		$_SERVER['REQUEST_METHOD'] = 'OPTIONS';
		$routes->options('here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testEnvironmentRestricts()
	{
		// ENVIRONMENT should be 'testing'

		$routes = new RouteCollection();

		$expected = ['here' => '\there'];

		$_SERVER['REQUEST_METHOD'] = 'GET';

		$routes->environment('testing', function($routes)
		{
			$routes->get('here', 'there');
		});

		$routes->environment('badenvironment', function($routes)
		{
			$routes->get('from', 'to');
		});

		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testReverseRoutingFindsSimpleMatch()
	{
		$routes = new RouteCollection();

		$routes->add('path/(:any)/to/(:num)', 'myController::goto/$1/$2');

		$match = $routes->reverseRoute('myController::goto', 'string', 13);

		$this->assertEquals('path/string/to/13', $match);
	}

	//--------------------------------------------------------------------

	public function testReverseRoutingThrowsExceptionWithBadParamCount()
	{
		$routes = new RouteCollection();

		$routes->add('path/(:any)/to/(:num)', 'myController::goto/$1');

		$this->setExpectedException('InvalidArgumentException');
		$match = $routes->reverseRoute('myController::goto', 'string', 13);
	}

	//--------------------------------------------------------------------

	public function testReverseRoutingThrowsExceptionWithNoMatch()
	{
		$routes = new RouteCollection();

		$routes->add('path/(:any)/to/(:num)', 'myController::goto/$1/$2');

		$this->setExpectedException('InvalidArgumentException');
		$match = $routes->reverseRoute('myBadController::goto', 'string', 13);
	}

	//--------------------------------------------------------------------

	public function testReverseRoutingThrowsExceptionWithBadParamTypes()
	{
		$routes = new RouteCollection();

		$routes->add('path/(:any)/to/(:num)', 'myController::goto/$1/$2');

		$this->setExpectedException('LogicException');
		$match = $routes->reverseRoute('myController::goto', 13, 'string');
	}

	//--------------------------------------------------------------------
	
	
}