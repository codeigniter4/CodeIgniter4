<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Router;

use CodeIgniter\Config\Factories;
use CodeIgniter\Config\Services;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Exceptions\BadRequestException;
use CodeIgniter\HTTP\Exceptions\RedirectException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Router\Exceptions\RouterException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Config\Modules;
use Config\Routing;
use Tests\Support\Filters\Customfilter;

/**
 * @internal
 *
 * @group Others
 */
final class RouterTest extends CIUnitTestCase
{
    private RouteCollection $collection;
    private IncomingRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createRouteCollection();

        $this->request = Services::request();
        $this->request->setMethod(Method::GET);
    }

    private function createRouteCollection(?Routing $routingConfig = null): void
    {
        $moduleConfig          = new Modules();
        $moduleConfig->enabled = false;

        $routingConfig ??= new Routing();
        $routingConfig->defaultNamespace = '\\';

        $this->collection = new RouteCollection(Services::locator(), $moduleConfig, $routingConfig);

        $routes = [
            '/'                                               => 'Home::index',
            'users'                                           => 'Users::index',
            'user-setting/show-list'                          => 'User_setting::show_list',
            'user-setting/(:segment)'                         => 'User_setting::detail/$1',
            'posts'                                           => 'Blog::posts',
            'pages'                                           => 'App\Pages::list_all',
            'posts/(:num)'                                    => 'Blog::show/$1',
            'posts/(:num)/edit'                               => 'Blog::edit/$1',
            'books/(:num)/(:alpha)/(:num)'                    => 'Blog::show/$3/$1',
            'closure/(:num)/(:alpha)'                         => static fn ($num, $str) => $num . '-' . $str,
            '{locale}/pages'                                  => 'App\Pages::list_all',
            'test/(:any)/lang/{locale}'                       => 'App\Pages::list_all',
            'admin/admins'                                    => 'App\Admin\Admins::list_all',
            'admin/admins/edit/(:any)'                        => 'App/Admin/Admins::edit_show/$1',
            '/some/slash'                                     => 'App\Slash::index',
            'objects/(:segment)/sort/(:segment)/([A-Z]{3,7})' => 'AdminList::objectsSortCreate/$1/$2/$3',
            '(:segment)/(:segment)/(:segment)'                => '$2::$3/$1',
        ];
        $this->collection->map($routes);
    }

    public function testEmptyURIMatchesRoot(): void
    {
        $router = new Router($this->collection, $this->request);

        $router->handle('');

        $this->assertSame('\Home', $router->controllerName());
        $this->assertSame('index', $router->methodName());
    }

    public function testZeroAsURIPath(): void
    {
        $router = new Router($this->collection, $this->request);

        $this->expectException(PageNotFoundException::class);

        $router->handle('0');
    }

    public function testNotPermittedChars(): void
    {
        $router = new Router($this->collection, $this->request);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('The URI you submitted has disallowed characters: "<a>"');

        $router->handle('test/%3Ca%3E');
    }

    public function testURIMapsToController(): void
    {
        $router = new Router($this->collection, $this->request);

        $router->handle('users');

        $this->assertSame('\Users', $router->controllerName());
        $this->assertSame('index', $router->methodName());
    }

    public function testURIWithTrailingSlashMapsToController(): void
    {
        $router = new Router($this->collection, $this->request);

        $router->handle('users/');

        $this->assertSame('\Users', $router->controllerName());
        $this->assertSame('index', $router->methodName());
    }

    public function testURIMapsToControllerAltMethod(): void
    {
        $router = new Router($this->collection, $this->request);

        $router->handle('posts');

        $this->assertSame('\Blog', $router->controllerName());
        $this->assertSame('posts', $router->methodName());
    }

    public function testURIMapsToNamespacedController(): void
    {
        $router = new Router($this->collection, $this->request);

        $router->handle('pages');

        $this->assertSame('\App\Pages', $router->controllerName());
        $this->assertSame('list_all', $router->methodName());
    }

    public function testURIMapsParamsToBackReferences(): void
    {
        $router = new Router($this->collection, $this->request);

        $router->handle('posts/123');

        $this->assertSame('show', $router->methodName());
        $this->assertSame(['123'], $router->params());
    }

    public function testURIMapsParamsToRearrangedBackReferences(): void
    {
        $router = new Router($this->collection, $this->request);

        $router->handle('posts/123/edit');

        $this->assertSame('edit', $router->methodName());
        $this->assertSame(['123'], $router->params());
    }

    public function testURIMapsParamsToBackReferencesWithUnused(): void
    {
        $router = new Router($this->collection, $this->request);

        $router->handle('books/123/sometitle/456');

        $this->assertSame('show', $router->methodName());
        $this->assertSame(['456', '123'], $router->params());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/672
     */
    public function testURIMapsParamsWithMany(): void
    {
        $router = new Router($this->collection, $this->request);

        $router->handle('objects/123/sort/abc/FOO');

        $this->assertSame('objectsSortCreate', $router->methodName());
        $this->assertSame(['123', 'abc', 'FOO'], $router->params());
    }

    public function testURIWithTrailingSlashMapsParamsWithMany(): void
    {
        $router = new Router($this->collection, $this->request);

        $router->handle('objects/123/sort/abc/FOO/');

        $this->assertSame('objectsSortCreate', $router->methodName());
        $this->assertSame(['123', 'abc', 'FOO'], $router->params());
    }

    public function testClosures(): void
    {
        $router = new Router($this->collection, $this->request);

        $router->handle('closure/123/alpha');

        $closure = $router->controllerName();

        $expects = $closure(...$router->params());

        $this->assertIsCallable($router->controllerName());
        $this->assertSame($expects, '123-alpha');
    }

    public function testAutoRouteFindsDefaultControllerAndMethod(): void
    {
        $this->collection->setAutoRoute(true);
        $this->collection->setDefaultController('Mycontroller');
        $this->collection->setDefaultMethod('getSomemethod');
        $router = new Router($this->collection, $this->request);

        copy(TESTPATH . '_support/_controller/Mycontroller.php', APPPATH . 'Controllers/Mycontroller.php');

        $router->autoRoute('/');

        unlink(APPPATH . 'Controllers/Mycontroller.php');

        $this->assertSame('Mycontroller', $router->controllerName());
        $this->assertSame('getSomemethod', $router->methodName());
    }

    public function testAutoRouteFindsControllerWithFileAndMethod(): void
    {
        $this->collection->setAutoRoute(true);
        $router = new Router($this->collection, $this->request);

        copy(TESTPATH . '_support/_controller/Mycontroller.php', APPPATH . 'Controllers/Mycontroller.php');

        $router->autoRoute('mycontroller/getSomemethod');

        unlink(APPPATH . 'Controllers/Mycontroller.php');

        $this->assertSame('Mycontroller', $router->controllerName());
        $this->assertSame('getSomemethod', $router->methodName());
    }

    public function testAutoRouteFindsControllerWithFile(): void
    {
        $this->collection->setAutoRoute(true);
        $router = new Router($this->collection, $this->request);

        copy(TESTPATH . '_support/_controller/Mycontroller.php', APPPATH . 'Controllers/Mycontroller.php');

        $router->autoRoute('mycontroller');

        unlink(APPPATH . 'Controllers/Mycontroller.php');

        $this->assertSame('Mycontroller', $router->controllerName());
        $this->assertSame('index', $router->methodName());
    }

    public function testAutoRouteFindsControllerWithSubfolder(): void
    {
        $this->collection->setAutoRoute(true);
        $router = new Router($this->collection, $this->request);

        mkdir(APPPATH . 'Controllers/Subfolder');

        copy(TESTPATH . '_support/_controller/Subfolder/Mycontroller.php', APPPATH . 'Controllers/Subfolder/Mycontroller.php');

        $router->autoRoute('subfolder/mycontroller/getSomemethod');

        unlink(APPPATH . 'Controllers/Subfolder/Mycontroller.php');

        rmdir(APPPATH . 'Controllers/Subfolder');

        $this->assertSame('Mycontroller', $router->controllerName());
        $this->assertSame('getSomemethod', $router->methodName());
    }

    public function testAutoRouteFindsDashedSubfolder(): void
    {
        $this->collection->setAutoRoute(true);
        $router = new Router($this->collection, $this->request);
        $router->setTranslateURIDashes(true);

        mkdir(APPPATH . 'Controllers/Dash_folder');
        copy(TESTPATH . '_support/_controller/Dash_folder/Mycontroller.php', APPPATH . 'Controllers/Dash_folder/Mycontroller.php');

        $router->autoRoute('dash-folder/mycontroller/somemethod');

        unlink(APPPATH . 'Controllers/Dash_folder/Mycontroller.php');
        rmdir(APPPATH . 'Controllers/Dash_folder');

        $this->assertSame('Dash_folder/', $router->directory());
        $this->assertSame('Mycontroller', $router->controllerName());
        $this->assertSame('somemethod', $router->methodName());
    }

    public function testAutoRouteFindsDashedController(): void
    {
        $this->collection->setAutoRoute(true);
        $router = new Router($this->collection, $this->request);
        $router->setTranslateURIDashes(true);

        mkdir(APPPATH . 'Controllers/Dash_folder');
        copy(TESTPATH . '_support/_controller/Dash_folder/Dash_controller.php', APPPATH . 'Controllers/Dash_folder/Dash_controller.php');

        $router->autoRoute('dash-folder/dash-controller/getSomemethod');

        unlink(APPPATH . 'Controllers/Dash_folder/Dash_controller.php');
        rmdir(APPPATH . 'Controllers/Dash_folder');

        $this->assertSame('Dash_folder/', $router->directory());
        $this->assertSame('Dash_controller', $router->controllerName());
        $this->assertSame('getSomemethod', $router->methodName());
    }

    public function testAutoRouteFindsDashedMethod(): void
    {
        $this->collection->setAutoRoute(true);
        $router = new Router($this->collection, $this->request);
        $router->setTranslateURIDashes(true);

        mkdir(APPPATH . 'Controllers/Dash_folder');
        copy(TESTPATH . '_support/_controller/Dash_folder/Dash_controller.php', APPPATH . 'Controllers/Dash_folder/Dash_controller.php');

        $router->autoRoute('dash-folder/dash-controller/getDash_method');

        unlink(APPPATH . 'Controllers/Dash_folder/Dash_controller.php');
        rmdir(APPPATH . 'Controllers/Dash_folder');

        $this->assertSame('Dash_folder/', $router->directory());
        $this->assertSame('Dash_controller', $router->controllerName());
        $this->assertSame('getDash_method', $router->methodName());
    }

    public function testAutoRouteFindsDefaultDashFolder(): void
    {
        $this->collection->setAutoRoute(true);
        $router = new Router($this->collection, $this->request);
        $router->setTranslateURIDashes(true);

        mkdir(APPPATH . 'Controllers/Dash_folder');
        copy(TESTPATH . '_support/_controller/Dash_folder/Home.php', APPPATH . 'Controllers/Dash_folder/Home.php');

        $router->autoRoute('dash-folder');

        unlink(APPPATH . 'Controllers/Dash_folder/Home.php');
        rmdir(APPPATH . 'Controllers/Dash_folder');

        $this->assertSame('Dash_folder/', $router->directory());
        $this->assertSame('Home', $router->controllerName());
        $this->assertSame('index', $router->methodName());
    }

    public function testAutoRouteFindsMByteDir(): void
    {
        $this->collection->setAutoRoute(true);
        $router = new Router($this->collection, $this->request);
        $router->setTranslateURIDashes(true);

        mkdir(APPPATH . 'Controllers/Φ');
        copy(TESTPATH . '_support/_controller/Φ/Home.php', APPPATH . 'Controllers/Φ/Home.php');

        $router->autoRoute('Φ');

        unlink(APPPATH . 'Controllers/Φ/Home.php');
        rmdir(APPPATH . 'Controllers/Φ');

        $this->assertSame('Φ/', $router->directory());
        $this->assertSame('Home', $router->controllerName());
        $this->assertSame('index', $router->methodName());
    }

    public function testAutoRouteFindsMByteController(): void
    {
        $this->collection->setAutoRoute(true);
        $router = new Router($this->collection, $this->request);
        $router->setTranslateURIDashes(true);

        copy(TESTPATH . '_support/_controller/Φ.php', APPPATH . 'Controllers/Φ.php');

        $router->autoRoute('Φ');

        unlink(APPPATH . 'Controllers/Φ.php');

        $this->assertSame('Φ', $router->controllerName());
        $this->assertSame('index', $router->methodName());
    }

    public function testAutoRouteRejectsSingleDot(): void
    {
        $this->collection->setAutoRoute(true);
        $router = new Router($this->collection, $this->request);
        $router->setTranslateURIDashes(true);

        $this->expectException(PageNotFoundException::class);

        $router->autoRoute('.');
    }

    public function testAutoRouteRejectsDoubleDot(): void
    {
        $this->collection->setAutoRoute(true);
        $router = new Router($this->collection, $this->request);
        $router->setTranslateURIDashes(true);

        $this->expectException(PageNotFoundException::class);

        $router->autoRoute('..');
    }

    public function testAutoRouteRejectsMidDot(): void
    {
        $this->collection->setAutoRoute(true);
        $router = new Router($this->collection, $this->request);
        $router->setTranslateURIDashes(true);

        $this->expectException(PageNotFoundException::class);

        $router->autoRoute('Foo.bar');
    }

    public function testAutoRouteRejectsInitController(): void
    {
        $this->collection->setAutoRoute(true);
        $router = new Router($this->collection, $this->request);
        $router->setTranslateURIDashes(true);

        $this->expectException(PageNotFoundException::class);

        $router->autoRoute('home/initController');
    }

    public function testExceptionMessageWhenRouteNotFound(): void
    {
        $router = new Router($this->collection, $this->request);

        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage("Can't find a route for 'GET: url/not-exists'");

        $router->handle('url/not-exists');
    }

    public function testDetectsLocales(): void
    {
        $router = new Router($this->collection, $this->request);

        $router->handle('fr/pages');

        $this->assertTrue($router->hasLocale());
        $this->assertSame('fr', $router->getLocale());

        $router->handle('test/123/lang/bg');

        $this->assertTrue($router->hasLocale());
        $this->assertSame('bg', $router->getLocale());
    }

    public function testRouteResource(): void
    {
        $router = new Router($this->collection, $this->request);

        $router->handle('admin/admins');

        $this->assertSame('\App\Admin\Admins', $router->controllerName());
        $this->assertSame('list_all', $router->methodName());
    }

    public function testRouteWithSlashInControllerName(): void
    {
        $this->expectExceptionMessage(
            'The namespace delimiter is a backslash (\), not a slash (/). Route handler: "\App/Admin/Admins::edit_show/$1"'
        );

        $router = new Router($this->collection, $this->request);

        $router->handle('admin/admins/edit/1');
    }

    public function testRouteWithLeadingSlash(): void
    {
        $router = new Router($this->collection, $this->request);

        $router->handle('some/slash');

        $this->assertSame('\App\Slash', $router->controllerName());
        $this->assertSame('index', $router->methodName());
    }

    public function testRouteWithDynamicController(): void
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('A dynamic controller is not allowed for security reasons. Route handler: "\$2::$3/$1"');

        $router = new Router($this->collection, $this->request);

        $router->handle('en/zoo/bar');
    }

    // options need to be declared separately, to not confuse PHPCBF
    public function testMatchedRouteOptions(): void
    {
        $optionsFoo = [
            'as'  => 'login',
            'foo' => 'baz',
        ];
        $this->collection->add('foo', static function (): void {
        }, $optionsFoo);
        $optionsBaz = [
            'as'  => 'admin',
            'foo' => 'bar',
        ];
        $this->collection->add('baz', static function (): void {
        }, $optionsBaz);

        $router = new Router($this->collection, $this->request);

        $router->handle('foo');

        $this->assertSame($router->getMatchedRouteOptions(), ['as' => 'login', 'foo' => 'baz']);
    }

    public function testRouteWorksWithFilters(): void
    {
        $collection = $this->collection;

        $collection->group('foo', ['filter' => 'test'], static function ($routes): void {
            $routes->add('bar', 'TestController::foobar');
        });

        $router = new Router($collection, $this->request);

        $router->handle('foo/bar');

        $this->assertSame('\TestController', $router->controllerName());
        $this->assertSame('foobar', $router->methodName());
        $this->assertSame(['test'], $router->getFilters());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1247
     */
    public function testGroupedResourceRoutesWithFilters(): void
    {
        $group = [
            'api',
            [
                'namespace' => 'App\Controllers\Api',
                'filter'    => 'api-auth',
            ],
            static function (RouteCollection $routes): void {
                $routes->resource('posts', [
                    'controller' => 'PostController',
                ]);
            },
        ];

        // GET
        $this->collection->group(...$group);

        $router = new Router($this->collection, $this->request);

        $router->handle('api/posts');

        $this->assertSame('\App\Controllers\Api\PostController', $router->controllerName());
        $this->assertSame('index', $router->methodName());
        $this->assertSame(['api-auth'], $router->getFilters());

        $router->handle('api/posts/new');

        $this->assertSame('\App\Controllers\Api\PostController', $router->controllerName());
        $this->assertSame('new', $router->methodName());
        $this->assertSame(['api-auth'], $router->getFilters());

        $router->handle('api/posts/50');

        $this->assertSame('\App\Controllers\Api\PostController', $router->controllerName());
        $this->assertSame('show', $router->methodName());
        $this->assertSame(['api-auth'], $router->getFilters());

        $router->handle('api/posts/50/edit');

        $this->assertSame('\App\Controllers\Api\PostController', $router->controllerName());
        $this->assertSame('edit', $router->methodName());
        $this->assertSame(['api-auth'], $router->getFilters());

        // POST
        $this->collection->group(...$group);

        $router = new Router($this->collection, $this->request);
        $this->collection->setHTTPVerb(Method::POST);

        $router->handle('api/posts');

        $this->assertSame('\App\Controllers\Api\PostController', $router->controllerName());
        $this->assertSame('create', $router->methodName());
        $this->assertSame(['api-auth'], $router->getFilters());

        // PUT
        $this->collection->group(...$group);

        $router = new Router($this->collection, $this->request);
        $this->collection->setHTTPVerb(Method::PUT);

        $router->handle('api/posts/50');

        $this->assertSame('\App\Controllers\Api\PostController', $router->controllerName());
        $this->assertSame('update', $router->methodName());
        $this->assertSame(['api-auth'], $router->getFilters());

        // PATCH
        $this->collection->group(...$group);

        $router = new Router($this->collection, $this->request);
        $this->collection->setHTTPVerb(Method::PATCH);

        $router->handle('api/posts/50');

        $this->assertSame('\App\Controllers\Api\PostController', $router->controllerName());
        $this->assertSame('update', $router->methodName());
        $this->assertSame(['api-auth'], $router->getFilters());

        // DELETE
        $this->collection->group(...$group);

        $router = new Router($this->collection, $this->request);
        $this->collection->setHTTPVerb(Method::DELETE);

        $router->handle('api/posts/50');

        $this->assertSame('\App\Controllers\Api\PostController', $router->controllerName());
        $this->assertSame('delete', $router->methodName());
        $this->assertSame(['api-auth'], $router->getFilters());
    }

    public function testRouteWorksWithClassnameFilter(): void
    {
        $collection = $this->collection;

        $collection->add('foo', 'TestController::foo', ['filter' => Customfilter::class]);
        $router = new Router($collection, $this->request);

        $router->handle('foo');

        $this->assertSame('\TestController', $router->controllerName());
        $this->assertSame('foo', $router->methodName());
        $this->assertSame([Customfilter::class], $router->getFilters());

        $this->resetServices();
    }

    public function testRouteWorksWithMultipleFilters(): void
    {
        $collection = $this->collection;

        $collection->add('foo', 'TestController::foo', ['filter' => ['filter1', 'filter2:param']]);
        $router = new Router($collection, $this->request);

        $router->handle('foo');

        $this->assertSame('\TestController', $router->controllerName());
        $this->assertSame('foo', $router->methodName());
        $this->assertSame(['filter1', 'filter2:param'], $router->getFilters());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1240
     */
    public function testMatchesCorrectlyWithMixedVerbs(): void
    {
        $this->collection->setHTTPVerb(Method::GET);

        $this->collection->add('/', 'Home::index');
        $this->collection->get('news', 'News::index');
        $this->collection->get('news/(:segment)', 'News::view/$1');
        $this->collection->add('(:any)', 'Pages::view/$1');

        $router = new Router($this->collection, $this->request);

        $router->handle('/');
        $this->assertSame('\Home', $router->controllerName());
        $this->assertSame('index', $router->methodName());

        $router->handle('news');
        $this->assertSame('\News', $router->controllerName());
        $this->assertSame('index', $router->methodName());

        $router->handle('news/daily');
        $this->assertSame('\News', $router->controllerName());
        $this->assertSame('view', $router->methodName());

        $router->handle('about');
        $this->assertSame('\Pages', $router->controllerName());
        $this->assertSame('view', $router->methodName());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1354
     */
    public function testRouteOrder(): void
    {
        $this->collection->post('auth', 'Main::auth_post');
        $this->collection->add('auth', 'Main::index');

        $router = new Router($this->collection, $this->request);
        $this->collection->setHTTPVerb(Method::POST);

        $router->handle('auth');
        $this->assertSame('\Main', $router->controllerName());
        $this->assertSame('auth_post', $router->methodName());
    }

    public function testRoutePriorityOrder(): void
    {
        $this->collection->add('main', 'Main::index');
        $this->collection->add('(.*)', 'Main::wildcard', ['priority' => 1]);
        $this->collection->add('module', 'Module::index');

        $router = new Router($this->collection, $this->request);

        $this->collection->setHTTPVerb(Method::GET);

        $router->handle('module');
        $this->assertSame('\Main', $router->controllerName());
        $this->assertSame('wildcard', $router->methodName());

        $this->collection->setPrioritize();

        $router->handle('module');
        $this->assertSame('\Module', $router->controllerName());
        $this->assertSame('index', $router->methodName());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1564
     */
    public function testTranslateURIDashes(): void
    {
        $router = new Router($this->collection, $this->request);

        $router->handle('user-setting/show-list');

        $router->setTranslateURIDashes(true);

        $this->assertSame('\User_setting', $router->controllerName());
        $this->assertSame('show_list', $router->methodName());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1564
     */
    public function testTranslateURIDashesForParams(): void
    {
        $router = new Router($this->collection, $this->request);
        $router->setTranslateURIDashes(true);

        $router->handle('user-setting/2018-12-02');

        $this->assertSame('\User_setting', $router->controllerName());
        $this->assertSame('detail', $router->methodName());
        $this->assertSame(['2018-12-02'], $router->params());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1564
     */
    public function testTranslateURIDashesForAutoRoute(): void
    {
        $this->collection->setAutoRoute(true);
        $router = new Router($this->collection, $this->request);
        $router->setTranslateURIDashes(true);

        copy(TESTPATH . '_support/_controller/Admin_user.php', APPPATH . 'Controllers/Admin_user.php');

        $router->autoRoute('admin_user/show_list');

        unlink(APPPATH . 'Controllers/Admin_user.php');

        $this->assertSame('Admin_user', $router->controllerName());
        $this->assertSame('show_list', $router->methodName());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/2032
     */
    public function testAutoRouteMatchesZeroParams(): void
    {
        $this->collection->setAutoRoute(true);
        $router = new Router($this->collection, $this->request);

        copy(TESTPATH . '_support/_controller/Mycontroller.php', APPPATH . 'Controllers/Mycontroller.php');

        $router->autoRoute('mycontroller/getSomemethod/0/abc');

        $this->assertSame('Mycontroller', $router->controllerName());
        $this->assertSame('getSomemethod', $router->methodName());

        unlink(APPPATH . 'Controllers/Mycontroller.php');

        $expected = [
            '0',
            'abc',
        ];
        $this->assertSame($expected, $router->params());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/2965
     */
    public function testAutoRouteMethodEmpty(): void
    {
        $this->collection->setAutoRoute(true);
        $router = new Router($this->collection, $this->request);
        $this->collection->setAutoRoute(true);

        $router->handle('Home/');

        $this->assertSame('Home', $router->controllerName());
        $this->assertSame('index', $router->methodName());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/3169
     */
    public function testRegularExpressionWithUnicode(): void
    {
        $config                    = config(App::class);
        $config->permittedURIChars = 'a-z 0-9~%.:_\-\x{0980}-\x{09ff}';

        $this->collection->get('news/([a-z0-9\x{0980}-\x{09ff}-]+)', 'News::view/$1');

        $router = new Router($this->collection, $this->request);

        $router->handle('news/a0%E0%A6%80%E0%A7%BF-');
        $this->assertSame('\News', $router->controllerName());
        $this->assertSame('view', $router->methodName());

        $expected = [
            'a0ঀ৿-',
        ];
        $this->assertSame($expected, $router->params());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/3169
     */
    public function testRegularExpressionPlaceholderWithUnicode(): void
    {
        $config                    = config(App::class);
        $config->permittedURIChars = 'a-z 0-9~%.:_\-\x{0980}-\x{09ff}';

        $this->collection->addPlaceholder('custom', '[a-z0-9\x{0980}-\x{09ff}-]+');
        $this->collection->get('news/(:custom)', 'News::view/$1');

        $router = new Router($this->collection, $this->request);

        $router->handle('news/a0%E0%A6%80%E0%A7%BF-');
        $this->assertSame('\News', $router->controllerName());
        $this->assertSame('view', $router->methodName());

        $expected = [
            'a0ঀ৿-',
        ];
        $this->assertSame($expected, $router->params());
    }

    public function testRouterPriorDirectory(): void
    {
        $this->collection->setAutoRoute(true);
        $router = new Router($this->collection, $this->request);

        mkdir(APPPATH . 'Controllers/foo');
        mkdir(APPPATH . 'Controllers/foo/bar');
        mkdir(APPPATH . 'Controllers/foo/bar/baz');
        copy(TESTPATH . '_support/_controller/foo/bar/baz/Some_controller.php', APPPATH . 'Controllers/foo/bar/baz/Some_controller.php');

        $router->setDirectory('foo/bar/baz', false, true);
        $router->handle('Some_controller/some_method/param1/param2/param3');

        unlink(APPPATH . 'Controllers/foo/bar/baz/Some_controller.php');
        rmdir(APPPATH . 'Controllers/foo/bar/baz');
        rmdir(APPPATH . 'Controllers/foo/bar');
        rmdir(APPPATH . 'Controllers/foo');

        $this->assertSame('foo/bar/baz/', $router->directory());
        $this->assertSame('Some_controller', $router->controllerName());
        $this->assertSame('some_method', $router->methodName());
    }

    public function testSetDirectoryValid(): void
    {
        $this->collection->setAutoRoute(true);
        $router = new Router($this->collection, $this->request);
        $router->setDirectory('foo/bar/baz', false, true);

        $this->assertSame('foo/bar/baz/', $router->directory());
    }

    public function testSetDirectoryInvalid(): void
    {
        $this->collection->setAutoRoute(true);
        $router = new Router($this->collection, $this->request);
        $router->setDirectory('foo/bad-segment/bar', false, true);

        $internal = $this->getPrivateProperty($router, 'directory');

        $this->assertNull($internal);
        $this->assertSame('', $router->directory());
    }

    /**
     * @dataProvider provideRedirectRoute
     */
    public function testRedirectRoute(
        string $route,
        string $redirectFrom,
        string $redirectTo,
        string $url,
        string $expectedPath,
        string $alias
    ): void {
        $collection = clone $this->collection;
        $collection->resetRoutes();

        $collection->get($route, 'Controller::method', ['as' => $alias]);
        $collection->addRedirect($redirectFrom, $redirectTo);

        $this->expectException(RedirectException::class);
        $this->expectExceptionMessage($expectedPath);

        $router = new Router($collection, $this->request);
        $router->handle($url);
    }

    public static function provideRedirectRoute(): iterable
    {
        // [$route, $redirectFrom, $redirectTo, $url, $expectedPath, $alias]
        return [
            ['posts', 'articles', 'posts', '/articles', 'posts', 'alias'],
            ['posts', 'articles', 'alias', '/articles', 'posts', 'alias'],
            ['post/(:num)', 'article/(:num)', 'post/$1', '/article/1', 'post/1', 'alias'],
            ['post/(:num)', 'article/(:num)', 'alias', '/article/1', 'post/1', 'alias'],
            ['post/(:num)/comment/(:any)', 'article/(:num)/(:any)', 'post/$1/comment/$2', '/article/1/2', 'post/1/comment/2', 'alias'],
            ['post/(:segment)/comment/(:any)', 'article/(:num)/(:any)', 'alias', '/article/1/2', 'post/1/comment/2', 'alias'],
        ];
    }

    public function testRoutePlaceholderAnyWithMultipleSegmentsParamFalse(): void
    {
        $this->createRouteCollection();
        $this->collection->get('product/(:any)', 'Catalog::productLookup/$1');
        $router = new Router($this->collection, $this->request);

        $router->handle('product/123/456');

        $this->assertSame('\Catalog', $router->controllerName());
        $this->assertSame('productLookup', $router->methodName());
        $this->assertSame(['123', '456'], $router->params());
    }

    public function testRoutePlaceholderAnyWithMultipleSegmentsParamTrue(): void
    {
        $routingConfig                           = new Routing();
        $routingConfig->multipleSegmentsOneParam = true;
        Factories::injectMock('config', Routing::class, $routingConfig);

        $this->createRouteCollection($routingConfig);
        $this->collection->get('product/(:any)', 'Catalog::productLookup/$1');
        $router = new Router($this->collection, $this->request);

        $router->handle('product/123/456');

        $this->assertSame('\Catalog', $router->controllerName());
        $this->assertSame('productLookup', $router->methodName());
        $this->assertSame(['123/456'], $router->params());
    }
}
