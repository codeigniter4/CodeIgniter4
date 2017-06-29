<?php namespace CodeIgniter\Router;

use CodeIgniter\Autoloader\MockFileLocator;

class RouterTest extends \CIUnitTestCase
{

	/**
	 * @var \CodeIgniter\Router\RouteCollection $collection
	 */
	protected $collection;

	/**
	 * vfsStream root directory
	 * @var
	 */
	protected $root;

	public function setUp()
	{
		$this->collection = new RouteCollection(new MockFileLocator(new \Config\Autoload()));

		$routes = [
			'users'                        => 'Users::index',
			'posts'                        => 'Blog::posts',
			'pages'                        => 'App\Pages::list_all',
			'posts/(:num)'                 => 'Blog::show/$1',
			'posts/(:num)/edit'            => 'Blog::edit/$1',
			'books/(:num)/(:alpha)/(:num)' => 'Blog::show/$3/$1',
			'closure/(:num)/(:alpha)'      => function ($num, $str) { return $num.'-'.$str; },
			'{locale}/pages'			   => 'App\Pages::list_all',
			'Admin/Admins'		   		   => 'App\Admin\Admins::list_all',
            '/some/slash'                  => 'App\Slash::index',
		];

		$this->collection->map($routes);
	}

	//--------------------------------------------------------------------

	public function tearDown()
	{
	}

	//--------------------------------------------------------------------

	public function testEmptyURIMatchesDefaults()
	{
		$router = new Router($this->collection);

		$router->handle('');

		$this->assertEquals($this->collection->getDefaultController(), $router->controllerName());
		$this->assertEquals($this->collection->getDefaultMethod(), $router->methodName());
	}

	//--------------------------------------------------------------------

	public function testURIMapsToController()
	{
		$router = new Router($this->collection);

		$router->handle('users');

		$this->assertEquals('\Users', $router->controllerName());
		$this->assertEquals('index', $router->methodName());
	}

	//--------------------------------------------------------------------

	public function testURIMapsToControllerAltMethod()
	{
		$router = new Router($this->collection);

		$router->handle('posts');

		$this->assertEquals('\Blog', $router->controllerName());
		$this->assertEquals('posts', $router->methodName());
	}

	//--------------------------------------------------------------------

	public function testURIMapsToNamespacedController()
	{
		$router = new Router($this->collection);

		$router->handle('pages');

		$this->assertEquals('\App\Pages', $router->controllerName());
		$this->assertEquals('list_all', $router->methodName());
	}

	//--------------------------------------------------------------------

	public function testURIMapsParamsToBackReferences()
	{
		$router = new Router($this->collection);

		$router->handle('posts/123');

		$this->assertEquals('show', $router->methodName());
		$this->assertEquals([123], $router->params());
	}

	//--------------------------------------------------------------------

	public function testURIMapsParamsToRearrangedBackReferences()
	{
		$router = new Router($this->collection);

		$router->handle('posts/123/edit');

		$this->assertEquals('edit', $router->methodName());
		$this->assertEquals([123], $router->params());
	}

	//--------------------------------------------------------------------

	public function testURIMapsParamsToBackReferencesWithUnused()
	{
		$router = new Router($this->collection);

		$router->handle('books/123/sometitle/456');

		$this->assertEquals('show', $router->methodName());
		$this->assertEquals([456, 123], $router->params());
	}

	//--------------------------------------------------------------------

	public function testClosures()
	{
		$router = new Router($this->collection);

		$router->handle('closure/123/alpha');

		$closure = $router->controllerName();

		$expects = call_user_func_array($closure, $router->params());

		$this->assertTrue(is_callable($router->controllerName()));
		$this->assertEquals($expects, '123-alpha');
	}

	//--------------------------------------------------------------------

	public function testAutoRouteFindsControllerWithFileAndMethod()
	{
	    $router = new Router($this->collection);

		$router->autoRoute('myController/someMethod');

		$this->assertEquals('MyController', $router->controllerName());
		$this->assertEquals('someMethod', $router->methodName());
	}

	//--------------------------------------------------------------------

	public function testAutoRouteFindsControllerWithFile()
	{
		$router = new Router($this->collection);

		$router->autoRoute('myController');

		$this->assertEquals('MyController', $router->controllerName());
		$this->assertEquals('index', $router->methodName());
	}

	//--------------------------------------------------------------------

	public function testAutoRouteFindsControllerWithSubfolder()
	{
		$router = new Router($this->collection);

		mkdir(APPPATH.'Controllers/Subfolder');

		$router->autoRoute('subfolder/myController/someMethod');

		rmdir(APPPATH.'Controllers/Subfolder');

		$this->assertEquals('MyController', $router->controllerName());
		$this->assertEquals('someMethod', $router->methodName());
	}

	//--------------------------------------------------------------------

	public function testDetectsLocales()
	{
	    $router = new Router($this->collection);

		$router->handle('fr/pages');

		$this->assertTrue($router->hasLocale());
		$this->assertEquals('fr', $router->getLocale());
	}

	//--------------------------------------------------------------------

	public function testRouteResource()
	{
		$router = new Router($this->collection);

		$router->handle('Admin/Admins');

		$this->assertEquals('\App\Admin\Admins', $router->controllerName());
		$this->assertEquals('list_all', $router->methodName());
	}

	//--------------------------------------------------------------------

    public function testRouteWithLeadingSlash()
    {
        $router = new Router($this->collection);

        $router->handle('some/slash');

        $this->assertEquals('\App\Slash', $router->controllerName());
        $this->assertEquals('index', $router->methodName());
    }

    //--------------------------------------------------------------------

}
