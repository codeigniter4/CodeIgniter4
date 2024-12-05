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

namespace CodeIgniter\Commands\Utilities;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Commands\Utilities\Routes\AutoRouteCollector;
use CodeIgniter\Commands\Utilities\Routes\AutoRouterImproved\AutoRouteCollector as AutoRouteCollectorImproved;
use CodeIgniter\Commands\Utilities\Routes\FilterCollector;
use CodeIgniter\Commands\Utilities\Routes\SampleURIGenerator;
use CodeIgniter\Router\DefinedRouteCollector;
use CodeIgniter\Router\Router;
use Config\Feature;
use Config\Routing;

/**
 * Lists all the routes. This will include any Routes files
 * that can be discovered, and will include routes that are not defined
 * in routes files, but are instead discovered through auto-routing.
 */
class Routes extends BaseCommand
{
    /**
     * The group the command is lumped under
     * when listing commands.
     *
     * @var string
     */
    protected $group = 'CodeIgniter';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'routes';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Displays all routes.';

    /**
     * the Command's usage
     *
     * @var string
     */
    protected $usage = 'routes';

    /**
     * the Command's Arguments
     *
     * @var array<string, string>
     */
    protected $arguments = [];

    /**
     * the Command's Options
     *
     * @var array<string, string>
     */
    protected $options = [
        '-h'     => 'Sort by Handler.',
        '--host' => 'Specify hostname in request URI.',
    ];

    /**
     * Displays the help for the spark cli script itself.
     */
    public function run(array $params)
    {
        $sortByHandler = array_key_exists('h', $params);
        $host          = $params['host'] ?? null;

        // Set HTTP_HOST
        if ($host !== null) {
            $request              = service('request');
            $_SERVER              = $request->getServer();
            $_SERVER['HTTP_HOST'] = $host;
            $request->setGlobal('server', $_SERVER);
        }

        $collection = service('routes')->loadRoutes();

        // Reset HTTP_HOST
        if ($host !== null) {
            unset($_SERVER['HTTP_HOST']);
        }

        $methods = Router::HTTP_METHODS;

        $tbody           = [];
        $uriGenerator    = new SampleURIGenerator();
        $filterCollector = new FilterCollector();

        $definedRouteCollector = new DefinedRouteCollector($collection);

        foreach ($definedRouteCollector->collect() as $route) {
            $sampleUri = $uriGenerator->get($route['route']);
            $filters   = $filterCollector->get($route['method'], $sampleUri);

            $routeName = ($route['route'] === $route['name']) ? '»' : $route['name'];

            $tbody[] = [
                strtoupper($route['method']),
                $route['route'],
                $routeName,
                $route['handler'],
                implode(' ', array_map(class_basename(...), $filters['before'])),
                implode(' ', array_map(class_basename(...), $filters['after'])),
            ];
        }

        if ($collection->shouldAutoRoute()) {
            $autoRoutesImproved = config(Feature::class)->autoRoutesImproved ?? false;

            if ($autoRoutesImproved) {
                $autoRouteCollector = new AutoRouteCollectorImproved(
                    $collection->getDefaultNamespace(),
                    $collection->getDefaultController(),
                    $collection->getDefaultMethod(),
                    $methods,
                    $collection->getRegisteredControllers('*')
                );

                $autoRoutes = $autoRouteCollector->get();

                // Check for Module Routes.
                $routingConfig = config(Routing::class);

                if ($routingConfig instanceof Routing) {
                    foreach ($routingConfig->moduleRoutes as $uri => $namespace) {
                        $autoRouteCollector = new AutoRouteCollectorImproved(
                            $namespace,
                            $collection->getDefaultController(),
                            $collection->getDefaultMethod(),
                            $methods,
                            $collection->getRegisteredControllers('*'),
                            $uri
                        );

                        $autoRoutes = [...$autoRoutes, ...$autoRouteCollector->get()];
                    }
                }
            } else {
                $autoRouteCollector = new AutoRouteCollector(
                    $collection->getDefaultNamespace(),
                    $collection->getDefaultController(),
                    $collection->getDefaultMethod()
                );

                $autoRoutes = $autoRouteCollector->get();

                foreach ($autoRoutes as &$routes) {
                    // There is no `AUTO` method, but it is intentional not to get route filters.
                    $filters = $filterCollector->get('AUTO', $uriGenerator->get($routes[1]));

                    $routes[] = implode(' ', array_map(class_basename(...), $filters['before']));
                    $routes[] = implode(' ', array_map(class_basename(...), $filters['after']));
                }
            }

            $tbody = [...$tbody, ...$autoRoutes];
        }

        $thead = [
            'Method',
            'Route',
            'Name',
            $sortByHandler ? 'Handler ↓' : 'Handler',
            'Before Filters',
            'After Filters',
        ];

        // Sort by Handler.
        if ($sortByHandler) {
            usort($tbody, static fn ($handler1, $handler2) => strcmp($handler1[3], $handler2[3]));
        }

        if ($host !== null) {
            CLI::write('Host: ' . $host);
        }

        CLI::table($tbody, $thead);

        $this->showRequiredFilters();
    }

    private function showRequiredFilters(): void
    {
        $filterCollector = new FilterCollector();

        $required = $filterCollector->getRequiredFilters();

        $filters = [];

        foreach ($required['before'] as $filter) {
            $filters[] = CLI::color($filter, 'yellow');
        }

        CLI::write('Required Before Filters: ' . implode(', ', $filters));

        $filters = [];

        foreach ($required['after'] as $filter) {
            $filters[] = CLI::color($filter, 'yellow');
        }

        CLI::write(' Required After Filters: ' . implode(', ', $filters));
    }
}
