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

use Closure;
use Generator;

/**
 * Collect all defined routes for display.
 *
 * @see \CodeIgniter\Router\DefinedRouteCollectorTest
 */
final class DefinedRouteCollector
{
    public function __construct(private readonly RouteCollectionInterface $routeCollection)
    {
    }

    /**
     * @return Generator<array{method: string, route: string, name: string, handler: string}>
     */
    public function collect(): Generator
    {
        $methods = Router::HTTP_METHODS;

        foreach ($methods as $method) {
            $routes = $this->routeCollection->getRoutes($method);

            foreach ($routes as $route => $handler) {
                // The route key should be a string, but it is stored as an array key,
                // it might be an integer.
                $route = (string) $route;

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
