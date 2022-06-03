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

    public function testAutoRouteFindsDefaultControllerAndMethodGet()
    {
        $this->collection->setDefaultController('Index');

        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('/');

        $this->assertNull($directory);
        $this->assertSame('\\' . Index::class, $controller);
        $this->assertSame('getIndex', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsDefaultControllerAndMethodPost()
    {
        $this->collection->setDefaultController('Index');

        $router = $this->createNewAutoRouter('post');

        [$directory, $controller, $method, $params]
            = $router->getRoute('/');

        $this->assertNull($directory);
        $this->assertSame('\\' . Index::class, $controller);
        $this->assertSame('postIndex', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsControllerWithFileAndMethod()
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('mycontroller/somemethod');

        $this->assertNull($directory);
        $this->assertSame('\\' . Mycontroller::class, $controller);
        $this->assertSame('getSomemethod', $method);
        $this->assertSame([], $params);
    }

    public function testFindsControllerAndMethodAndParam()
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('mycontroller/somemethod/a');

        $this->assertNull($directory);
        $this->assertSame('\\' . Mycontroller::class, $controller);
        $this->assertSame('getSomemethod', $method);
        $this->assertSame(['a'], $params);
    }

    public function testUriParamCountIsGreaterThanMethodParams()
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage(
            'Handler:\CodeIgniter\Router\Controllers\Mycontroller::getSomemethod, URI:mycontroller/somemethod/a/b'
        );

        $router = $this->createNewAutoRouter();

        $router->getRoute('mycontroller/somemethod/a/b');
    }

    public function testAutoRouteFindsControllerWithFile()
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('mycontroller');

        $this->assertNull($directory);
        $this->assertSame('\\' . Mycontroller::class, $controller);
        $this->assertSame('getIndex', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsControllerWithSubfolder()
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('subfolder/mycontroller/somemethod');

        $this->assertSame('Subfolder/', $directory);
        $this->assertSame('\\' . \CodeIgniter\Router\Controllers\Subfolder\Mycontroller::class, $controller);
        $this->assertSame('getSomemethod', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsDashedSubfolder()
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('dash-folder/mycontroller/somemethod');

        $this->assertSame('Dash_folder/', $directory);
        $this->assertSame(
            '\\' . \CodeIgniter\Router\Controllers\Dash_folder\Mycontroller::class,
            $controller
        );
        $this->assertSame('getSomemethod', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsDashedController()
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('dash-folder/dash-controller/somemethod');

        $this->assertSame('Dash_folder/', $directory);
        $this->assertSame('\\' . Dash_controller::class, $controller);
        $this->assertSame('getSomemethod', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsDashedMethod()
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('dash-folder/dash-controller/dash-method');

        $this->assertSame('Dash_folder/', $directory);
        $this->assertSame('\\' . Dash_controller::class, $controller);
        $this->assertSame('getDash_method', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsDefaultDashFolder()
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('dash-folder');

        $this->assertSame('Dash_folder/', $directory);
        $this->assertSame('\\' . Home::class, $controller);
        $this->assertSame('getIndex', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteRejectsSingleDot()
    {
        $this->expectException(PageNotFoundException::class);

        $router = $this->createNewAutoRouter();

        $router->getRoute('.');
    }

    public function testAutoRouteRejectsDoubleDot()
    {
        $this->expectException(PageNotFoundException::class);

        $router = $this->createNewAutoRouter();

        $router->getRoute('..');
    }

    public function testAutoRouteRejectsMidDot()
    {
        $this->expectException(PageNotFoundException::class);

        $router = $this->createNewAutoRouter();

        $router->getRoute('foo.bar');
    }

    public function testRejectsDefaultControllerPath()
    {
        $this->expectException(PageNotFoundException::class);

        $router = $this->createNewAutoRouter();

        $router->getRoute('home');
    }

    public function testRejectsDefaultControllerAndDefaultMethodPath()
    {
        $this->expectException(PageNotFoundException::class);

        $router = $this->createNewAutoRouter();

        $router->getRoute('home/index');
    }

    public function testRejectsDefaultMethodPath()
    {
        $this->expectException(PageNotFoundException::class);

        $router = $this->createNewAutoRouter();

        $router->getRoute('mycontroller/index');
    }

    public function testRejectsControllerWithRemapMethod()
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage(
            'AutoRouterImproved does not support `_remap()` method. Controller:\CodeIgniter\Router\Controllers\Remap'
        );

        $router = $this->createNewAutoRouter();

        $router->getRoute('remap/test');
    }
}
