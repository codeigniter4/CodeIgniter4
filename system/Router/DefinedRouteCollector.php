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

use Closure;
use Generator;

/**
 * Collect all defined routes for display.
 *
 * @see \CodeIgniter\Router\DefinedRouteCollectorTest
 */
final class DefinedRouteCollector
{
    private RouteCollection $routeCollection;

    public function __construct(RouteCollection $routes)
    {
        $this->routeCollection = $routes;
    }

    /**
     * @return Generator<array{method: string, route: string, name: string, handler: string}>
     */
    public function collect(): Generator
    {
        $methods = [
            'get',
            'head',
            'post',
            'patch',
            'put',
            'delete',
            'options',
            'trace',
            'connect',
            'cli',
        ];

        foreach ($methods as $method) {
            $routes = $this->routeCollection->getRoutes($method);

            foreach ($routes as $route => $handler) {
                if (is_string($handler) || $handler instanceof Closure) {
                    if ($handler instanceof Closure) {
                        $view = $this->routeCollection->getRoutesOptions($route, $method)['view'] ?? false;

                        $handler = $view ? '(View) ' . $view : '(Closure)';
                    }

                    $routeName = $this->routeCollection->getRoutesOptions($route, $method)['as'] ?? $route;

                    yield [
                        'method'  => $method,
                        'route'   => $route,
                        'name'    => $routeName,
                        'handler' => $handler,
                    ];
                }
            }
        }
    }
}
