<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

use Closure;
use CodeIgniter\Filters\Exceptions\FilterException;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\Filters\Filters;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Router\RouteCollection;
use Config\Filters as FiltersConfig;
use Config\Services;
use InvalidArgumentException;
use RuntimeException;

/**
 * Filter Test Trait
 *
 * Provides functionality for testing
 * filters and their route associations.
 *
 * @mixin CIUnitTestCase
 */
trait FilterTestTrait
{
    /**
     * Have the one-time classes been instantiated?
     *
     * @var bool
     */
    private $doneFilterSetUp = false;

    /**
     * The active IncomingRequest or CLIRequest
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * The active Response instance
     *
     * @var ResponseInterface
     */
    protected $response;

    /**
     * The Filters configuration to use.
     * Extracted for access to aliases
     * during Filters::discoverFilters().
     *
     * @var FiltersConfig|null
     */
    protected $filtersConfig;

    /**
     * The prepared Filters library.
     *
     * @var Filters|null
     */
    protected $filters;

    /**
     * The default App and discovered
     * routes to check for filters.
     *
     * @var RouteCollection|null
     */
    protected $collection;

    // --------------------------------------------------------------------
    // Staging
    // --------------------------------------------------------------------

    /**
     * Initializes dependencies once.
     */
    protected function setUpFilterTestTrait(): void
    {
        if ($this->doneFilterSetUp === true) {
            return;
        }

        // Create our own Request and Response so we can
        // use the same ones for Filters and FilterInterface
        // yet isolate them from outside influence
        $this->request ??= clone Services::request();
        $this->response ??= clone Services::response();

        // Create our config and Filters instance to reuse for performance
        $this->filtersConfig ??= config(FiltersConfig::class);
        $this->filters ??= new Filters($this->filtersConfig, $this->request, $this->response);

        if ($this->collection === null) {
            $this->collection = Services::routes()->loadRoutes();
        }

        $this->doneFilterSetUp = true;
    }

    // --------------------------------------------------------------------
    // Utility
    // --------------------------------------------------------------------

    /**
     * Returns a callable method for a filter position
     * using the local HTTP instances.
     *
     * @param FilterInterface|string $filter   The filter instance, class, or alias
     * @param string                 $position "before" or "after"
     */
    protected function getFilterCaller($filter, string $position): Closure
    {
        if (! in_array($position, ['before', 'after'], true)) {
            throw new InvalidArgumentException('Invalid filter position passed: ' . $position);
        }

        if (is_string($filter)) {
            // Check for an alias (no namespace)
            if (strpos($filter, '\\') === false) {
                if (! isset($this->filtersConfig->aliases[$filter])) {
                    throw new RuntimeException("No filter found with alias '{$filter}'");
                }

                $filterClasses = $this->filtersConfig->aliases[$filter];
            }

            $filterClasses = (array) $filterClasses;
        }

        foreach ($filterClasses as $class) {
            // Get an instance
            $filter = new $class();

            if (! $filter instanceof FilterInterface) {
                throw FilterException::forIncorrectInterface(get_class($filter));
            }
        }

        $request = clone $this->request;

        if ($position === 'before') {
            return static function (?array $params = null) use ($filterClasses, $request) {
                foreach ($filterClasses as $class) {
                    $filter = new $class();

                    $result = $filter->before($request, $params);

                    // @TODO The following logic is in Filters class.
                    //       Should use Filters class.
                    if ($result instanceof RequestInterface) {
                        $request = $result;

                        continue;
                    }
                    if ($result instanceof ResponseInterface) {
                        return $result;
                    }
                    if (empty($result)) {
                        continue;
                    }
                }

                return $result;
            };
        }

        $response = clone $this->response;

        return static function (?array $params = null) use ($filterClasses, $request, $response) {
            foreach ($filterClasses as $class) {
                $filter = new $class();

                $result = $filter->after($request, $response, $params);

                // @TODO The following logic is in Filters class.
                //       Should use Filters class.
                if ($result instanceof ResponseInterface) {
                    $response = $result;

                    continue;
                }
            }

            return $result;
        };
    }

    /**
     * Gets an array of filter aliases enabled
     * for the given route at position.
     *
     * @param string $route    The route to test
     * @param string $position "before" or "after"
     *
     * @return string[] The filter aliases
     */
    protected function getFiltersForRoute(string $route, string $position): array
    {
        if (! in_array($position, ['before', 'after'], true)) {
            throw new InvalidArgumentException('Invalid filter position passed:' . $position);
        }

        $this->filters->reset();

        if ($routeFilters = $this->collection->getFiltersForRoute($route)) {
            $this->filters->enableFilters($routeFilters, $position);
        }

        $aliases = $this->filters->initialize($route)->getFilters();

        $this->filters->reset();

        return $aliases[$position];
    }

    // --------------------------------------------------------------------
    // Assertions
    // --------------------------------------------------------------------

    /**
     * Asserts that the given route at position uses
     * the filter (by its alias).
     *
     * @param string $route    The route to test
     * @param string $position "before" or "after"
     * @param string $alias    Alias for the anticipated filter
     */
    protected function assertFilter(string $route, string $position, string $alias): void
    {
        $filters = $this->getFiltersForRoute($route, $position);

        $this->assertContains(
            $alias,
            $filters,
            "Filter '{$alias}' does not apply to '{$route}'.",
        );
    }

    /**
     * Asserts that the given route at position does not
     * use the filter (by its alias).
     *
     * @param string $route    The route to test
     * @param string $position "before" or "after"
     * @param string $alias    Alias for the anticipated filter
     */
    protected function assertNotFilter(string $route, string $position, string $alias)
    {
        $filters = $this->getFiltersForRoute($route, $position);

        $this->assertNotContains(
            $alias,
            $filters,
            "Filter '{$alias}' applies to '{$route}' when it should not.",
        );
    }

    /**
     * Asserts that the given route at position has
     * at least one filter set.
     *
     * @param string $route    The route to test
     * @param string $position "before" or "after"
     */
    protected function assertHasFilters(string $route, string $position)
    {
        $filters = $this->getFiltersForRoute($route, $position);

        $this->assertNotEmpty(
            $filters,
            "No filters found for '{$route}' when at least one was expected.",
        );
    }

    /**
     * Asserts that the given route at position has
     * no filters set.
     *
     * @param string $route    The route to test
     * @param string $position "before" or "after"
     */
    protected function assertNotHasFilters(string $route, string $position)
    {
        $filters = $this->getFiltersForRoute($route, $position);

        $this->assertSame(
            [],
            $filters,
            "Found filters for '{$route}' when none were expected: " . implode(', ', $filters) . '.',
        );
    }
}
