<?php

require_once 'system/Router/RouterInterface.php';
require_once 'system/Router/Router.php';
require_once 'system/Router/RouteCollectionInterface.php';
require_once 'system/Router/RouteCollection.php';

use CodeIgniter\Router\Router;

class RouterTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @var \CodeIgniter\Router\RouteCollection $collection
	 */
	protected $collection;

	public function setUp()
	{
		$this->collection = new \CodeIgniter\Router\RouteCollection();

		$routes = [
			'users'                        => 'Users::index',
			'posts'                        => 'Blog::posts',
			'pages'                        => 'App\Pages::list_all',
			'posts/(:num)'                 => 'Blog::show/$1',
			'posts/(:num)/edit'            => 'Blog::edit/$1',
			'books/(:num)/(:alpha)/(:num)' => 'Blog::show/$3/$1',
			'closure/(:num)/(:alpha)'      => function ($num, $str) { return $num.'-'.$str; },
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

		$this->assertEquals($this->collection->defaultController(), $router->controllerName());
		$this->assertEquals($this->collection->defaultMethod(), $router->methodName());
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

	/**
	 * @group single
	 */
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
}