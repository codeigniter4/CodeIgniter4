<?php
namespace CodeIgniter\Router;

use CodeIgniter\Config\Services;

class RouterTest extends \CodeIgniter\Test\CIUnitTestCase
{

	/**
	 * @var \CodeIgniter\Router\RouteCollection $collection
	 */
	protected $collection;

	/**
	 * vfsStream root directory
	 *
	 * @var
	 */
	protected $root;

	/**
	 * @var \CodeIgniter\HTTP\IncomingRequest
	 */
	protected $request;

	protected function setUp(): void
	{
		parent::setUp();

		$moduleConfig          = new \Config\Modules;
		$moduleConfig->enabled = false;
		$this->collection      = new RouteCollection(Services::locator(), $moduleConfig);

		$routes = [
			'users'                                           => 'Users::index',
			'user-setting/show-list'                          => 'User_setting::show_list',
			'user-setting/(:segment)'                         => 'User_setting::detail/$1',
			'posts'                                           => 'Blog::posts',
			'pages'                                           => 'App\Pages::list_all',
			'posts/(:num)'                                    => 'Blog::show/$1',
			'posts/(:num)/edit'                               => 'Blog::edit/$1',
			'books/(:num)/(:alpha)/(:num)'                    => 'Blog::show/$3/$1',
			'closure/(:num)/(:alpha)'                         => function ($num, $str) {
				return $num . '-' . $str;
			},
			'{locale}/pages'                                  => 'App\Pages::list_all',
			'Admin/Admins'                                    => 'App\Admin\Admins::list_all',
			'/some/slash'                                     => 'App\Slash::index',
			'objects/(:segment)/sort/(:segment)/([A-Z]{3,7})' => 'AdminList::objectsSortCreate/$1/$2/$3',
		];

		$this->collection->map($routes);
		$this->request = Services::request();
		$this->request->setMethod('get');
	}

	//--------------------------------------------------------------------

	public function tearDown(): void
	{
	}

	//--------------------------------------------------------------------

	public function testEmptyURIMatchesDefaults()
	{
		$router = new Router($this->collection, $this->request);

		$router->handle('');

		$this->assertEquals($this->collection->getDefaultController(), $router->controllerName());
		$this->assertEquals($this->collection->getDefaultMethod(), $router->methodName());
	}

	//--------------------------------------------------------------------

	public function testZeroAsURIPath()
	{
		$router = new Router($this->collection, $this->request);

		$router->handle('0');

		$this->assertEquals('0', $router->controllerName());
	}

	//--------------------------------------------------------------------

	public function testURIMapsToController()
	{
		$router = new Router($this->collection, $this->request);

		$router->handle('users');

		$this->assertEquals('\Users', $router->controllerName());
		$this->assertEquals('index', $router->methodName());
	}

	//--------------------------------------------------------------------

	public function testURIMapsToControllerAltMethod()
	{
		$router = new Router($this->collection, $this->request);

		$router->handle('posts');

		$this->assertEquals('\Blog', $router->controllerName());
		$this->assertEquals('posts', $router->methodName());
	}

	//--------------------------------------------------------------------

	public function testURIMapsToNamespacedController()
	{
		$router = new Router($this->collection, $this->request);

		$router->handle('pages');

		$this->assertEquals('\App\Pages', $router->controllerName());
		$this->assertEquals('list_all', $router->methodName());
	}

	//--------------------------------------------------------------------

	public function testURIMapsParamsToBackReferences()
	{
		$router = new Router($this->collection, $this->request);

		$router->handle('posts/123');

		$this->assertEquals('show', $router->methodName());
		$this->assertEquals([123], $router->params());
	}

	//--------------------------------------------------------------------

	public function testURIMapsParamsToRearrangedBackReferences()
	{
		$router = new Router($this->collection, $this->request);

		$router->handle('posts/123/edit');

		$this->assertEquals('edit', $router->methodName());
		$this->assertEquals([123], $router->params());
	}

	//--------------------------------------------------------------------

	public function testURIMapsParamsToBackReferencesWithUnused()
	{
		$router = new Router($this->collection, $this->request);

		$router->handle('books/123/sometitle/456');

		$this->assertEquals('show', $router->methodName());
		$this->assertEquals([456, 123], $router->params());
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/672
	 */
	public function testURIMapsParamsWithMany()
	{
		$router = new Router($this->collection, $this->request);

		$router->handle('objects/123/sort/abc/FOO');

		$this->assertEquals('objectsSortCreate', $router->methodName());
		$this->assertEquals([123, 'abc', 'FOO'], $router->params());
	}

	//--------------------------------------------------------------------

	public function testClosures()
	{
		$router = new Router($this->collection, $this->request);

		$router->handle('closure/123/alpha', $this->request);

		$closure = $router->controllerName();

		$expects = $closure(...$router->params());

		$this->assertIsCallable($router->controllerName());
		$this->assertEquals($expects, '123-alpha');
	}

	//--------------------------------------------------------------------

	public function testAutoRouteFindsControllerWithFileAndMethod()
	{
		$router = new Router($this->collection, $this->request);

		$router->autoRoute('myController/someMethod');

		$this->assertEquals('MyController', $router->controllerName());
		$this->assertEquals('someMethod', $router->methodName());
	}

	//--------------------------------------------------------------------

	public function testAutoRouteFindsControllerWithFile()
	{
		$router = new Router($this->collection, $this->request);

		$router->autoRoute('myController');

		$this->assertEquals('MyController', $router->controllerName());
		$this->assertEquals('index', $router->methodName());
	}

	//--------------------------------------------------------------------

	public function testAutoRouteFindsControllerWithSubfolder()
	{
		$router = new Router($this->collection, $this->request);

		mkdir(APPPATH . 'Controllers/Subfolder');

		$router->autoRoute('subfolder/myController/someMethod');

		rmdir(APPPATH . 'Controllers/Subfolder');

		$this->assertEquals('MyController', $router->controllerName());
		$this->assertEquals('someMethod', $router->methodName());
	}

	//--------------------------------------------------------------------

	public function testDetectsLocales()
	{
		$router = new Router($this->collection, $this->request);

		$router->handle('fr/pages');

		$this->assertTrue($router->hasLocale());
		$this->assertEquals('fr', $router->getLocale());
	}

	//--------------------------------------------------------------------

	public function testRouteResource()
	{
		$router = new Router($this->collection, $this->request);

		$router->handle('Admin/Admins');

		$this->assertEquals('\App\Admin\Admins', $router->controllerName());
		$this->assertEquals('list_all', $router->methodName());
	}

	//--------------------------------------------------------------------

	public function testRouteWithLeadingSlash()
	{
		$router = new Router($this->collection, $this->request);

		$router->handle('some/slash');

		$this->assertEquals('\App\Slash', $router->controllerName());
		$this->assertEquals('index', $router->methodName());
	}

	//--------------------------------------------------------------------
	// options need to be declared separately, to not confuse PHPCBF
	public function testMatchedRouteOptions()
	{
		$optionsFoo = [
			'as'  => 'login',
			'foo' => 'baz',
		];
		$this->collection->add('foo', function () {
		}, $optionsFoo);
		$optionsBaz = [
			'as'  => 'admin',
			'foo' => 'bar',
		];
		$this->collection->add('baz', function () {
		}, $optionsBaz);

		$router = new Router($this->collection, $this->request);

		$router->handle('foo');

		$this->assertEquals($router->getMatchedRouteOptions(), ['as' => 'login', 'foo' => 'baz']);
	}

	public function testRouteWorksWithFilters()
	{
		$collection = $this->collection;

		$collection->group('foo', ['filter' => 'test'], function ($routes) {
			$routes->add('bar', 'TestController::foobar');
		});

		$router = new Router($collection, $this->request);

		$router->handle('foo/bar');

		$this->assertEquals('\TestController', $router->controllerName());
		$this->assertEquals('foobar', $router->methodName());
		$this->assertEquals('test', $router->getFilter());
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1247
	 */
	public function testGroupedResourceRoutesWithFilters()
	{
		$group = [
			'api',
			[
				'namespace' => 'App\Controllers\Api',
				'filter'    => 'api-auth',
			],
			function (RouteCollection $routes) {
				$routes->resource('posts', [
					'controller' => 'PostController',
				]);
			},
		];

		// GET
		$this->collection->group(...$group);

		$router = new Router($this->collection, $this->request);

		$router->handle('api/posts');

		$this->assertEquals('\App\Controllers\Api\PostController', $router->controllerName());
		$this->assertEquals('index', $router->methodName());
		$this->assertEquals('api-auth', $router->getFilter());

		$router->handle('api/posts/new');

		$this->assertEquals('\App\Controllers\Api\PostController', $router->controllerName());
		$this->assertEquals('new', $router->methodName());
		$this->assertEquals('api-auth', $router->getFilter());

		$router->handle('api/posts/50');

		$this->assertEquals('\App\Controllers\Api\PostController', $router->controllerName());
		$this->assertEquals('show', $router->methodName());
		$this->assertEquals('api-auth', $router->getFilter());

		$router->handle('api/posts/50/edit');

		$this->assertEquals('\App\Controllers\Api\PostController', $router->controllerName());
		$this->assertEquals('edit', $router->methodName());
		$this->assertEquals('api-auth', $router->getFilter());

		// POST
		$this->collection->group(...$group);

		$router = new Router($this->collection, $this->request);
		$this->collection->setHTTPVerb('post');

		$router->handle('api/posts');

		$this->assertEquals('\App\Controllers\Api\PostController', $router->controllerName());
		$this->assertEquals('create', $router->methodName());
		$this->assertEquals('api-auth', $router->getFilter());

		// PUT
		$this->collection->group(...$group);

		$router = new Router($this->collection, $this->request);
		$this->collection->setHTTPVerb('put');

		$router->handle('api/posts/50');

		$this->assertEquals('\App\Controllers\Api\PostController', $router->controllerName());
		$this->assertEquals('update', $router->methodName());
		$this->assertEquals('api-auth', $router->getFilter());

		// PATCH
		$this->collection->group(...$group);

		$router = new Router($this->collection, $this->request);
		$this->collection->setHTTPVerb('patch');

		$router->handle('api/posts/50');

		$this->assertEquals('\App\Controllers\Api\PostController', $router->controllerName());
		$this->assertEquals('update', $router->methodName());
		$this->assertEquals('api-auth', $router->getFilter());

		// DELETE
		$this->collection->group(...$group);

		$router = new Router($this->collection, $this->request);
		$this->collection->setHTTPVerb('delete');

		$router->handle('api/posts/50');

		$this->assertEquals('\App\Controllers\Api\PostController', $router->controllerName());
		$this->assertEquals('delete', $router->methodName());
		$this->assertEquals('api-auth', $router->getFilter());
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1240
	 */
	public function testMatchesCorrectlyWithMixedVerbs()
	{
		$this->collection->setHTTPVerb('get');

		$this->collection->add('/', 'Home::index');
		$this->collection->get('news', 'News::index');
		$this->collection->get('news/(:segment)', 'News::view/$1');
		$this->collection->add('(:any)', 'Pages::view/$1');

		$router = new Router($this->collection, $this->request);

		$router->handle('/');
		$this->assertEquals('\Home', $router->controllerName());
		$this->assertEquals('index', $router->methodName());

		$router->handle('news');
		$this->assertEquals('\News', $router->controllerName());
		$this->assertEquals('index', $router->methodName());

		$router->handle('news/daily');
		$this->assertEquals('\News', $router->controllerName());
		$this->assertEquals('view', $router->methodName());

		$router->handle('about');
		$this->assertEquals('\Pages', $router->controllerName());
		$this->assertEquals('view', $router->methodName());
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1354
	 */
	public function testRouteOrder()
	{
		$this->collection->post('auth', 'Main::auth_post');
		$this->collection->add('auth', 'Main::index');

		$router = new Router($this->collection, $this->request);
		$this->collection->setHTTPVerb('post');

		$router->handle('auth');
		$this->assertEquals('\Main', $router->controllerName());
		$this->assertEquals('auth_post', $router->methodName());
	}

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1564
	 */
	public function testTranslateURIDashes()
	{
		$router = new Router($this->collection, $this->request);

		$router->handle('user-setting/show-list');

		$router->setTranslateURIDashes(true);

		$this->assertEquals('\User_setting', $router->controllerName());
		$this->assertEquals('show_list', $router->methodName());
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1564
	 */
	public function testTranslateURIDashesForParams()
	{
		$router = new Router($this->collection, $this->request);
		$router->setTranslateURIDashes(true);

		$router->handle('user-setting/2018-12-02');

		$this->assertEquals('\User_setting', $router->controllerName());
		$this->assertEquals('detail', $router->methodName());
		$this->assertEquals(['2018-12-02'], $router->params());
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1564
	 */
	public function testTranslateURIDashesForAutoRoute()
	{
		$router = new Router($this->collection, $this->request);
		$router->setTranslateURIDashes(true);

		$router->autoRoute('admin-user/show-list');

		$this->assertEquals('Admin_user', $router->controllerName());
		$this->assertEquals('show_list', $router->methodName());
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/2032
	 */
	public function testAutoRouteMatchesZeroParams()
	{
		$router = new Router($this->collection, $this->request);

		$router->autoRoute('myController/someMethod/0/abc');

		$this->assertEquals('MyController', $router->controllerName());
		$this->assertEquals('someMethod', $router->methodName());

		$expected = [
			'0',
			'abc',
		];
		$this->assertEquals($expected, $router->params());
	}

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/2965
	 */
	public function testAutoRouteMethodEmpty()
	{
		$router = new Router($this->collection, $this->request);
		$router->handle('Home/');
		$this->assertEquals('Home', $router->controllerName());
		$this->assertEquals('index', $router->methodName());
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/3169
	 */
	public function testRegularExpressionWithUnicode()
	{
		$this->collection->get('news/([a-z0-9\x{0980}-\x{09ff}-]+)', 'News::view/$1');

		$router = new Router($this->collection, $this->request);

		$router->handle('news/a0%E0%A6%80%E0%A7%BF-');
		$this->assertEquals('\News', $router->controllerName());
		$this->assertEquals('view', $router->methodName());

		$expected = [
			'a0ঀ৿-',
		];
		$this->assertEquals($expected, $router->params());
	}

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/3169
	 */
	public function testRegularExpressionPlaceholderWithUnicode()
	{
		$this->collection->addPlaceholder('custom', '[a-z0-9\x{0980}-\x{09ff}-]+');
		$this->collection->get('news/(:custom)', 'News::view/$1');

		$router = new Router($this->collection, $this->request);

		$router->handle('news/a0%E0%A6%80%E0%A7%BF-');
		$this->assertEquals('\News', $router->controllerName());
		$this->assertEquals('view', $router->methodName());

		$expected = [
			'a0ঀ৿-',
		];
		$this->assertEquals($expected, $router->params());
	}
}
