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
use CodeIgniter\HTTP\Exceptions\RedirectException;
use CodeIgniter\Router\Router;
use Config\App;
use Config\Feature;
use Config\Services;

/**
 * Finds filters.
 *
 * @see \CodeIgniter\Commands\Utilities\Routes\FilterFinderTest
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

        $isLocaleExist = strpos($uri, '{locale}') !== false;

        if ($isLocaleExist) {
            $uri = str_replace('{locale}', config(App::class)->defaultLocale, $uri);
        }

        // Add route filters
        try {
            $routeFilters = $this->getRouteFilters($uri);

            $this->filters->enableFilters($routeFilters, 'before');

            if (! config(Feature::class)->oldFilterOrder) {
                $routeFilters = array_reverse($routeFilters);
            }

            $this->filters->enableFilters($routeFilters, 'after');

            $this->filters->initialize($uri);

            $filters = $this->filters->getFilters();

            if ($isLocaleExist) {
                $filters['before'] = array_map(static fn ($filter) => '!' . $filter, $filters['before']);
                $filters['after']  = array_map(static fn ($filter) => '!' . $filter, $filters['after']);
            }

            return $filters;
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
