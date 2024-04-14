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

use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Modules;
use Config\Routing;

/**
 * @internal
 *
 * @group Others
 */
final class DefinedRouteCollectorTest extends CIUnitTestCase
{
    private function createRouteCollection(array $config = [], $moduleConfig = null): RouteCollection
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

        return (new RouteCollection($loader, $moduleConfig, new Routing()))->setHTTPVerb(Method::GET);
    }

    public function testCollect(): void
    {
        $routes = $this->createRouteCollection();
        $routes->get('journals', 'Blogs');
        $routes->get('product/(:num)', 'Catalog::productLookupByID/$1');
        $routes->get('feed', static fn () => 'A Closure route.');
        $routes->view('about', 'pages/about');

        $collector = new DefinedRouteCollector($routes);

        $definedRoutes = [];

        foreach ($collector->collect() as $route) {
            $definedRoutes[] = $route;
        }

        $expected = [
            [
                'method'  => 'GET',
                'route'   => 'journals',
                'name'    => 'journals',
                'handler' => '\App\Controllers\Blogs',
            ],
            [
                'method'  => 'GET',
                'route'   => 'product/([0-9]+)',
                'name'    => 'product/([0-9]+)',
                'handler' => '\App\Controllers\Catalog::productLookupByID/$1',
            ],
            [
                'method'  => 'GET',
                'route'   => 'feed',
                'name'    => 'feed',
                'handler' => '(Closure)',
            ],
            [
                'method'  => 'GET',
                'route'   => 'about',
                'name'    => 'about',
                'handler' => '(View) pages/about',
            ],
        ];
        $this->assertSame($expected, $definedRoutes);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/8039
     */
    public function testCollectSameFromWithDifferentVerb(): void
    {
        $routes = $this->createRouteCollection();
        $routes->get('login', 'AuthController::showLogin', ['as' => 'loginShow']);
        $routes->post('login', 'AuthController::login', ['as' => 'login']);
        $routes->get('logout', 'AuthController::logout', ['as' => 'logout']);

        $collector = new DefinedRouteCollector($routes);

        $definedRoutes = [];

        foreach ($collector->collect() as $route) {
            $definedRoutes[] = $route;
        }

        $expected = [
            [
                'method'  => 'GET',
                'route'   => 'login',
                'name'    => 'loginShow',
                'handler' => '\\App\\Controllers\\AuthController::showLogin',
            ],
            [
                'method'  => 'GET',
                'route'   => 'logout',
                'name'    => 'logout',
                'handler' => '\\App\\Controllers\\AuthController::logout',
            ],
            [
                'method'  => 'POST',
                'route'   => 'login',
                'name'    => 'login',
                'handler' => '\\App\\Controllers\\AuthController::login',
            ],
        ];
        $this->assertSame($expected, $definedRoutes);
    }
}
