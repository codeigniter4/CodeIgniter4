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

        return (new RouteCollection($loader, $moduleConfig, new Routing()))->setHTTPVerb('get');
    }

    public function testCollect()
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
                'method'  => 'get',
                'route'   => 'journals',
                'name'    => 'journals',
                'handler' => '\App\Controllers\Blogs',
            ],
            [
                'method'  => 'get',
                'route'   => 'product/([0-9]+)',
                'name'    => 'product/([0-9]+)',
                'handler' => '\App\Controllers\Catalog::productLookupByID/$1',
            ],
            [
                'method'  => 'get',
                'route'   => 'feed',
                'name'    => 'feed',
                'handler' => '(Closure)',
            ],
            [
                'method'  => 'get',
                'route'   => 'about',
                'name'    => 'about',
                'handler' => '(View) pages/about',
            ],
        ];
        $this->assertSame($expected, $definedRoutes);
    }
}
