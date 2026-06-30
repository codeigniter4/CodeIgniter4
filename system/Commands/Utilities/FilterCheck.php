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
use CodeIgniter\CLI\Input\Argument;
use CodeIgniter\Commands\Utilities\Routes\FilterCollector;

/**
 * Check filters for a route.
 */
#[Command(name: 'filter:check', description: 'Check filters for a route.', group: 'CodeIgniter')]
class FilterCheck extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument(new Argument(
                name: 'method',
                description: 'The HTTP method. GET, POST, PUT, etc.',
                required: true,
            ))
            ->addArgument(new Argument(
                name: 'route',
                description: 'The route (URI path) to check filters.',
                required: true,
            ));
    }

    protected function execute(array $arguments, array $options): int
    {
        $method = $arguments['method'];
        assert(is_string($method));

        $route = $arguments['route'];
        assert(is_string($route));

        service('routes')->loadRoutes();

        $filterCollector = new FilterCollector();

        $filters = $filterCollector->get($method, $route);

        // PageNotFoundException
        if ($filters['before'] === ['<unknown>']) {
            CLI::error(sprintf(
                "Can't find a route: %s",
                CLI::color(sprintf('"%s %s"', strtoupper($method), $route), 'black', 'light_gray'),
            ));

            return EXIT_ERROR;
        }

        $this->showTable($filterCollector, $filters, $method, $route);
        $this->showFilterClasses($filterCollector, $method, $route);

        return EXIT_SUCCESS;
    }

    /**
     * @param array{before: list<string>, after: list<string>} $filters
     */
    private function showTable(
        FilterCollector $filterCollector,
        array $filters,
        string $method,
        string $route,
    ): void {
        $merged = $this->mergeFilters($filterCollector->getRequiredFilters(), $filters);

        $thead = ['Method', 'Route', 'Before Filters', 'After Filters'];
        $tbody = [
            [
                strtoupper($method),
                $route,
                implode(' ', $merged['before']),
                implode(' ', $merged['after']),
            ],
        ];

        CLI::table($tbody, $thead);
    }

    private function showFilterClasses(
        FilterCollector $filterCollector,
        string $method,
        string $route,
    ): void {
        $merged = $this->mergeFilters(
            $filterCollector->getRequiredFilterClasses(),
            $filterCollector->getClasses($method, $route),
        );

        $lastPosition = array_key_last($merged);

        foreach ($merged as $position => $classes) {
            CLI::write(sprintf('%s Filter Classes:', ucfirst($position)), 'cyan');
            CLI::write(implode(' → ', $classes));

            if ($position !== $lastPosition) {
                CLI::newLine();
            }
        }
    }

    /**
     * Merges the required filters (highlighted) with the route's filters,
     * keeping required-before filters first and required-after filters last.
     *
     * @param array{before: list<string>, after: list<string>} $required
     * @param array{before: list<string>, after: list<string>} $filters
     *
     * @return array{before: list<string>, after: list<string>}
     */
    private function mergeFilters(array $required, array $filters): array
    {
        return [
            'before' => array_merge($this->highlight($required['before']), $filters['before']),
            'after'  => array_merge($filters['after'], $this->highlight($required['after'])),
        ];
    }

    /**
     * Applies the highlight color to the given filter names.
     *
     * @param list<string> $items
     *
     * @return list<string>
     */
    private function highlight(array $items): array
    {
        return array_map(static fn (string $item): string => CLI::color($item, 'yellow'), $items);
    }
}
