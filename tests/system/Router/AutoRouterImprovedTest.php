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
use CodeIgniter\HTTP\Method;
use CodeIgniter\Router\Controllers\BlogController;
use CodeIgniter\Router\Controllers\Dash_folder\Dash_controller;
use CodeIgniter\Router\Controllers\Dash_folder\Home;
use CodeIgniter\Router\Controllers\Index;
use CodeIgniter\Router\Controllers\Mycontroller;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Modules;
use Config\Routing;

/**
 * @internal
 *
 * @group Others
 */
final class AutoRouterImprovedTest extends CIUnitTestCase
{
    private RouteCollection $collection;

    protected function setUp(): void
    {
        parent::setUp();

        $moduleConfig          = new Modules();
        $moduleConfig->enabled = false;
        $this->collection      = new RouteCollection(Services::locator(), $moduleConfig, new Routing());
    }

    private function createNewAutoRouter($namespace = 'CodeIgniter\Router\Controllers'): AutoRouterImproved
    {
        return new AutoRouterImproved(
            [],
            $namespace,
            $this->collection->getDefaultController(),
            $this->collection->getDefaultMethod(),
            true
        );
    }

    public function testAutoRouteFindsDefaultControllerAndMethodGet(): void
    {
        $this->collection->setDefaultController('Index');

        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('/', Method::GET);

        $this->assertNull($directory);
        $this->assertSame('\\' . Index::class, $controller);
        $this->assertSame('getIndex', $method);
        $this->assertSame([], $params);
        $this->assertSame([
            'controller' => null,
            'method'     => null,
            'params'     => null,
        ], $router->getPos());
    }

    public function testAutoRouteFindsModuleDefaultControllerAndMethodGet(): void
    {
        $config               = config(Routing::class);
        $config->moduleRoutes = [
            'test' => 'CodeIgniter\Router\Controllers',
        ];
        Factories::injectMock('config', Routing::class, $config);

        $this->collection->setDefaultController('Index');

        $router = $this->createNewAutoRouter('App/Controllers');

        [$directory, $controller, $method, $params]
            = $router->getRoute('test', Method::GET);

        $this->assertNull($directory);
        $this->assertSame('\\' . Index::class, $controller);
        $this->assertSame('getIndex', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsDefaultControllerAndMethodPost(): void
    {
        $this->collection->setDefaultController('Index');

        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('/', Method::POST);

        $this->assertNull($directory);
        $this->assertSame('\\' . Index::class, $controller);
        $this->assertSame('postIndex', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsControllerWithFileAndMethod(): void
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('mycontroller/somemethod', Method::GET);

        $this->assertNull($directory);
        $this->assertSame('\\' . Mycontroller::class, $controller);
        $this->assertSame('getSomemethod', $method);
        $this->assertSame([], $params);
        $this->assertSame([
            'controller' => 0,
            'method'     => 1,
            'params'     => null,
        ], $router->getPos());
    }

    public function testFindsControllerAndMethodAndParam(): void
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('mycontroller/somemethod/a', Method::GET);

        $this->assertNull($directory);
        $this->assertSame('\\' . Mycontroller::class, $controller);
        $this->assertSame('getSomemethod', $method);
        $this->assertSame(['a'], $params);
        $this->assertSame([
            'controller' => 0,
            'method'     => 1,
            'params'     => 2,
        ], $router->getPos());
    }

    public function testUriParamCountIsGreaterThanMethodParams(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage(
            'Handler:\CodeIgniter\Router\Controllers\Mycontroller::getSomemethod, URI:mycontroller/somemethod/a/b'
        );

        $router = $this->createNewAutoRouter();

        $router->getRoute('mycontroller/somemethod/a/b', Method::GET);
    }

    public function testAutoRouteFindsControllerWithFile(): void
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('mycontroller', Method::GET);

        $this->assertNull($directory);
        $this->assertSame('\\' . Mycontroller::class, $controller);
        $this->assertSame('getIndex', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsControllerWithSubfolder(): void
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('subfolder/mycontroller/somemethod', Method::GET);

        $this->assertSame('Subfolder/', $directory);
        $this->assertSame('\\' . Controllers\Subfolder\Mycontroller::class, $controller);
        $this->assertSame('getSomemethod', $method);
        $this->assertSame([], $params);
        $this->assertSame([
            'controller' => 1,
            'method'     => 2,
            'params'     => null,
        ], $router->getPos());
    }

    public function testAutoRouteFindsControllerWithSubSubfolder(): void
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('subfolder/sub/mycontroller/somemethod', Method::GET);

        $this->assertSame('Subfolder/Sub/', $directory);
        $this->assertSame('\\' . Controllers\Subfolder\Sub\Mycontroller::class, $controller);
        $this->assertSame('getSomemethod', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsDashedSubfolder(): void
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('dash-folder/mycontroller/somemethod', Method::GET);

        $this->assertSame('Dash_folder/', $directory);
        $this->assertSame(
            '\\' . Controllers\Dash_folder\Mycontroller::class,
            $controller
        );
        $this->assertSame('getSomemethod', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsDashedController(): void
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('dash-folder/dash-controller/somemethod', Method::GET);

        $this->assertSame('Dash_folder/', $directory);
        $this->assertSame('\\' . Dash_controller::class, $controller);
        $this->assertSame('getSomemethod', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsDashedMethod(): void
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('dash-folder/dash-controller/dash-method', Method::GET);

        $this->assertSame('Dash_folder/', $directory);
        $this->assertSame('\\' . Dash_controller::class, $controller);
        $this->assertSame('getDash_method', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsDefaultDashFolder(): void
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('dash-folder', Method::GET);

        $this->assertSame('Dash_folder/', $directory);
        $this->assertSame('\\' . Home::class, $controller);
        $this->assertSame('getIndex', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFallbackToDefaultMethod(): void
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('index/15', Method::GET);

        $this->assertNull($directory);
        $this->assertSame('\\' . Index::class, $controller);
        $this->assertSame('getIndex', $method);
        $this->assertSame(['15'], $params);
        $this->assertSame([
            'controller' => 0,
            'method'     => null,
            'params'     => 1,
        ], $router->getPos());
    }

    public function testAutoRouteFallbackToDefaultControllerOneParam(): void
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('subfolder/15', Method::GET);

        $this->assertSame('Subfolder/', $directory);
        $this->assertSame('\\' . Controllers\Subfolder\Home::class, $controller);
        $this->assertSame('getIndex', $method);
        $this->assertSame(['15'], $params);
        $this->assertSame([
            'controller' => null,
            'method'     => null,
            'params'     => 1,
        ], $router->getPos());
    }

    public function testAutoRouteFallbackToDefaultControllerTwoParams(): void
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('subfolder/15/20', Method::GET);

        $this->assertSame('Subfolder/', $directory);
        $this->assertSame('\\' . Controllers\Subfolder\Home::class, $controller);
        $this->assertSame('getIndex', $method);
        $this->assertSame(['15', '20'], $params);
        $this->assertSame([
            'controller' => null,
            'method'     => null,
            'params'     => 1,
        ], $router->getPos());
    }

    public function testAutoRouteFallbackToDefaultControllerNoParams(): void
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('subfolder', Method::GET);

        $this->assertSame('Subfolder/', $directory);
        $this->assertSame('\\' . Controllers\Subfolder\Home::class, $controller);
        $this->assertSame('getIndex', $method);
        $this->assertSame([], $params);
        $this->assertSame([
            'controller' => null,
            'method'     => null,
            'params'     => null,
        ], $router->getPos());
    }

    public function testAutoRouteRejectsSingleDot(): void
    {
        $this->expectException(PageNotFoundException::class);

        $router = $this->createNewAutoRouter();

        $router->getRoute('.', Method::GET);
    }

    public function testAutoRouteRejectsDoubleDot(): void
    {
        $this->expectException(PageNotFoundException::class);

        $router = $this->createNewAutoRouter();

        $router->getRoute('..', Method::GET);
    }

    public function testAutoRouteRejectsMidDot(): void
    {
        $this->expectException(PageNotFoundException::class);

        $router = $this->createNewAutoRouter();

        $router->getRoute('foo.bar', Method::GET);
    }

    public function testRejectsDefaultControllerPath(): void
    {
        $this->expectException(PageNotFoundException::class);

        $router = $this->createNewAutoRouter();

        $router->getRoute('home', Method::GET);
    }

    public function testRejectsDefaultControllerAndDefaultMethodPath(): void
    {
        $this->expectException(PageNotFoundException::class);

        $router = $this->createNewAutoRouter();

        $router->getRoute('home/index', Method::GET);
    }

    public function testRejectsDefaultMethodPath(): void
    {
        $this->expectException(PageNotFoundException::class);

        $router = $this->createNewAutoRouter();

        $router->getRoute('mycontroller/index', Method::GET);
    }

    public function testRejectsControllerWithRemapMethod(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage(
            'AutoRouterImproved does not support `_remap()` method. Controller:\CodeIgniter\Router\Controllers\Remap'
        );

        $router = $this->createNewAutoRouter();

        $router->getRoute('remap/test', Method::GET);
    }

    public function testRejectsURIWithUnderscoreFolder(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage(
            'AutoRouterImproved prohibits access to the URI containing underscores ("dash_folder")'
        );

        $router = $this->createNewAutoRouter();

        $router->getRoute('dash_folder', Method::GET);
    }

    public function testRejectsURIWithUnderscoreController(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage(
            'AutoRouterImproved prohibits access to the URI containing underscores ("dash_controller")'
        );

        $router = $this->createNewAutoRouter();

        $router->getRoute('dash-folder/dash_controller/dash-method', Method::GET);
    }

    public function testRejectsURIWithUnderscoreMethod(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage(
            'AutoRouterImproved prohibits access to the URI containing underscores ("dash_method")'
        );

        $router = $this->createNewAutoRouter();

        $router->getRoute('dash-folder/dash-controller/dash_method', Method::GET);
    }

    public function testPermitsURIWithUnderscoreParam(): void
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('mycontroller/somemethod/a_b', Method::GET);

        $this->assertNull($directory);
        $this->assertSame('\\' . Mycontroller::class, $controller);
        $this->assertSame('getSomemethod', $method);
        $this->assertSame(['a_b'], $params);
    }

    public function testDoesNotTranslateDashInParam(): void
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('mycontroller/somemethod/a-b', Method::GET);

        $this->assertNull($directory);
        $this->assertSame('\\' . Mycontroller::class, $controller);
        $this->assertSame('getSomemethod', $method);
        $this->assertSame(['a-b'], $params);
    }

    /**
     * @dataProvider provideTranslateUriToCamelCase
     */
    public function testTranslateUriToCamelCase(
        string $uri,
        ?string $expDirectory,
        string $expController,
        string $expMethod,
        int $controllerPos,
        ?int $methodPos,
        ?int $paramPos
    ): void {
        $config                          = config(Routing::class);
        $config->translateUriToCamelCase = true;
        Factories::injectMock('config', Routing::class, $config);

        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute($uri, Method::GET);

        $this->assertSame($expDirectory, $directory);
        $this->assertSame($expController, $controller);
        $this->assertSame($expMethod, $method);
        $this->assertSame([], $params);
        $this->assertSame([
            'controller' => $controllerPos,
            'method'     => $methodPos,
            'params'     => $paramPos,
        ], $router->getPos());
    }

    public static function provideTranslateUriToCamelCase(): iterable
    {
        yield from [
            'controller-wz-dash' => [
                'blog-controller',
                null,
                '\\' . BlogController::class,
                'getIndex',
                0,
                null,
                null,
            ],
            'controller-and-method-wz-dash' => [
                'blog-controller/some-method',
                null,
                '\\' . BlogController::class,
                'getSomeMethod',
                0,
                1,
                null,
            ],
            'subfolder-controller-and-method-wz-dash' => [
                'subfolder/sub/blog-controller/some-method',
                'Subfolder/Sub/',
                '\\' . Controllers\Subfolder\Sub\BlogController::class,
                'getSomeMethod',
                2,
                3,
                null,
            ],
            'subfolder-wz-dash-controller-and-method-wz-dash' => [
                'sub-dir/blog-controller/some-method',
                'SubDir/',
                '\\' . Controllers\SubDir\BlogController::class,
                'getSomeMethod',
                1,
                2,
                null,
            ],
        ];
    }

    /**
     * @dataProvider provideRejectTranslateUriToCamelCase
     */
    public function testRejectTranslateUriToCamelCase(string $uri, string $expMsg): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage(
            $expMsg
        );

        $config                          = config(Routing::class);
        $config->translateUriToCamelCase = true;
        Factories::injectMock('config', Routing::class, $config);

        $router = $this->createNewAutoRouter();

        $router->getRoute($uri, Method::GET);
    }

    public static function provideRejectTranslateUriToCamelCase(): iterable
    {
        yield from [
            'controller-wo-dash' => [
                'blogcontroller',
                '"\CodeIgniter\Router\Controllers\Blogcontroller" is not found.',
            ],
            'controller-wz-double-dash' => [
                'blog--controller',
                'AutoRouterImproved prohibits access to the URI containing double dash ("blog--controller") when $translateUriToCamelCase is enabled. Please use the single dash. URI:blog--controller',
            ],
            'controller-wz-uppercase' => [
                'blogController',
                'AutoRouterImproved prohibits access to the URI containing uppercase letters ("blogController") when $translateUriToCamelCase is enabled. Please use the dash. URI:blogController',
            ],
            'method-wo-dash' => [
                'blog-controller/somemethod',
                '"\CodeIgniter\Router\Controllers\BlogController::getSomemethod()" is not found.',
            ],
            'method-wz-uppercase' => [
                'blog-controller/someMethod',
                'AutoRouterImproved prohibits access to the URI containing uppercase letters ("someMethod") when $translateUriToCamelCase is enabled. Please use the dash. URI:blog-controller/someMethod',
            ],
            'method-wz-double-dash' => [
                'blog-controller/some--method',
                'AutoRouterImproved prohibits access to the URI containing double dash ("some--method") when $translateUriToCamelCase is enabled. Please use the single dash. URI:blog-controller/some--method',
            ],
        ];
    }
}
