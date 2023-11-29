<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Commands\Utilities\Routes;

use CodeIgniter\Config\Services;
use CodeIgniter\Filters\Filters;
use CodeIgniter\HTTP\Request;
use CodeIgniter\Router\Router;
use Config\Filters as FiltersConfig;

/**
 * Collects filters for a route.
 *
 * @see \CodeIgniter\Commands\Utilities\Routes\FilterCollectorTest
 */
final class FilterCollector
{
    /**
     * Whether to reset Defined Routes.
     *
     * If set to true, route filters are not found.
     */
    private readonly bool $resetRoutes;

    public function __construct(bool $resetRoutes = false)
    {
        $this->resetRoutes = $resetRoutes;
    }

    /**
     * @param string $method HTTP verb like `GET`,`POST` or `CLI`.
     * @param string $uri    URI path to find filters for
     *
     * @return array{before: list<string>, after: list<string>} array of filter alias or classname
     */
    public function get(string $method, string $uri): array
    {
        if ($method === strtolower($method)) {
            @trigger_error(
                'Passing lowercase HTTP method "' . $method . '" is deprecated.'
                . ' Use uppercase HTTP method like "' . strtoupper($method) . '".',
                E_USER_DEPRECATED
            );
        }

        /**
         * @deprecated 4.5.0
         * @TODO Remove this in the future.
         */
        $method = strtoupper($method);

        if ($method === 'CLI') {
            return [
                'before' => [],
                'after'  => [],
            ];
        }

        $request = Services::incomingrequest(null, false);
        $request->setMethod($method);

        $router  = $this->createRouter($request);
        $filters = $this->createFilters($request);

        $finder = new FilterFinder($router, $filters);

        return $finder->find($uri);
    }

    private function createRouter(Request $request): Router
    {
        $routes = Services::routes();

        if ($this->resetRoutes) {
            $routes->resetRoutes();
        }

        return new Router($routes, $request);
    }

    private function createFilters(Request $request): Filters
    {
        $config = config(FiltersConfig::class);

        return new Filters($config, $request, Services::response());
    }
}
