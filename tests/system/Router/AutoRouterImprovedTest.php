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
use CodeIgniter\Test\CIUnitTestCase;
use Config\Modules;

/**
 * @internal
 */
final class AutoRouterImprovedTest extends CIUnitTestCase
{
    /**
     * @var RouteCollection
     */
    protected $collection;

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
            $this->collection,
            'CodeIgniter\Router\Controllers',
            true,
            $httpVerb
        );
    }

    public function testAutoRouteFindsDefaultControllerAndMethodGet()
    {
        $this->collection->setDefaultController('Test');

        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('/');

        $this->assertNull($directory);
        $this->assertSame('\CodeIgniter\Router\Controllers\Test', $controller);
        $this->assertSame('getIndex', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsDefaultControllerAndMethodPost()
    {
        $this->collection->setDefaultController('Test');

        $router = $this->createNewAutoRouter('post');

        [$directory, $controller, $method, $params]
            = $router->getRoute('/');

        $this->assertNull($directory);
        $this->assertSame('\CodeIgniter\Router\Controllers\Test', $controller);
        $this->assertSame('postIndex', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsControllerWithFileAndMethod()
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('myController/someMethod');

        $this->assertNull($directory);
        $this->assertSame('\CodeIgniter\Router\Controllers\MyController', $controller);
        $this->assertSame('getSomeMethod', $method);
        $this->assertSame([], $params);
    }

    public function testFindsControllerAndMethodAndParam()
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('myController/someMethod/a');

        $this->assertNull($directory);
        $this->assertSame('\CodeIgniter\Router\Controllers\MyController', $controller);
        $this->assertSame('getSomeMethod', $method);
        $this->assertSame(['a'], $params);
    }

    public function testUriParamCountIsGreaterThanMethodParams()
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage(
            'Handler:\CodeIgniter\Router\Controllers\MyController::getSomeMethod, URI:myController/someMethod/a/b'
        );

        $router = $this->createNewAutoRouter();

        $router->getRoute('myController/someMethod/a/b');
    }

    public function testAutoRouteFindsControllerWithFile()
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('myController');

        $this->assertNull($directory);
        $this->assertSame('\CodeIgniter\Router\Controllers\MyController', $controller);
        $this->assertSame('getIndex', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsControllerWithSubfolder()
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('subfolder/myController/someMethod');

        $this->assertSame('Subfolder/', $directory);
        $this->assertSame('\CodeIgniter\Router\Controllers\Subfolder\MyController', $controller);
        $this->assertSame('getSomeMethod', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsDashedSubfolder()
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('dash-folder/mycontroller/somemethod');

        $this->assertSame('Dash_folder/', $directory);
        $this->assertSame('\CodeIgniter\Router\Controllers\Dash_folder\Mycontroller', $controller);
        $this->assertSame('getSomemethod', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsDashedController()
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('dash-folder/dash-controller/somemethod');

        $this->assertSame('Dash_folder/', $directory);
        $this->assertSame('\CodeIgniter\Router\Controllers\Dash_folder\Dash_controller', $controller);
        $this->assertSame('getSomemethod', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsDashedMethod()
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('dash-folder/dash-controller/dash-method');

        $this->assertSame('Dash_folder/', $directory);
        $this->assertSame('\CodeIgniter\Router\Controllers\Dash_folder\Dash_controller', $controller);
        $this->assertSame('getDash_method', $method);
        $this->assertSame([], $params);
    }

    public function testAutoRouteFindsDefaultDashFolder()
    {
        $router = $this->createNewAutoRouter();

        [$directory, $controller, $method, $params]
            = $router->getRoute('dash-folder');

        $this->assertSame('Dash_folder/', $directory);
        $this->assertSame('\CodeIgniter\Router\Controllers\Dash_folder\Home', $controller);
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

        $router->getRoute('test/index');
    }
}
