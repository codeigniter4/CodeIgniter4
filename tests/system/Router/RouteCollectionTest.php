<?php
namespace CodeIgniter\Router;

use CodeIgniter\Config\Services;
use CodeIgniter\Router\Exceptions\RouterException;

/**
 * @backupGlobals enabled
 */
class RouteCollectionTest extends \CodeIgniter\Test\CIUnitTestCase
{

	public function tearDown(): void
	{
	}

	//--------------------------------------------------------------------

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

		return (new RouteCollection($loader, $moduleConfig))->setHTTPVerb('get');
	}

	public function testBasicAdd()
	{
		$routes = $this->getCollector();

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
		$routes = $this->getCollector();

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
		$routes = $this->getCollector();

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
		Services::request()->setMethod('get');

		$routes = $this->getCollector();

		$routes->match(['get'], 'home', 'controller');

		$expects = [
			'home' => '\controller',
		];

		$routes = $routes->getRoutes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testAddWithLeadingSlash()
	{
		$routes = $this->getCollector();

		$routes->add('/home', 'controller');

		$expects = [
			'home' => '\controller',
		];

		$routes = $routes->getRoutes();

		$this->assertEquals($expects, $routes);
	}

	//--------------------------------------------------------------------

	public function testMatchIgnoresInvalidHTTPMethods()
	{
		Services::request()->setMethod('get');

		$routes = $this->getCollector();

		$routes->match(['put'], 'home', 'controller');

		$routes = $routes->getRoutes();

		$this->assertEquals([], $routes);
	}

	//--------------------------------------------------------------------

	public function testAddWorksWithArrayOFHTTPMethods()
	{
		Services::request()->setMethod('post');

		$routes = $this->getCollector();

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
		$routes = $this->getCollector();

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
		$routes = $this->getCollector();
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
		$routes = $this->getCollector();
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
		$routes = $this->getCollector();
		$routes->setDefaultController('godzilla');

		$this->assertEquals('godzilla', $routes->getDefaultController());
	}

	//--------------------------------------------------------------------

	public function testSetDefaultMethodStoresIt()
	{
		$routes = $this->getCollector();
		$routes->setDefaultMethod('biggerBox');

		$this->assertEquals('biggerBox', $routes->getDefaultMethod());
	}

	//--------------------------------------------------------------------

	public function testTranslateURIDashesWorks()
	{
		$routes = $this->getCollector();
		$routes->setTranslateURIDashes(true);

		$this->assertTrue($routes->shouldTranslateURIDashes());
	}

	//--------------------------------------------------------------------

	public function testAutoRouteStoresIt()
	{
		$routes = $this->getCollector();
		$routes->setAutoRoute(true);

		$this->assertTrue($routes->shouldAutoRoute());
	}

	//--------------------------------------------------------------------

	public function testGroupingWorks()
	{
		$routes = $this->getCollector();

		$routes->group(
				'admin', function ($routes) {
					$routes->add('users/list', '\Users::list');
				}
		);

		$expected = [
			'admin/users/list' => '\Users::list',
		];

		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testGroupGetsSanitized()
	{
		$routes = $this->getCollector();

		$routes->group(
				'<script>admin', function ($routes) {
					$routes->add('users/list', '\Users::list');
				}
		);

		$expected = [
			'admin/users/list' => '\Users::list',
		];

		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testGroupSetsOptions()
	{
		$routes = $this->getCollector();

		$routes->group(
				'admin', ['namespace' => 'Admin'], function ($routes) {
					$routes->add('users/list', 'Users::list');
				}
		);

		$expected = [
			'admin/users/list' => '\Admin\Users::list',
		];

		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testHostnameOption()
	{
		$_SERVER['HTTP_HOST'] = 'example.com';

		$routes = $this->getCollector();

		$routes->add('from', 'to', ['hostname' => 'example.com']);
		$routes->add('foo', 'bar', ['hostname' => 'foobar.com']);

		$expected = [
			'from' => '\to',
		];

		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testResourceScaffoldsCorrectly()
	{
		$routes = $this->getCollector();
		$routes->setHTTPVerb('get');

		$routes->resource('photos');

		$expected = [
			'photos'           => '\Photos::index',
			'photos/new'       => '\Photos::new',
			'photos/(.*)/edit' => '\Photos::edit/$1',
			'photos/(.*)'      => '\Photos::show/$1',
		];

		$this->assertEquals($expected, $routes->getRoutes());

		$routes = $this->getCollector();
		$routes->setHTTPVerb('post');
		$routes->resource('photos');

		$expected = [
			'photos' => '\Photos::create',
		];

		$this->assertEquals($expected, $routes->getRoutes());

		$routes = $this->getCollector();
		$routes->setHTTPVerb('put');
		$routes->resource('photos');

		$expected = [
			'photos/(.*)' => '\Photos::update/$1',
		];

		$this->assertEquals($expected, $routes->getRoutes());

		$routes = $this->getCollector();
		$routes->setHTTPVerb('patch');
		$routes->resource('photos');

		$expected = [
			'photos/(.*)' => '\Photos::update/$1',
		];

		$this->assertEquals($expected, $routes->getRoutes());

		$routes = $this->getCollector();
		$routes->setHTTPVerb('delete');
		$routes->resource('photos');

		$expected = [
			'photos/(.*)' => '\Photos::delete/$1',
		];

		$this->assertEquals($expected, $routes->getRoutes());
	}

	// Similar to the above, but with a more typical endpoint

	public function testResourceAPIScaffoldsCorrectly()
	{
		$routes = $this->getCollector();
		$routes->setHTTPVerb('get');

		$routes->resource('api/photos', ['controller' => 'Photos']);

		$expected = [
			'api/photos'           => '\Photos::index',
			'api/photos/new'       => '\Photos::new',
			'api/photos/(.*)/edit' => '\Photos::edit/$1',
			'api/photos/(.*)'      => '\Photos::show/$1',
		];

		$this->assertEquals($expected, $routes->getRoutes());

		$routes = $this->getCollector();
		$routes->setHTTPVerb('post');
		$routes->resource('api/photos', ['controller' => 'Photos']);

		$expected = [
			'api/photos' => '\Photos::create',
		];

		$this->assertEquals($expected, $routes->getRoutes());

		$routes = $this->getCollector();
		$routes->setHTTPVerb('put');
		$routes->resource('api/photos', ['controller' => 'Photos']);

		$expected = [
			'api/photos/(.*)' => '\Photos::update/$1',
		];

		$this->assertEquals($expected, $routes->getRoutes());

		$routes = $this->getCollector();
		$routes->setHTTPVerb('patch');
		$routes->resource('api/photos', ['controller' => 'Photos']);

		$expected = [
			'api/photos/(.*)' => '\Photos::update/$1',
		];

		$this->assertEquals($expected, $routes->getRoutes());

		$routes = $this->getCollector();
		$routes->setHTTPVerb('delete');
		$routes->resource('api/photos', ['controller' => 'Photos']);

		$expected = [
			'api/photos/(.*)' => '\Photos::delete/$1',
		];

		$this->assertEquals($expected, $routes->getRoutes());
	}

	public function testPresenterScaffoldsCorrectly()
	{
		$routes = $this->getCollector();
		$routes->setHTTPVerb('get');

		$routes->presenter('photos');

		$expected = [
			'photos'             => '\Photos::index',
			'photos/show/(.*)'   => '\Photos::show/$1',
			'photos/new'         => '\Photos::new',
			'photos/edit/(.*)'   => '\Photos::edit/$1',
			'photos/remove/(.*)' => '\Photos::remove/$1',
			'photos/(.*)'        => '\Photos::show/$1',
		];

		$this->assertEquals($expected, $routes->getRoutes());

		$routes = $this->getCollector();
		$routes->setHTTPVerb('post');
		$routes->presenter('photos');

		$expected = [
			'photos/create'      => '\Photos::create',
			'photos/update/(.*)' => '\Photos::update/$1',
			'photos/delete/(.*)' => '\Photos::delete/$1',
			'photos'             => '\Photos::create',
		];

		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testResourcesWithCustomController()
	{
		Services::request()->setMethod('get');
		$routes = $this->getCollector();

		$routes->resource('photos', ['controller' => '<script>gallery']);

		$expected = [
			'photos'           => '\Gallery::index',
			'photos/new'       => '\Gallery::new',
			'photos/(.*)/edit' => '\Gallery::edit/$1',
			'photos/(.*)'      => '\Gallery::show/$1',
		];

		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testResourcesWithCustomPlaceholder()
	{
		Services::request()->setMethod('get');
		$routes = $this->getCollector();

		$routes->resource('photos', ['placeholder' => ':num']);

		$expected = [
			'photos'               => '\Photos::index',
			'photos/new'           => '\Photos::new',
			'photos/([0-9]+)/edit' => '\Photos::edit/$1',
			'photos/([0-9]+)'      => '\Photos::show/$1',
		];

		$this->assertEquals($expected, $routes->getRoutes());
	}

	public function testResourcesWithDefaultPlaceholder()
	{
		Services::request()->setMethod('get');
		$routes = $this->getCollector();

		$routes->setDefaultConstraint('num');
		$routes->resource('photos');

		$expected = [
			'photos'               => '\Photos::index',
			'photos/new'           => '\Photos::new',
			'photos/([0-9]+)/edit' => '\Photos::edit/$1',
			'photos/([0-9]+)'      => '\Photos::show/$1',
		];

		$this->assertEquals($expected, $routes->getRoutes());
	}

	public function testResourcesWithBogusDefaultPlaceholder()
	{
		Services::request()->setMethod('get');
		$routes = $this->getCollector();

		$routes->setDefaultConstraint(':num');
		$routes->resource('photos');

		$expected = [
			'photos'           => '\Photos::index',
			'photos/new'       => '\Photos::new',
			'photos/(.*)/edit' => '\Photos::edit/$1',
			'photos/(.*)'      => '\Photos::show/$1',
		];

		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testResourcesWithOnly()
	{
		Services::request()->setMethod('get');
		$routes = $this->getCollector();

		$routes->resource('photos', ['only' => 'index']);

		$expected = [
			'photos' => '\Photos::index',
		];

		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testResourcesWithExcept()
	{
		Services::request()->setMethod('get');
		$routes = $this->getCollector();

		$routes->resource('photos', ['except' => 'edit,new']);

		$expected = [
			'photos'      => '\Photos::index',
			'photos/(.*)' => '\Photos::show/$1',
		];

		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testResourcesWithWebsafe()
	{
		$routes = $this->getCollector();
		$routes->setHTTPVerb('post');

		$routes->resource('photos', ['websafe' => true]);

		$expected = [
			'photos'             => '\Photos::create',
			'photos/(.*)'        => '\Photos::update/$1',
			'photos/(.*)/delete' => '\Photos::delete/$1',
		];

		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testMatchSupportsMultipleMethods()
	{
		Services::request()->setMethod('get');
		$routes = $this->getCollector();

		$expected = ['here' => '\there'];

		$routes->match(['get', 'post'], 'here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());

		Services::request()->setMethod('post');
		$routes = $this->getCollector();
		$routes->match(['get', 'post'], 'here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testGet()
	{
		Services::request()->setMethod('get');
		$routes = $this->getCollector();

		$expected = ['here' => '\there'];

		$routes->get('here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testPost()
	{
		$routes = $this->getCollector();
		$routes->setHTTPVerb('post');

		$expected = ['here' => '\there'];

		$routes->post('here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testGetDoesntAllowOtherMethods()
	{
		$routes = $this->getCollector();
		$routes->setHTTPVerb('get');

		$expected = ['here' => '\there'];

		$routes->get('here', 'there');
		$routes->post('from', 'to');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testPut()
	{
		$routes = $this->getCollector();
		$routes->setHTTPVerb('put');

		$expected = ['here' => '\there'];

		$routes->put('here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testDelete()
	{
		$routes = $this->getCollector();
		$routes->setHTTPVerb('delete');

		$expected = ['here' => '\there'];

		$routes->delete('here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testHead()
	{
		$routes = $this->getCollector();
		$routes->setHTTPVerb('head');

		$expected = ['here' => '\there'];

		$routes->head('here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testPatch()
	{
		$routes = $this->getCollector();
		$routes->setHTTPVerb('patch');

		$expected = ['here' => '\there'];

		$routes->patch('here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testOptions()
	{
		$routes = $this->getCollector();
		$routes->setHTTPVerb('options');

		$expected = ['here' => '\there'];

		$routes->options('here', 'there');
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testCLI()
	{
		$routes = $this->getCollector();

		$expected = ['here' => '\there'];

		$routes->cli('here', 'there');
		$this->assertEquals($expected, $routes->getRoutes('cli'));
	}

	//--------------------------------------------------------------------

	public function testEnvironmentRestricts()
	{
		// ENVIRONMENT should be 'testing'

		Services::request()->setMethod('get');
		$routes = $this->getCollector();

		$expected = ['here' => '\there'];

		$routes->environment(
				'testing', function ($routes) {
					$routes->get('here', 'there');
				}
		);

		$routes->environment(
				'badenvironment', function ($routes) {
					$routes->get('from', 'to');
				}
		);

		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	public function testReverseRoutingFindsSimpleMatch()
	{
		$routes = $this->getCollector();

		$routes->add('path/(:any)/to/(:num)', 'myController::goto/$1/$2');

		$match = $routes->reverseRoute('myController::goto', 'string', 13);

		$this->assertEquals('/path/string/to/13', $match);
	}

	//--------------------------------------------------------------------

	public function testReverseRoutingWithLocaleAndFindsSimpleMatch()
	{
		$routes = $this->getCollector();

		$routes->add('{locale}/path/(:any)/to/(:num)', 'myController::goto/$1/$2');

		$match = $routes->reverseRoute('myController::goto', 'string', 13);

		$this->assertEquals('/en/path/string/to/13', $match);
	}

	//--------------------------------------------------------------------

	public function testReverseRoutingReturnsFalseWithBadParamCount()
	{
		$routes = $this->getCollector();

		$routes->add('path/(:any)/to/(:num)', 'myController::goto/$1');

		$this->assertFalse($routes->reverseRoute('myController::goto', 'string', 13));
	}

	//--------------------------------------------------------------------

	public function testReverseRoutingReturnsFalseWithNoMatch()
	{
		$routes = $this->getCollector();

		$routes->add('path/(:any)/to/(:num)', 'myController::goto/$1/$2');

		$this->assertFalse($routes->reverseRoute('myBadController::goto', 'string', 13));
	}

	//--------------------------------------------------------------------

	public function testReverseRoutingThrowsExceptionWithBadParamTypes()
	{
		$routes = $this->getCollector();

		$routes->add('path/(:any)/to/(:num)', 'myController::goto/$1/$2');

		$this->expectException(RouterException::class);
		$match = $routes->reverseRoute('myController::goto', 13, 'string');
	}
	//--------------------------------------------------------------------

	public function testReverseRoutingWithLocale()
	{
		$routes = $this->getCollector();

		$routes->add('{locale}/contact', 'myController::goto');

		$this->assertEquals('/en/contact', $routes->reverseRoute('myController::goto'));
	}

	//--------------------------------------------------------------------

	public function testNamedRoutes()
	{
		$routes = $this->getCollector();

		$routes->add('users', 'Users::index', ['as' => 'namedRoute']);

		$this->assertEquals('/users', $routes->reverseRoute('namedRoute'));
	}

	//--------------------------------------------------------------------

	public function testNamedRoutesWithLocale()
	{
		$routes = $this->getCollector();

		$routes->add('{locale}/users', 'Users::index', ['as' => 'namedRoute']);

		$this->assertEquals('/en/users', $routes->reverseRoute('namedRoute'));
	}

	//--------------------------------------------------------------------

	public function testNamedRoutesFillInParams()
	{
		$routes = $this->getCollector();

		$routes->add('path/(:any)/to/(:num)', 'myController::goto/$1/$2', ['as' => 'namedRoute']);

		$match = $routes->reverseRoute('namedRoute', 'string', 13);

		$this->assertEquals('/path/string/to/13', $match);
	}

	//--------------------------------------------------------------------

	public function testNamedRoutesWithLocaleAndFillInParams()
	{
		$routes = $this->getCollector();

		$routes->add('{locale}/path/(:any)/to/(:num)', 'myController::goto/$1/$2', ['as' => 'namedRoute']);

		$match = $routes->reverseRoute('namedRoute', 'string', 13);

		$this->assertEquals('/en/path/string/to/13', $match);
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/642
	 */
	public function testNamedRoutesWithSameURIDifferentMethods()
	{
		$routes = $this->getCollector();

		$routes->get('user/insert', 'myController::goto/$1/$2', ['as' => 'namedRoute1']);
		$routes->post(
				'user/insert', function () {
				}, ['as' => 'namedRoute2']
		);
		$routes->put(
				'user/insert', function () {
				}, ['as' => 'namedRoute3']
		);

		$match1 = $routes->reverseRoute('namedRoute1');
		$match2 = $routes->reverseRoute('namedRoute2');
		$match3 = $routes->reverseRoute('namedRoute3');

		$this->assertEquals('/user/insert', $match1);
		$this->assertEquals('/user/insert', $match2);
		$this->assertEquals('/user/insert', $match3);
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/642
	 */
	public function testNamedRoutesWithLocaleAndWithSameURIDifferentMethods()
	{
		$routes = $this->getCollector();

		$routes->get('{locale}/user/insert', 'myController::goto/$1/$2', ['as' => 'namedRoute1']);
		$routes->post(
			'{locale}/user/insert',
			function () {
			},
			['as' => 'namedRoute2']
		);
		$routes->put(
			'{locale}/user/insert',
			function () {
			},
			['as' => 'namedRoute3']
		);

		$match1 = $routes->reverseRoute('namedRoute1');
		$match2 = $routes->reverseRoute('namedRoute2');
		$match3 = $routes->reverseRoute('namedRoute3');

		$this->assertEquals('/en/user/insert', $match1);
		$this->assertEquals('/en/user/insert', $match2);
		$this->assertEquals('/en/user/insert', $match3);
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/3048
	 */
	public function testNamedRoutesWithPipesInRegex()
	{
		$routes = $this->getCollector();

		$routes->get('/system/(this|that)', 'myController::system/$1', ['as' => 'pipedRoute']);

		$this->assertEquals('/system/this', $routes->reverseRoute('pipedRoute', 'this'));
		$this->assertEquals('/system/that', $routes->reverseRoute('pipedRoute', 'that'));
	}

	//--------------------------------------------------------------------

	public function testReverseRouteMatching()
	{
		$routes = $this->getCollector();

		$routes->get('test/(:segment)/(:segment)', 'TestController::test/$1/$2', ['as' => 'testRouter']);

		$match = $routes->reverseRoute('testRouter', 1, 2);

		$this->assertEquals('/test/1/2', $match);
	}

	//--------------------------------------------------------------------

	public function testReverseRouteMatchingWithLocale()
	{
		$routes = $this->getCollector();

		$routes->get('{locale}/test/(:segment)/(:segment)', 'TestController::test/$1/$2', ['as' => 'testRouter']);

		$match = $routes->reverseRoute('testRouter', 1, 2);

		$this->assertEquals('/en/test/1/2', $match);
	}

	//--------------------------------------------------------------------

	public function testAddRedirect()
	{
		$routes = $this->getCollector();

		//The second parameter is either the new URI to redirect to, or the name of a named route.
		$routes->addRedirect('users', 'users/index', 307);

		$expected = [
			'users' => 'users/index',
		];

		$this->assertEquals($expected, $routes->getRoutes());
		$this->assertTrue($routes->isRedirect('users'));
		$this->assertEquals(307, $routes->getRedirectCode('users'));
		$this->assertEquals(0, $routes->getRedirectCode('bosses'));
	}

	public function testAddRedirectNamed()
	{
		$routes = $this->getCollector();

		$routes->add('zombies', 'Zombies::index', ['as' => 'namedRoute']);
		$routes->addRedirect('users', 'namedRoute', 307);

		$expected = [
			'users'   => [
				'zombies' => '\Zombies::index',
			],
			'zombies' => '\Zombies::index',
		];

		$this->assertEquals($expected, $routes->getRoutes());
		$this->assertTrue($routes->isRedirect('users'));
		$this->assertEquals(307, $routes->getRedirectCode('users'));
	}

	public function testAddRedirectGetMethod()
	{
		$routes = $this->getCollector();

		$routes->get('zombies', 'Zombies::index', ['as' => 'namedRoute']);
		$routes->addRedirect('users', 'namedRoute', 307);

		$expected = [
			'users'   => [
				'zombies' => '\Zombies::index',
			],
			'zombies' => '\Zombies::index',
		];

		$this->assertEquals($expected, $routes->getRoutes());
		$this->assertTrue($routes->isRedirect('users'));
		$this->assertEquals(307, $routes->getRedirectCode('users'));
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/497
	 */
	public function testWithSubdomain()
	{
		$routes = $this->getCollector();

		$_SERVER['HTTP_HOST'] = 'adm.example.com';

		$routes->add('/objects/(:alphanum)', 'Admin::objectsList/$1', ['subdomain' => 'adm']);
		$routes->add('/objects/(:alphanum)', 'App::objectsList/$1');

		$expects = [
			'objects/([a-zA-Z0-9]+)' => '\Admin::objectsList/$1',
		];

		$this->assertEquals($expects, $routes->getRoutes());
	}

	public function testWithSubdomainMissing()
	{
		$routes = $this->getCollector();

		//      $_SERVER['HTTP_HOST'] = 'adm.example.com';

		$routes->add('/objects/(:alphanum)', 'Admin::objectsList/$1', ['subdomain' => 'adm']);
		$routes->add('/objects/(:alphanum)', 'App::objectsList/$1');

		$expects = [
			'objects/([a-zA-Z0-9]+)' => '\App::objectsList/$1',
		];

		$this->assertEquals($expects, $routes->getRoutes());
	}

	public function testWithDifferentSubdomain()
	{
		$routes = $this->getCollector();

		$_SERVER['HTTP_HOST'] = 'adm.example.com';

		$routes->add('/objects/(:alphanum)', 'Admin::objectsList/$1', ['subdomain' => 'sales']);
		$routes->add('/objects/(:alphanum)', 'App::objectsList/$1');

		$expects = [
			'objects/([a-zA-Z0-9]+)' => '\App::objectsList/$1',
		];

		$this->assertEquals($expects, $routes->getRoutes());
	}

	public function testWithWWWSubdomain()
	{
		$routes = $this->getCollector();

		$_SERVER['HTTP_HOST'] = 'www.example.com';

		$routes->add('/objects/(:alphanum)', 'Admin::objectsList/$1', ['subdomain' => 'sales']);
		$routes->add('/objects/(:alphanum)', 'App::objectsList/$1');

		$expects = [
			'objects/([a-zA-Z0-9]+)' => '\App::objectsList/$1',
		];

		$this->assertEquals($expects, $routes->getRoutes());
	}

	public function testWithDotCoSubdomain()
	{
		$routes = $this->getCollector();

		$_SERVER['HTTP_HOST'] = 'example.uk.co';

		$routes->add('/objects/(:alphanum)', 'Admin::objectsList/$1', ['subdomain' => 'sales']);
		$routes->add('/objects/(:alphanum)', 'App::objectsList/$1');

		$expects = [
			'objects/([a-zA-Z0-9]+)' => '\App::objectsList/$1',
		];

		$this->assertEquals($expects, $routes->getRoutes());
	}

	public function testWithDifferentSubdomainMissing()
	{
		$routes = $this->getCollector();

		$_SERVER['HTTP_HOST'] = 'adm.example.com';

		$routes->add('/objects/(:alphanum)', 'Admin::objectsList/$1', ['subdomain' => 'nothere']);
		$routes->add('/objects/(:alphanum)', 'App::objectsList/$1', ['subdomain' => '*']);

		$expects = [
			'objects/([a-zA-Z0-9]+)' => '\App::objectsList/$1',
		];

		$this->assertEquals($expects, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1692
	 */
	public function testWithSubdomainOrdered()
	{
		$routes = $this->getCollector();

		$_SERVER['HTTP_HOST'] = 'adm.example.com';

		$routes->add('/objects/(:alphanum)', 'App::objectsList/$1');
		$routes->add('/objects/(:alphanum)', 'Admin::objectsList/$1', ['subdomain' => 'adm']);

		$expects = [
			'objects/([a-zA-Z0-9]+)' => '\Admin::objectsList/$1',
		];

		$this->assertEquals($expects, $routes->getRoutes());
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/568
	 */
	public function testReverseRoutingWithClosure()
	{
		$routes = $this->getCollector();

		$routes->add('login', function () {
		});

		$match = $routes->reverseRoute('login');

		$this->assertEquals('/login', $match);
	}

	public function testReverseRoutingWithClosureNoMatch()
	{
		$routes = $this->getCollector();

		$routes->add('login', function () {
		});

		$this->assertFalse($routes->reverseRoute('foobar'));
	}

	//--------------------------------------------------------------------

	public function testWillDiscoverLocal()
	{
		$config = [
			'SampleSpace' => TESTPATH . '_support',
		];

		$moduleConfig          = new \Config\Modules();
		$moduleConfig->enabled = true;

		$routes = $this->getCollector($config, [], $moduleConfig);

		$match = $routes->getRoutes();

		$this->assertArrayHasKey('testing', $match);
		$this->assertEquals($match['testing'], '\TestController::index');
	}

	//--------------------------------------------------------------------

	public function testDiscoverLocalAllowsConfigToOverridePackages()
	{
		$config = [
			'SampleSpace' => TESTPATH . '_support',
		];

		$moduleConfig          = new \Config\Modules();
		$moduleConfig->enabled = true;

		$routes = $this->getCollector($config, [], $moduleConfig);

		$routes->add('testing', 'MainRoutes::index', ['as' => 'testing-index']);

		$match = $routes->getRoutes();

		$this->assertArrayHasKey('testing', $match);
		$this->assertEquals($match['testing'], '\MainRoutes::index');
	}

	//--------------------------------------------------------------------

	public function testRoutesOptions()
	{
		$routes = $this->getCollector();

		// options need to be declared separately, to not confuse PHPCBF
		$options = [
			'as'  => 'admin',
			'foo' => 'baz',
		];
		$routes->add(
				'administrator', function () {
				}, $options
		);

		$options = $routes->getRoutesOptions('administrator');

		$this->assertEquals($options, ['as' => 'admin', 'foo' => 'baz']);
	}

	public function testRouteGroupWithFilterSimple()
	{
		Services::request()->setMethod('get');
		$routes = $this->getCollector();

		$routes->group(
				'admin', ['filter' => 'role'], function ($routes) {
					$routes->add('users', '\Users::list');
				}
		);

		$this->assertTrue($routes->isFiltered('admin/users'));
		$this->assertFalse($routes->isFiltered('admin/franky'));
		$this->assertEquals('role', $routes->getFilterForRoute('admin/users'));
		$this->assertEquals('', $routes->getFilterForRoute('admin/bosses'));
	}

	public function testRouteGroupWithFilterWithParams()
	{
		Services::request()->setMethod('get');
		$routes = $this->getCollector();

		$routes->group(
				'admin', ['filter' => 'role:admin,manager'], function ($routes) {
					$routes->add('users', '\Users::list');
				}
		);

		$this->assertTrue($routes->isFiltered('admin/users'));
		$this->assertFalse($routes->isFiltered('admin/franky'));
		$this->assertEquals('role:admin,manager', $routes->getFilterForRoute('admin/users'));
	}

	//--------------------------------------------------------------------

	public function test404OverrideNot()
	{
		Services::request()->setMethod('get');
		$routes = $this->getCollector();

		$this->assertEquals(null, $routes->get404Override());
	}

	public function test404OverrideString()
	{
		Services::request()->setMethod('get');
		$routes = $this->getCollector();

		$routes->set404Override('Explode');
		$this->assertEquals('Explode', $routes->get404Override());
	}

	public function test404OverrideCallable()
	{
		Services::request()->setMethod('get');
		$routes = $this->getCollector();

		$routes->set404Override(function () {
			echo 'Explode now';
		}
		);
		$this->assertTrue(is_callable($routes->get404Override()));
	}

	//--------------------------------------------------------------------

	public function testOffsetParameters()
	{
		Services::request()->setMethod('get');
		$routes = $this->getCollector();

		$routes->get('users/(:num)', 'users/show/$1', ['offset' => 1]);
		$expected = ['users/([0-9]+)' => '\users/show/$2'];
		$this->assertEquals($expected, $routes->getRoutes());
	}

	//--------------------------------------------------------------------
	// Battery of tests for reported issue
	// @see https://github.com/codeigniter4/CodeIgniter4/issues/1697

	/**
	 */
	public function testRouteToWithSubdomainMatch()
	{
		$routes = $this->getCollector();

		Services::request()->setMethod('get');
		$_SERVER['HTTP_HOST'] = 'doc.example.com';

		$routes->get('i/(:any)', 'App\Controllers\Site\CDoc::item/$1', ['subdomain' => 'doc', 'as' => 'doc_item']);

		$this->assertEquals('/i/sth', $routes->reverseRoute('doc_item', 'sth'));
	}

	public function testRouteToWithSubdomainMismatch()
	{
		$routes = $this->getCollector();

		Services::request()->setMethod('get');
		$_SERVER['HTTP_HOST'] = 'dev.example.com';

		$routes->get('i/(:any)', 'App\Controllers\Site\CDoc::item/$1', ['subdomain' => 'doc', 'as' => 'doc_item']);

		$this->assertFalse($routes->reverseRoute('doc_item', 'sth'));
	}

	public function testRouteToWithSubdomainNot()
	{
		$routes = $this->getCollector();

		Services::request()->setMethod('get');
		$_SERVER['HTTP_HOST'] = 'example.com';

		$routes->get('i/(:any)', 'App\Controllers\Site\CDoc::item/$1', ['subdomain' => 'doc', 'as' => 'doc_item']);

		$this->assertFalse($routes->reverseRoute('doc_item', 'sth'));
	}

	public function testRouteToWithGenericSubdomainMatch()
	{
		$routes = $this->getCollector();

		Services::request()->setMethod('get');
		$_SERVER['HTTP_HOST'] = 'doc.example.com';

		$routes->get('i/(:any)', 'App\Controllers\Site\CDoc::item/$1', ['subdomain' => '*', 'as' => 'doc_item']);

		$this->assertEquals('/i/sth', $routes->reverseRoute('doc_item', 'sth'));
	}

	public function testRouteToWithGenericSubdomainMismatch()
	{
		$routes = $this->getCollector();

		Services::request()->setMethod('get');
		$_SERVER['HTTP_HOST'] = 'dev.example.com';

		$routes->get('i/(:any)', 'App\Controllers\Site\CDoc::item/$1', ['subdomain' => '*', 'as' => 'doc_item']);

		$this->assertEquals('/i/sth', $routes->reverseRoute('doc_item', 'sth'));
	}

	public function testRouteToWithGenericSubdomainNot()
	{
		$routes = $this->getCollector();

		Services::request()->setMethod('get');
		$_SERVER['HTTP_HOST'] = 'example.com';

		$routes->get('i/(:any)', 'App\Controllers\Site\CDoc::item/$1', ['subdomain' => '*', 'as' => 'doc_item']);

		$this->assertEquals('/i/sth', $routes->reverseRoute('doc_item', 'sth'));
	}

	public function testRouteToWithoutSubdomainMatch()
	{
		$routes = $this->getCollector();

		Services::request()->setMethod('get');
		$_SERVER['HTTP_HOST'] = 'doc.example.com';

		$routes->get('i/(:any)', 'App\Controllers\Site\CDoc::item/$1', ['hostname' => 'example.com', 'as' => 'doc_item']);

		$this->assertFalse($routes->reverseRoute('doc_item', 'sth'));
	}

	public function testRouteToWithoutSubdomainMismatch()
	{
		$routes = $this->getCollector();

		Services::request()->setMethod('get');
		$_SERVER['HTTP_HOST'] = 'dev.example.com';

		$routes->get('i/(:any)', 'App\Controllers\Site\CDoc::item/$1', ['hostname' => 'example.com', 'as' => 'doc_item']);

		$this->assertFalse($routes->reverseRoute('doc_item', 'sth'));
	}

	public function testRouteToWithoutSubdomainNot()
	{
		$routes = $this->getCollector();

		Services::request()->setMethod('get');
		$_SERVER['HTTP_HOST'] = 'example.com';

		$routes->get('i/(:any)', 'App\Controllers\Site\CDoc::item/$1', ['hostname' => 'example.com', 'as' => 'doc_item']);

		$this->assertEquals('/i/sth', $routes->reverseRoute('doc_item', 'sth'));
	}

	//--------------------------------------------------------------------
	// Tests for router overwritting issue
	// @see https://github.com/codeigniter4/CodeIgniter4/issues/1692

	public function testRouteOverwritingDifferentSubdomains()
	{
		Services::request()->setMethod('get');
		$_SERVER['HTTP_HOST'] = 'doc.domain.com';

		$routes = $this->getCollector();
		$router = new Router($routes, Services::request());

		$routes->setDefaultNamespace('App\Controllers');
		$routes->setDefaultController('Home');
		$routes->setDefaultMethod('index');
		$routes->setHTTPVerb('get');

		$routes->get('/', '\App\Controllers\Site\CDoc::index', ['subdomain' => 'doc', 'as' => 'doc_index']);
		$routes->get('/', 'Home::index', ['subdomain' => 'dev']);

		$expects = '\App\Controllers\Site\CDoc';

		$this->assertEquals($expects, $router->handle('/'));
	}

	public function testRouteOverwritingTwoRules()
	{
		Services::request()->setMethod('get');
		$_SERVER['HTTP_HOST'] = 'doc.domain.com';

		$routes = $this->getCollector();
		$router = new Router($routes, Services::request());

		$routes->setDefaultNamespace('App\Controllers');
		$routes->setDefaultController('Home');
		$routes->setDefaultMethod('index');

		$routes->get('/', '\App\Controllers\Site\CDoc::index', ['subdomain' => 'doc', 'as' => 'doc_index']);
		$routes->get('/', 'Home::index');

		// the second rule applies, so overwrites the first
		$expects = '\App\Controllers\Home';

		$this->assertEquals($expects, $router->handle('/'));
	}

	public function testRouteOverwritingTwoRulesLastApplies()
	{
		Services::request()->setMethod('get');
		$_SERVER['HTTP_HOST'] = 'doc.domain.com';

		$routes = $this->getCollector();
		$router = new Router($routes, Services::request());

		$routes->setDefaultNamespace('App\Controllers');
		$routes->setDefaultController('Home');
		$routes->setDefaultMethod('index');

		$routes->get('/', 'Home::index');
		$routes->get('/', '\App\Controllers\Site\CDoc::index', ['subdomain' => 'doc', 'as' => 'doc_index']);

		$expects = '\App\Controllers\Site\CDoc';

		$this->assertEquals($expects, $router->handle('/'));
	}

	public function testRouteOverwritingMatchingSubdomain()
	{
		Services::request()->setMethod('get');
		$_SERVER['HTTP_HOST'] = 'doc.domain.com';

		$routes = $this->getCollector();
		$router = new Router($routes, Services::request());

		$routes->setDefaultNamespace('App\Controllers');
		$routes->setDefaultController('Home');
		$routes->setDefaultMethod('index');

		$routes->get('/', 'Home::index', ['as' => 'ddd']);
		$routes->get('/', '\App\Controllers\Site\CDoc::index', ['subdomain' => 'doc', 'as' => 'doc_index']);

		$expects = '\App\Controllers\Site\CDoc';

		$this->assertEquals($expects, $router->handle('/'));
	}

	public function testRouteOverwritingMatchingHost()
	{
		Services::request()->setMethod('get');
		$_SERVER['HTTP_HOST'] = 'doc.domain.com';

		$routes = $this->getCollector();
		$router = new Router($routes, Services::request());

		$routes->setDefaultNamespace('App\Controllers');
		$routes->setDefaultController('Home');
		$routes->setDefaultMethod('index');

		$routes->get('/', 'Home::index', ['as' => 'ddd']);
		$routes->get('/', '\App\Controllers\Site\CDoc::index', ['hostname' => 'doc.domain.com', 'as' => 'doc_index']);

		$expects = '\App\Controllers\Site\CDoc';

		$this->assertEquals($expects, $router->handle('/'));
	}

	//--------------------------------------------------------------------
	// Tests for router DefaultNameSpace issue
	// @see https://github.com/codeigniter4/CodeIgniter4/issues/2423

	public function testRouteDefaultNameSpace()
	{
		Services::request()->setMethod('get');
		$routes = $this->getCollector();
		$router = new Router($routes, Services::request());

		$routes->setDefaultNamespace('App\Controllers');
		$routes->get('/', 'Core\Home::index');

		$expects = '\App\Controllers\Core\Home';

		$this->assertEquals($expects, $router->handle('/'));
	}

	public function testZeroAsURIPath()
	{
		Services::request()->setMethod('get');
		$routes = $this->getCollector();
		$router = new Router($routes, Services::request());

		$routes->setDefaultNamespace('App\Controllers');
		$routes->get('/0', 'Core\Home::index');

		$expects = '\App\Controllers\Core\Home';

		$this->assertEquals($expects, $router->handle('/0'));
	}

	public function provideRouteDefaultNamespace()
	{
		return [
			'with \\ prefix'    => ['\App\Controllers'],
			'without \\ prefix' => ['App\Controllers'],
		];
	}

	/**
	 * @dataProvider provideRouteDefaultNamespace
	 */
	public function testAutoRoutesControllerNameReturnsFQCN($namespace)
	{
		$routes = $this->getCollector();
		$routes->setAutoRoute(true);
		$routes->setDefaultNamespace($namespace);

		$router = new Router($routes, Services::request());
		$router->handle('/product');

		$this->assertEquals('\App\\Controllers\\Product', $router->controllerName());
	}

	/**
	 * @dataProvider provideRouteDefaultNamespace
	 */
	public function testRoutesControllerNameReturnsFQCN($namespace)
	{
		$routes = $this->getCollector();
		$routes->setAutoRoute(false);
		$routes->setDefaultNamespace($namespace);
		$routes->get('/product', 'Product');

		$router = new Router($routes, Services::request());
		$router->handle('/product');

		$this->assertEquals('\App\\Controllers\\Product', $router->controllerName());
	}

}
