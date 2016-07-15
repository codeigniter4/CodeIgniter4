<?php namespace CodeIgniter\Router;

class RouteCollectionTest extends \CIUnitTestCase
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

	public function testGroupSetsOptions()
	{
		$routes = new RouteCollection();

		$routes->group('admin', ['namespace' => 'Admin'], function($routes)
		{
			$routes->add('users/list', 'Users::list');
		});

		$expected = [
			'admin/users/list' => '\Admin\Users::list'
		];

		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testHostnameOption()
	{
		$_SERVER['HTTP_HOST'] = 'example.com';

		$routes = new RouteCollection();

		$routes->add('from', 'to', ['hostname' => 'example.com']);
		$routes->add('foo', 'bar', ['hostname' => 'foobar.com']);

		$expected = [
			'from' => '\to'
		];

		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------



	public function testResourcesScaffoldsCorrectly()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$routes = new RouteCollection();

		$routes->resource('photos');

		$expected = [
			'photos' => '\Photos::listAll',
			'photos/(.*)' => '\Photos::show/$1'
		];

		$this->assertEquals($expected, $routes->getRoutes());

		$_SERVER['REQUEST_METHOD'] = 'POST';
		$routes = new RouteCollection();
		$routes->resource('photos');

		$expected = [
				'photos' => '\Photos::create'
		];

		$this->assertEquals($expected, $routes->getRoutes());

		$_SERVER['REQUEST_METHOD'] = 'PUT';
		$routes = new RouteCollection();
		$routes->resource('photos');

		$expected = [
				'photos/(.*)' => '\Photos::update/$1'
		];

		$this->assertEquals($expected, $routes->getRoutes());

		$_SERVER['REQUEST_METHOD'] = 'DELETE';
		$routes = new RouteCollection();
		$routes->resource('photos');

		$expected = [
				'photos/(.*)' => '\Photos::delete/$1'
		];

		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testResourcesWithCustomController()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$routes = new RouteCollection();

		$routes->resource('photos', ['controller' => '<script>gallery']);

		$expected = [
				'photos' => '\Gallery::listAll',
				'photos/(.*)' => '\Gallery::show/$1'
		];

		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testResourcesWithCustomPlaceholder()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$routes = new RouteCollection();

		$routes->resource('photos', ['placeholder' => ':num']);

		$expected = [
				'photos' => '\Photos::listAll',
				'photos/([0-9]+)' => '\Photos::show/$1'
		];

		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testResourcesWithOnly()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$routes = new RouteCollection();

		$routes->resource('photos', ['only' => 'listAll']);

		$expected = [
			'photos' => '\Photos::listAll'
		];

		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testMatchSupportsMultipleMethods()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$routes = new RouteCollection();

		$expected = ['here' => '\there'];

		$routes->match(['get', 'post'], 'here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());

		$_SERVER['REQUEST_METHOD'] = 'POST';
		$routes = new RouteCollection();
		$routes->match(['get', 'post'], 'here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testGet()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$routes = new RouteCollection();

		$expected = ['here' => '\there'];

		$routes->get('here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testPost()
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$routes = new RouteCollection();

		$expected = ['here' => '\there'];

		$routes->post('here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testGetDoesntAllowOtherMethods()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$routes = new RouteCollection();

		$expected = ['here' => '\there'];

		$routes->get('here', 'there');
		$routes->post('from', 'to');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testPut()
	{
		$_SERVER['REQUEST_METHOD'] = 'PUT';
		$routes = new RouteCollection();

		$expected = ['here' => '\there'];

		$routes->put('here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testDelete()
	{
		$_SERVER['REQUEST_METHOD'] = 'DELETE';
		$routes = new RouteCollection();

		$expected = ['here' => '\there'];

		$routes->delete('here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testHead()
	{
		$_SERVER['REQUEST_METHOD'] = 'HEAD';
		$routes = new RouteCollection();

		$expected = ['here' => '\there'];

		$routes->head('here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testPatch()
	{
		$_SERVER['REQUEST_METHOD'] = 'PATCH';
		$routes = new RouteCollection();

		$expected = ['here' => '\there'];

		$routes->patch('here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testOptions()
	{
		$_SERVER['REQUEST_METHOD'] = 'OPTIONS';
		$routes = new RouteCollection();

		$expected = ['here' => '\there'];

		$routes->options('here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testCLI()
	{
		$routes = new RouteCollection();

		$expected = ['here' => '\there'];

		$routes->cli('here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testEnvironmentRestricts()
	{
		// ENVIRONMENT should be 'testing'

		$_SERVER['REQUEST_METHOD'] = 'GET';
		$routes = new RouteCollection();

		$expected = ['here' => '\there'];

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
	
	public function testNamedRoutes()
	{
		$routes = new RouteCollection();

		$routes->add('users', 'Users::index', ['as' => 'namedRoute']);

		$this->assertEquals('users', $routes->reverseRoute('namedRoute'));
	}
	
	//--------------------------------------------------------------------
	
	public function testNamedRoutesFillInParams()
	{
		$routes = new RouteCollection();

		$routes->add('path/(:any)/to/(:num)', 'myController::goto/$1/$2', ['as' => 'namedRoute']);

		$match = $routes->reverseRoute('namedRoute', 'string', 13);

		$this->assertEquals('path/string/to/13', $match);
	}

	//--------------------------------------------------------------------

	public function testAddRedirect()
	{
		$routes = new RouteCollection();

		$routes->addRedirect('users', 'Users::index', 307);

		$expected = [
			'users' => '\Users::index'
		];

		$this->assertEquals($expected, $routes->getRoutes());
		$this->assertTrue($routes->isRedirect('users'));
		$this->assertEquals(307, $routes->getRedirectCode('users'));
	}

	//--------------------------------------------------------------------


}
