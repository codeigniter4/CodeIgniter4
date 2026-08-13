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

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Input\Option;
use CodeIgniter\Commands\Utilities\Routes\AutoRouteCollector;
use CodeIgniter\Commands\Utilities\Routes\AutoRouterImproved\AutoRouteCollector as AutoRouteCollectorImproved;
use CodeIgniter\Commands\Utilities\Routes\FilterCollector;
use CodeIgniter\Commands\Utilities\Routes\SampleURIGenerator;
use CodeIgniter\Router\DefinedRouteCollector;
use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Router\Router;
use Config\Feature;
use Config\Routing;

/**
 * Lists all the routes. This will include any Routes files
 * that can be discovered, and will include routes that are not defined
 * in routes files, but are instead discovered through auto-routing.
 */
#[Command(name: 'routes', description: 'Displays all routes.', group: 'CodeIgniter')]
class Routes extends AbstractCommand
{
    private SampleURIGenerator $uriGenerator;
    private FilterCollector $filterCollector;

    protected function configure(): void
    {
        $this
            ->addOption(new Option(
                name: 'sort-by-handler',
                description: 'Sort by handler.',
            ))
            ->addOption(new Option(
                name: 'host',
                description: 'Specify hostname in request URI.',
                requiresValue: true,
                default: '',
            ));
    }

    protected function execute(array $arguments, array $options): int
    {
        $sortByHandler = $options['sort-by-handler'] !== false;

        $host = $options['host'];
        assert(is_string($host));

        if ($host !== '') {
            service('superglobals')->setServer('HTTP_HOST', $host);
        }

        $collection = service('routes')->loadRoutes();

        if ($host !== '') {
            service('superglobals')->unsetServer('HTTP_HOST');
        }

        $this->uriGenerator    = new SampleURIGenerator();
        $this->filterCollector = new FilterCollector();

        $tbody = $this->collectDefinedRoutes($collection);

        if ($collection->shouldAutoRoute()) {
            $tbody = [...$tbody, ...$this->collectAutoRoutes($collection)];
        }

        if ($sortByHandler) {
            usort($tbody, static fn (array $route1, array $route2): int => strcmp($route1[3], $route2[3]));
        }

        if ($host !== '') {
            CLI::write(sprintf('Host: %s', $host));
        }

        CLI::table($tbody, [
            'Method',
            'Route',
            'Name',
            $sortByHandler ? 'Handler ↓' : 'Handler',
            'Before Filters',
            'After Filters',
        ]);

        return $this->showRequiredFilters();
    }

    /**
     * @return list<list<string>>
     */
    private function collectDefinedRoutes(RouteCollection $collection): array
    {
        $tbody = [];

        foreach ((new DefinedRouteCollector($collection))->collect() as $route) {
            $filters = $this->filterCollector->get(
                $route['method'],
                $this->uriGenerator->get($route['route']),
            );

            $tbody[] = [
                strtoupper($route['method']),
                $route['route'],
                $route['route'] === $route['name'] ? '»' : $route['name'],
                $route['handler'],
                $this->basenames($filters['before']),
                $this->basenames($filters['after']),
            ];
        }

        return $tbody;
    }

    /**
     * @return list<list<string>>
     */
    private function collectAutoRoutes(RouteCollection $collection): array
    {
        if (config(Feature::class)->autoRoutesImproved) {
            return $this->collectImprovedAutoRoutes($collection);
        }

        $autoRoutes = (new AutoRouteCollector(
            $collection->getDefaultNamespace(),
            $collection->getDefaultController(),
            $collection->getDefaultMethod(),
        ))->get();

        foreach ($autoRoutes as &$route) {
            // There is no `AUTO` method, but it is intentional not to get route filters.
            $filters = $this->filterCollector->get('AUTO', $this->uriGenerator->get($route[1]));

            $route[] = $this->basenames($filters['before']);
            $route[] = $this->basenames($filters['after']);
        }

        return $autoRoutes;
    }

    /**
     * @return list<list<string>>
     */
    private function collectImprovedAutoRoutes(RouteCollection $collection): array
    {
        $autoRoutes = (new AutoRouteCollectorImproved(
            $collection->getDefaultNamespace(),
            $collection->getDefaultController(),
            $collection->getDefaultMethod(),
            Router::HTTP_METHODS,
            $collection->getRegisteredControllers('*'),
        ))->get();

        $routingConfig = config(Routing::class);

        if (! $routingConfig instanceof Routing) {
            return $autoRoutes;
        }

        foreach ($routingConfig->moduleRoutes as $uri => $namespace) {
            $moduleRoutes = (new AutoRouteCollectorImproved(
                $namespace,
                $collection->getDefaultController(),
                $collection->getDefaultMethod(),
                Router::HTTP_METHODS,
                $collection->getRegisteredControllers('*'),
                $uri,
            ))->get();

            $autoRoutes = [...$autoRoutes, ...$moduleRoutes];
        }

        return $autoRoutes;
    }

    /**
     * @param list<string> $filters
     */
    private function basenames(array $filters): string
    {
        return implode(' ', array_map(class_basename(...), $filters));
    }

    private function showRequiredFilters(): int
    {
        $required = (new FilterCollector())->getRequiredFilters();

        CLI::write(sprintf('Required Before Filters: %s', $this->highlight($required['before'])));
        CLI::write(sprintf(' Required After Filters: %s', $this->highlight($required['after'])));

        return EXIT_SUCCESS;
    }

    /**
     * @param list<string> $filters
     */
    private function highlight(array $filters): string
    {
        return implode(', ', array_map(
            static fn (string $filter): string => CLI::color($filter, 'yellow'),
            $filters,
        ));
    }
}
