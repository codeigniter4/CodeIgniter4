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

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\Filters;
use CodeIgniter\Router\Exceptions\RedirectException;
use CodeIgniter\Router\Router;
use Config\Services;

/**
 * Finds filters.
 */
final class FilterFinder
{
    private Router $router;
    private Filters $filters;

    public function __construct(?Router $router = null, ?Filters $filters = null)
    {
        $this->router  = $router ?? Services::router();
        $this->filters = $filters ?? Services::filters();
    }

    private function getRouteFilters(string $uri): array
    {
        $this->router->handle($uri);

        $multipleFiltersEnabled = config('Feature')->multipleFilters ?? false;
        if (! $multipleFiltersEnabled) {
            $filter = $this->router->getFilter();

            return $filter === null ? [] : [$filter];
        }

        return $this->router->getFilters();
    }

    /**
     * @param string $uri URI path to find filters for
     *
     * @return array{before: list<string>, after: list<string>} array of filter alias or classname
     */
    public function find(string $uri): array
    {
        $this->filters->reset();

        // Add route filters
        try {
            $routeFilters = $this->getRouteFilters($uri);
            $this->filters->enableFilters($routeFilters, 'before');
            $this->filters->enableFilters($routeFilters, 'after');

            $this->filters->initialize($uri);

            return $this->filters->getFilters();
        } catch (RedirectException $e) {
            return [
                'before' => [],
                'after'  => [],
            ];
        } catch (PageNotFoundException $e) {
            return [
                'before' => ['<unknown>'],
                'after'  => ['<unknown>'],
            ];
        }
    }
}
