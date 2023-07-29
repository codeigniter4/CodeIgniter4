<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Router;

use CodeIgniter\Config\Services;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Router\Controllers\Dash_folder\Dash_controller;
use CodeIgniter\Router\Controllers\Dash_folder\Home;
use CodeIgniter\Router\Controllers\Index;
use CodeIgniter\Router\Controllers\Mycontroller;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Modules;

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
        $this->collection      = new RouteCollection(Services::locator(), $moduleConfig);
    }

    private function createNewAutoRouter(string $httpVerb = 'get'): AutoRouterImproved
    {
        return new AutoRouterImproved(
            [],
            'CodeIgniter\Router\Controllers',
            $this->collection->getDefaultController(),
            $this->collection->getDefaultMethod(),
            true,
            $httpVerb
        );
    }

    public function testAutoRouteFindsDefaultControllerAndMethodGet(): void
    {
        $this->collection->setDefaultController('Index');

        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('/', 'get');

        $this->assertNull($directory);
        $this->assertSame('\\' . Index::class, $controller);
        $this->assertSame('getIndex', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsDefaultControllerAndMethodPost(): void
    {
        $this->collection->setDefaultController('Index');

        $router = $this->createNewAutoRouter('post');

        [$directory, $controller, $method, $params]
            = $router->getRoute('/', 'post');

        $this->assertNull($directory);
        $this->assertSame('\\' . Index::class, $controller);
        $this->assertSame('postIndex', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsControllerWithFileAndMethod(): void
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('mycontroller/somemethod', 'get');

        $this->assertNull($directory);
        $this->assertSame('\\' . Mycontroller::class, $controller);
        $this->assertSame('getSomemethod', $method);
        $this->assertSame([], $params);
    }

    public function testFindsControllerAndMethodAndParam(): void
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('mycontroller/somemethod/a', 'get');

        $this->assertNull($directory);
        $this->assertSame('\\' . Mycontroller::class, $controller);
        $this->assertSame('getSomemethod', $method);
        $this->assertSame(['a'], $params);
    }

    public function testUriParamCountIsGreaterThanMethodParams(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage(
            'Handler:\CodeIgniter\Router\Controllers\Mycontroller::getSomemethod, URI:mycontroller/somemethod/a/b'
        );

        $router = $this->createNewAutoRouter();

        $router->getRoute('mycontroller/somemethod/a/b', 'get');
    }

    public function testAutoRouteFindsControllerWithFile(): void
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('mycontroller', 'get');

        $this->assertNull($directory);
        $this->assertSame('\\' . Mycontroller::class, $controller);
        $this->assertSame('getIndex', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsControllerWithSubfolder(): void
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('subfolder/mycontroller/somemethod', 'get');

        $this->assertSame('Subfolder/', $directory);
        $this->assertSame('\\' . \CodeIgniter\Router\Controllers\Subfolder\Mycontroller::class, $controller);
        $this->assertSame('getSomemethod', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsDashedSubfolder(): void
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('dash-folder/mycontroller/somemethod', 'get');

        $this->assertSame('Dash_folder/', $directory);
        $this->assertSame(
            '\\' . \CodeIgniter\Router\Controllers\Dash_folder\Mycontroller::class,
            $controller
        );
        $this->assertSame('getSomemethod', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsDashedController(): void
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('dash-folder/dash-controller/somemethod', 'get');

        $this->assertSame('Dash_folder/', $directory);
        $this->assertSame('\\' . Dash_controller::class, $controller);
        $this->assertSame('getSomemethod', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsDashedMethod(): void
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('dash-folder/dash-controller/dash-method', 'get');

        $this->assertSame('Dash_folder/', $directory);
        $this->assertSame('\\' . Dash_controller::class, $controller);
        $this->assertSame('getDash_method', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsDefaultDashFolder(): void
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('dash-folder', 'get');

        $this->assertSame('Dash_folder/', $directory);
        $this->assertSame('\\' . Home::class, $controller);
        $this->assertSame('getIndex', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteRejectsSingleDot(): void
    {
        $this->expectException(PageNotFoundException::class);

        $router = $this->createNewAutoRouter();

        $router->getRoute('.', 'get');
    }

    public function testAutoRouteRejectsDoubleDot(): void
    {
        $this->expectException(PageNotFoundException::class);

        $router = $this->createNewAutoRouter();

        $router->getRoute('..', 'get');
    }

    public function testAutoRouteRejectsMidDot(): void
    {
        $this->expectException(PageNotFoundException::class);

        $router = $this->createNewAutoRouter();

        $router->getRoute('foo.bar', 'get');
    }

    public function testRejectsDefaultControllerPath(): void
    {
        $this->expectException(PageNotFoundException::class);

        $router = $this->createNewAutoRouter();

        $router->getRoute('home', 'get');
    }

    public function testRejectsDefaultControllerAndDefaultMethodPath(): void
    {
        $this->expectException(PageNotFoundException::class);

        $router = $this->createNewAutoRouter();

        $router->getRoute('home/index', 'get');
    }

    public function testRejectsDefaultMethodPath(): void
    {
        $this->expectException(PageNotFoundException::class);

        $router = $this->createNewAutoRouter();

        $router->getRoute('mycontroller/index', 'get');
    }

    public function testRejectsControllerWithRemapMethod(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage(
            'AutoRouterImproved does not support `_remap()` method. Controller:\CodeIgniter\Router\Controllers\Remap'
        );

        $router = $this->createNewAutoRouter();

        $router->getRoute('remap/test', 'get');
    }
}
