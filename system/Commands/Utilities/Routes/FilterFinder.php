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

namespace CodeIgniter\Commands\Utilities\Routes;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\Filters;
use CodeIgniter\HTTP\Exceptions\BadRequestException;
use CodeIgniter\HTTP\Exceptions\RedirectException;
use CodeIgniter\Router\Router;
use Config\Feature;

/**
 * Finds filters.
 *
 * @see \CodeIgniter\Commands\Utilities\Routes\FilterFinderTest
 */
final class FilterFinder
{
    private readonly Router $router;
    private readonly Filters $filters;

    public function __construct(?Router $router = null, ?Filters $filters = null)
    {
        $this->router  = $router ?? service('router');
        $this->filters = $filters ?? service('filters');
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

        // Add route filters
        try {
            $routeFilters = $this->getRouteFilters($uri);

            $this->filters->enableFilters($routeFilters, 'before');

            $oldFilterOrder = config(Feature::class)->oldFilterOrder ?? false;
            if (! $oldFilterOrder) {
                $routeFilters = array_reverse($routeFilters);
            }

            $this->filters->enableFilters($routeFilters, 'after');

            $this->filters->initialize($uri);

            return $this->filters->getFilters();
        } catch (RedirectException) {
            return [
                'before' => [],
                'after'  => [],
            ];
        } catch (BadRequestException|PageNotFoundException) {
            return [
                'before' => ['<unknown>'],
                'after'  => ['<unknown>'],
            ];
        }
    }

    /**
     * Returns Required Filters
     *
     * @return array{before: list<string>, after:list<string>}
     */
    public function getRequiredFilters(): array
    {
        [$requiredBefore] = $this->filters->getRequiredFilters('before');
        [$requiredAfter]  = $this->filters->getRequiredFilters('after');

        return [
            'before' => $requiredBefore,
            'after'  => $requiredAfter,
        ];
    }
}
