<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

use Closure;
use CodeIgniter\Filters\Exceptions\FilterException;
use CodeIgniter\Filters\Filters;
use CodeIgniter\Filters\FilterInterface;
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
	 * @var boolean
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

	//--------------------------------------------------------------------
	// Staging
	//--------------------------------------------------------------------

	/**
	 * Initializes dependencies once.
	 *
	 * @return void
	 */
	protected function setUpFilterTestTrait(): void
	{
		if ($this->doneFilterSetUp === true)
		{
			return;
		}

		// Create our own Request and Response so we can
		// use the same ones for Filters and FilterInterface
		// yet isolate them from outside influence
		$this->request  = $this->request ?? clone Services::request();
		$this->response = $this->response ?? clone Services::response();

		// Create our config and Filters instance to reuse for performance
		$this->filtersConfig = $this->filtersConfig ?? config('Filters');
		$this->filters       = $this->filters ?? new Filters($this->filtersConfig, $this->request, $this->response);

		if (is_null($this->collection))
		{
			// Load the RouteCollection from Config to gather App route info
			// (creates $routes using the Service as a starting point)
			require APPPATH . 'Config/Routes.php';

			$routes->getRoutes('*'); // Triggers discovery
			$this->collection = $routes;
		}

		$this->doneFilterSetUp = true;
	}

	//--------------------------------------------------------------------
	// Utility
	//--------------------------------------------------------------------

	/**
	 * Returns a callable method for a filter position
	 * using the local HTTP instances.
	 *
	 * @param FilterInterface|string $filter   The filter instance, class, or alias
	 * @param string                 $position "before" or "after"
	 *
	 * @return Closure
	 */
	protected function getFilterCaller($filter, string $position): Closure
	{
		if (! in_array($position, ['before', 'after'], true))
		{
			throw new InvalidArgumentException('Invalid filter position passed: ' . $position);
		}

		if (is_string($filter))
		{
			// Check for an alias (no namespace)
			if (strpos($filter, '\\') === false)
			{
				if (! isset($this->filtersConfig->aliases[$filter]))
				{
					throw new RuntimeException("No filter found with alias '{$filter}'");
				}

				$filter = $this->filtersConfig->aliases[$filter];
			}

			// Get an instance
			$filter = new $filter();
		}

		if (! $filter instanceof FilterInterface)
		{
			throw FilterException::forIncorrectInterface(get_class($filter));
		}

		$request = clone $this->request;

		if ($position === 'before')
		{
			return function (array $params = null) use ($filter, $request) {
				return $filter->before($request, $params);
			};
		}

		$response = clone $this->response;

		return function (array $params = null) use ($filter, $request, $response) {
			return $filter->after($request, $response, $params);
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
		if (! in_array($position, ['before', 'after'], true))
		{
			throw new InvalidArgumentException('Invalid filter position passed:' . $position);
		}

		$this->filters->reset();

		if ($routeFilter = $this->collection->getFilterForRoute($route))
		{
			$this->filters->enableFilter($routeFilter, $position);
		}

		$aliases = $this->filters->initialize($route)->getFilters();

		$this->filters->reset();
		return $aliases[$position];
	}

	//--------------------------------------------------------------------
	// Assertions
	//--------------------------------------------------------------------

	/**
	 * Asserts that the given route at position uses
	 * the filter (by its alias).
	 *
	 * @param string $route    The route to test
	 * @param string $position "before" or "after"
	 * @param string $alias    Alias for the anticipated filter
	 *
	 * @return void
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
	 *
	 * @return void
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
	 *
	 * @return void
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
	 *
	 * @return void
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
