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

namespace CodeIgniter\Commands\Utilities\Routes\AutoRouterImproved;

use CodeIgniter\Commands\Utilities\Routes\ControllerFinder;
use CodeIgniter\Commands\Utilities\Routes\FilterCollector;

/**
 * Collects data for Auto Routing Improved.
 *
 * @see \CodeIgniter\Commands\Utilities\Routes\AutoRouterImproved\AutoRouteCollectorTest
 */
final class AutoRouteCollector
{
    /**
     * @param string             $namespace            namespace to search
     * @param list<class-string> $protectedControllers List of controllers in Defined
     *                                                 Routes that should not be accessed via Auto-Routing.
     * @param string             $prefix               URI prefix for Module Routing
     */
    public function __construct(
        private readonly string $namespace,
        private readonly string $defaultController,
        private readonly string $defaultMethod,
        private readonly array $httpMethods,
        private readonly array $protectedControllers,
        private string $prefix = ''
    ) {
    }

    /**
     * @return list<list<string>>
     */
    public function get(): array
    {
        $finder = new ControllerFinder($this->namespace);
        $reader = new ControllerMethodReader($this->namespace, $this->httpMethods);

        $tbody = [];

        foreach ($finder->find() as $class) {
            // Exclude controllers in Defined Routes.
            if (in_array('\\' . $class, $this->protectedControllers, true)) {
                continue;
            }

            $routes = $reader->read(
                $class,
                $this->defaultController,
                $this->defaultMethod
            );

            if ($routes === []) {
                continue;
            }

            $routes = $this->addFilters($routes);

            foreach ($routes as $item) {
                $route = $item['route'] . $item['route_params'];

                // For module routing
                if ($this->prefix !== '' && $route === '/') {
                    $route = $this->prefix;
                } elseif ($this->prefix !== '') {
                    $route = $this->prefix . '/' . $route;
                }

                $tbody[] = [
                    strtoupper($item['method']) . '(auto)',
                    $route,
                    '',
                    $item['handler'],
                    $item['before'],
                    $item['after'],
                ];
            }
        }

        return $tbody;
    }

    private function addFilters($routes)
    {
        $filterCollector = new FilterCollector(true);

        foreach ($routes as &$route) {
            $routePath = $route['route'];

            // For module routing
            if ($this->prefix !== '' && $route === '/') {
                $routePath = $this->prefix;
            } elseif ($this->prefix !== '') {
                $routePath = $this->prefix . '/' . $routePath;
            }

            // Search filters for the URI with all params
            $sampleUri      = $this->generateSampleUri($route);
            $filtersLongest = $filterCollector->get($route['method'], $routePath . $sampleUri);

            // Search filters for the URI without optional params
            $sampleUri       = $this->generateSampleUri($route, false);
            $filtersShortest = $filterCollector->get($route['method'], $routePath . $sampleUri);

            // Get common array elements
            $filters['before'] = array_intersect($filtersLongest['before'], $filtersShortest['before']);
            $filters['after']  = array_intersect($filtersLongest['after'], $filtersShortest['after']);

            $route['before'] = implode(' ', array_map('class_basename', $filters['before']));
            $route['after']  = implode(' ', array_map('class_basename', $filters['after']));
        }

        return $routes;
    }

    private function generateSampleUri(array $route, bool $longest = true): string
    {
        $sampleUri = '';

        if (isset($route['params'])) {
            $i = 1;

            foreach ($route['params'] as $required) {
                if ($longest && ! $required) {
                    $sampleUri .= '/' . $i++;
                }
            }
        }

        return $sampleUri;
    }
}
