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
use CodeIgniter\Router\Exceptions\RouterException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Modules;
use Generator;

/**
 * @internal
 *
 * @group Others
 */
final class RouteCollectionReverseRouteTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->resetServices(true);
        $this->resetFactories();
    }

    protected function getCollector(array $config = [], array $files = [], $moduleConfig = null)
    {
        $defaults = [
            'Config' => APPPATH . 'Config',
            'App'    => APPPATH,
        ];
        $config = array_merge($config, $defaults);

        Services::autoloader()->addNamespace($config);

        $loader = Services::locator();

        if ($moduleConfig === null) {
            $moduleConfig          = new Modules();
            $moduleConfig->enabled = false;
        }

        return (new RouteCollection($loader, $moduleConfig, new \Config\Routing()))->setHTTPVerb('get');
    }

    public function testReverseRoutingFindsSimpleMatch()
    {
        $routes = $this->getCollector();

        // @TODO Do not put any placeholder after (:any).
        //       Because the number of parameters passed to the controller method may change.
        $routes->add('path/(:any)/to/(:num)', 'myController::goto/$1/$2');

        $match = $routes->reverseRoute('myController::goto', 'string', 13);

        $this->assertSame('/path/string/to/13', $match);
    }

    public function testReverseRoutingWithLocaleAndFindsSimpleMatch()
    {
        $routes = $this->getCollector();

        // @TODO Do not put any placeholder after (:any).
        //       Because the number of parameters passed to the controller method may change.
        $routes->add('{locale}/path/(:any)/to/(:num)', 'myController::goto/$1/$2');

        $match = $routes->reverseRoute('myController::goto', 'string', 13);

        $this->assertSame('/en/path/string/to/13', $match);
    }

    public function testReverseRoutingReturnsFalseWithBadParamCount()
    {
        $routes = $this->getCollector();

        // @TODO Do not put any placeholder after (:any).
        //       Because the number of parameters passed to the controller method may change.
        $routes->add('path/(:any)/to/(:num)', 'myController::goto/$1');

        $this->assertFalse($routes->reverseRoute('myController::goto', 'string', 13));
    }

    public function testReverseRoutingReturnsFalseWithNoMatch()
    {
        $routes = $this->getCollector();

        // @TODO Do not put any placeholder after (:any).
        //       Because the number of parameters passed to the controller method may change.
        $routes->add('path/(:any)/to/(:num)', 'myController::goto/$1/$2');

        $this->assertFalse($routes->reverseRoute('myBadController::goto', 'string', 13));
    }

    public function testReverseRoutingThrowsExceptionWithBadParamTypes()
    {
        $routes = $this->getCollector();

        // @TODO Do not put any placeholder after (:any).
        //       Because the number of parameters passed to the controller method may change.
        $routes->add('path/(:any)/to/(:num)', 'myController::goto/$1/$2');

        $this->expectException(RouterException::class);
        $routes->reverseRoute('myController::goto', 13, 'string');
    }

    public function testReverseRoutingWithLocale()
    {
        $routes = $this->getCollector();

        $routes->add('{locale}/contact', 'myController::goto');

        $this->assertSame('/en/contact', $routes->reverseRoute('myController::goto'));
    }

    public function reverseRoutingHandlerProvider(): Generator
    {
        return yield from [
            'Omit namespace'                  => ['Galleries::showUserGallery'],
            'Specify full ns starting with /' => ['\App\Controllers\Galleries::showUserGallery'],
            'Specify full ns w/o staring /'   => ['App\Controllers\Galleries::showUserGallery'],
        ];
    }

    /**
     * @dataProvider reverseRoutingHandlerProvider
     */
    public function testReverseRoutingDefaultNamespaceAppController(string $controller)
    {
        $routes = $this->getCollector();
        $routes->setDefaultNamespace('App\Controllers');

        $routes->get('users/(:num)/gallery(:any)', 'Galleries::showUserGallery/$1/$2');

        $match = $routes->reverseRoute($controller, 15, 12);

        $this->assertSame('/users/15/gallery12', $match);
    }

    public function testReverseRoutingDefaultNamespaceAppControllerSubNamespace()
    {
        $routes = $this->getCollector();
        $routes->setDefaultNamespace('App\Controllers');

        $routes->get('admin/(:num)/gallery(:any)', 'Admin\Galleries::showUserGallery/$1/$2');

        $match = $routes->reverseRoute('Admin\Galleries::showUserGallery', 15, 12);

        $this->assertSame('/admin/15/gallery12', $match);
    }

    public function testReverseRouteMatching()
    {
        $routes = $this->getCollector();

        $routes->get('test/(:segment)/(:segment)', 'TestController::test/$1/$2', ['as' => 'testRouter']);

        $match = $routes->reverseRoute('testRouter', 1, 2);

        $this->assertSame('/test/1/2', $match);
    }

    public function testReverseRouteMatchingWithLocale()
    {
        $routes = $this->getCollector();

        $routes->get('{locale}/test/(:segment)/(:segment)', 'TestController::test/$1/$2', ['as' => 'testRouter']);

        $match = $routes->reverseRoute('testRouter', 1, 2);

        $this->assertSame('/en/test/1/2', $match);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/568
     */
    public function testReverseRoutingWithClosure()
    {
        $routes = $this->getCollector();

        $routes->add('login', static function () {
        });

        $match = $routes->reverseRoute('login');

        $this->assertSame('/login', $match);
    }

    public function testReverseRoutingWithClosureNoMatch()
    {
        $routes = $this->getCollector();

        $routes->add('login', static function () {
        });

        $this->assertFalse($routes->reverseRoute('foobar'));
    }
}
