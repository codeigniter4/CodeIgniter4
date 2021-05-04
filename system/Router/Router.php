<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Router;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Request;
use CodeIgniter\Router\Exceptions\RedirectException;
use CodeIgniter\Router\Exceptions\RouterException;

/**
 * Request router.
 */
class Router implements RouterInterface
{
	/**
	 * A RouteCollection instance.
	 *
	 * @var RouteCollectionInterface
	 */
	protected $collection;

	/**
	 * Sub-directory that contains the requested controller class.
	 * Primarily used by 'autoRoute'.
	 *
	 * @var string|null
	 */
	protected $directory;

	/**
	 * The name of the controller class.
	 *
	 * @var string
	 */
	protected $controller;

	/**
	 * The name of the method to use.
	 *
	 * @var string
	 */
	protected $method;

	/**
	 * An array of binds that were collected
	 * so they can be sent to closure routes.
	 *
	 * @var array
	 */
	protected $params = [];

	/**
	 * The name of the front controller.
	 *
	 * @var string
	 */
	protected $indexPage = 'index.php';

	/**
	 * Whether dashes in URI's should be converted
	 * to underscores when determining method names.
	 *
	 * @var boolean
	 */
	protected $translateURIDashes = false;

	/**
	 * The route that was matched for this request.
	 *
	 * @var array|null
	 */
	protected $matchedRoute;

	/**
	 * The options set for the matched route.
	 *
	 * @var array|null
	 */
	protected $matchedRouteOptions;

	/**
	 * The locale that was detected in a route.
	 *
	 * @var string
	 */
	protected $detectedLocale;

	/**
	 * The filter info from Route Collection
	 * if the matched route should be filtered.
	 *
	 * @var string|null
	 */
	protected $filterInfo;

	//--------------------------------------------------------------------
	/**
	 * Stores a reference to the RouteCollection object.
	 *
	 * @param RouteCollectionInterface $routes
	 * @param Request                  $request
	 */
	public function __construct(RouteCollectionInterface $routes, Request $request = null)
	{
		$this->collection = $routes;

		$this->controller = $this->collection->getDefaultController();
		$this->method     = $this->collection->getDefaultMethod();

		// @phpstan-ignore-next-line
		$this->collection->setHTTPVerb($request->getMethod() ?? strtolower($_SERVER['REQUEST_METHOD']));
	}

	//--------------------------------------------------------------------

	/**
	 * @param string|null $uri
	 *
	 * @return mixed|string
	 * @throws RedirectException
	 * @throws PageNotFoundException
	 */
	public function handle(string $uri = null)
	{
		$this->translateURIDashes = $this->collection->shouldTranslateURIDashes();

		// If we cannot find a URI to match against, then
		// everything runs off of it's default settings.
		if ($uri === null || $uri === '')
		{
			return strpos($this->controller, '\\') === false
				? $this->collection->getDefaultNamespace() . $this->controller
				: $this->controller;
		}

		// Decode URL-encoded string
		$uri = urldecode($uri);

		// Restart filterInfo
		$this->filterInfo = null;

		if ($this->checkRoutes($uri))
		{
			if ($this->collection->isFiltered($this->matchedRoute[0]))
			{
				$this->filterInfo = $this->collection->getFilterForRoute($this->matchedRoute[0]);
			}

			return $this->controller;
		}

		// Still here? Then we can try to match the URI against
		// Controllers/directories, but the application may not
		// want this, like in the case of API's.
		if (! $this->collection->shouldAutoRoute())
		{
			throw new PageNotFoundException("Can't find a route for '{$uri}'.");
		}

		$this->autoRoute($uri);

		return $this->controllerName();
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the filter info for the matched route, if any.
	 *
	 * @return string
	 */
	public function getFilter()
	{
		return $this->filterInfo;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the name of the matched controller.
	 *
	 * @return mixed
	 */
	public function controllerName()
	{
		return $this->translateURIDashes
			? str_replace('-', '_', $this->controller)
			: $this->controller;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the name of the method to run in the
	 * chosen container.
	 *
	 * @return string
	 */
	public function methodName(): string
	{
		return $this->translateURIDashes
			? str_replace('-', '_', $this->method)
			: $this->method;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the 404 Override settings from the Collection.
	 * If the override is a string, will split to controller/index array.
	 */
	public function get404Override()
	{
		$route = $this->collection->get404Override();

		if (is_string($route))
		{
			$routeArray = explode('::', $route);

			return [
				$routeArray[0], // Controller
				$routeArray[1] ?? 'index',   // Method
			];
		}

		if (is_callable($route))
		{
			return $route;
		}

		return null;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the binds that have been matched and collected
	 * during the parsing process as an array, ready to send to
	 * instance->method(...$params).
	 *
	 * @return array
	 */
	public function params(): array
	{
		return $this->params;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the name of the sub-directory the controller is in,
	 * if any. Relative to APPPATH.'Controllers'.
	 *
	 * Only used when auto-routing is turned on.
	 *
	 * @return string
	 */
	public function directory(): string
	{
		return ! empty($this->directory) ? $this->directory : '';
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the routing information that was matched for this
	 * request, if a route was defined.
	 *
	 * @return array|null
	 */
	public function getMatchedRoute()
	{
		return $this->matchedRoute;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns all options set for the matched route
	 *
	 * @return array|null
	 */
	public function getMatchedRouteOptions()
	{
		return $this->matchedRouteOptions;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the value that should be used to match the index.php file. Defaults
	 * to index.php but this allows you to modify it in case your are using
	 * something like mod_rewrite to remove the page. This allows you to set
	 * it a blank.
	 *
	 * @param string $page
	 *
	 * @return $this
	 */
	public function setIndexPage($page): self
	{
		$this->indexPage = $page;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Tells the system whether we should translate URI dashes or not
	 * in the URI from a dash to an underscore.
	 *
	 * @param boolean|false $val
	 *
	 * @return $this
	 */
	public function setTranslateURIDashes(bool $val = false): self
	{
		$this->translateURIDashes = $val;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns true/false based on whether the current route contained
	 * a {locale} placeholder.
	 *
	 * @return boolean
	 */
	public function hasLocale()
	{
		return (bool) $this->detectedLocale;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the detected locale, if any, or null.
	 *
	 * @return string
	 */
	public function getLocale()
	{
		return $this->detectedLocale;
	}

	//--------------------------------------------------------------------

	/**
	 * Compares the uri string against the routes that the
	 * RouteCollection class defined for us, attempting to find a match.
	 * This method will modify $this->controller, etal as needed.
	 *
	 * @param string $uri The URI path to compare against the routes
	 *
	 * @return boolean Whether the route was matched or not.
	 * @throws RedirectException
	 */
	protected function checkRoutes(string $uri): bool
	{
		$routes = $this->collection->getRoutes($this->collection->getHTTPVerb());

		// Don't waste any time
		if (empty($routes))
		{
			return false;
		}

		$uri = $uri === '/'
			? $uri
			: ltrim($uri, '/ ');

		// Loop through the route array looking for wildcards
		foreach ($routes as $key => $val)
		{
			// Reset localeSegment
			$localeSegment = null;

			$key = $key === '/'
				? $key
				: ltrim($key, '/ ');

			$matchedKey = $key;

			// Are we dealing with a locale?
			if (strpos($key, '{locale}') !== false)
			{
				$localeSegment = array_search('{locale}', preg_split('/[\/]*((^[a-zA-Z0-9])|\(([^()]*)\))*[\/]+/m', $key), true);

				// Replace it with a regex so it
				// will actually match.
				$key = str_replace('/', '\/', $key);
				$key = str_replace('{locale}', '[^\/]+', $key);
			}

			// Does the RegEx match?
			if (preg_match('#^' . $key . '$#u', $uri, $matches))
			{
				// Is this route supposed to redirect to another?
				if ($this->collection->isRedirect($key))
				{
					throw new RedirectException(is_array($val) ? key($val) : $val, $this->collection->getRedirectCode($key));
				}
				// Store our locale so CodeIgniter object can
				// assign it to the Request.
				if (isset($localeSegment))
				{
					// The following may be inefficient, but doesn't upset NetBeans :-/
					$temp                 = (explode('/', $uri));
					$this->detectedLocale = $temp[$localeSegment];
				}

				// Are we using Closures? If so, then we need
				// to collect the params into an array
				// so it can be passed to the controller method later.
				if (! is_string($val) && is_callable($val))
				{
					$this->controller = $val;

					// Remove the original string from the matches array
					array_shift($matches);

					$this->params = $matches;

					$this->matchedRoute = [
						$matchedKey,
						$val,
					];

					$this->matchedRouteOptions = $this->collection->getRoutesOptions($matchedKey);

					return true;
				}
				// Are we using the default method for back-references?

				// Support resource route when function with subdirectory
				// ex: $routes->resource('Admin/Admins');
				if (strpos($val, '$') !== false && strpos($key, '(') !== false && strpos($key, '/') !== false)
				{
					$replacekey = str_replace('/(.*)', '', $key);
					$val        = preg_replace('#^' . $key . '$#u', $val, $uri);
					$val        = str_replace($replacekey, str_replace('/', '\\', $replacekey), $val);
				}
				elseif (strpos($val, '$') !== false && strpos($key, '(') !== false)
				{
					$val = preg_replace('#^' . $key . '$#u', $val, $uri);
				}
				elseif (strpos($val, '/') !== false)
				{
					[
						$controller,
						$method,
					] = explode('::', $val);

					// Only replace slashes in the controller, not in the method.
					$controller = str_replace('/', '\\', $controller);

					$val = $controller . '::' . $method;
				}

				$this->setRequest(explode('/', $val));

				$this->matchedRoute = [
					$matchedKey,
					$val,
				];

				$this->matchedRouteOptions = $this->collection->getRoutesOptions($matchedKey);

				return true;
			}
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to match a URI path against Controllers and directories
	 * found in APPPATH/Controllers, to find a matching route.
	 *
	 * @param string $uri
	 */
	public function autoRoute(string $uri)
	{
		$segments = explode('/', $uri);

		$segments = $this->scanControllers($segments);

		// If we don't have any segments left - try the default controller;
		// WARNING: Directories get shifted out of the segments array.
		if (empty($segments))
		{
			$this->setDefaultController();
		}
		// If not empty, then the first segment should be the controller
		else
		{
			$this->controller = ucfirst(array_shift($segments));
		}

		$controllerName = $this->controllerName();
		if (! $this->isValidSegment($controllerName))
		{
			throw new PageNotFoundException($this->controller . ' is not a valid controller name');
		}

		// Use the method name if it exists.
		// If it doesn't, no biggie - the default method name
		// has already been set.
		if (! empty($segments))
		{
			$this->method = array_shift($segments) ?: $this->method;
		}

		if (! empty($segments))
		{
			$this->params = $segments;
		}

		$defaultNamespace = $this->collection->getDefaultNamespace();
		if ($this->collection->getHTTPVerb() !== 'cli')
		{
			$controller = '\\' . $defaultNamespace;

			$controller .= $this->directory ? str_replace('/', '\\', $this->directory) : '';
			$controller .= $controllerName;

			$controller = strtolower($controller);
			$methodName = strtolower($this->methodName());

			foreach ($this->collection->getRoutes('cli') as $route)
			{
				if (is_string($route))
				{
					$route = strtolower($route);
					if (strpos($route, $controller . '::' . $methodName) === 0)
					{
						throw new PageNotFoundException();
					}

					if ($route === $controller)
					{
						throw new PageNotFoundException();
					}
				}
			}
		}

		// Load the file so that it's available for CodeIgniter.
		$file = APPPATH . 'Controllers/' . $this->directory . $controllerName . '.php';
		if (is_file($file))
		{
			include_once $file;
		}

		// Ensure the controller stores the fully-qualified class name
		// We have to check for a length over 1, since by default it will be '\'
		if (strpos($this->controller, '\\') === false && strlen($defaultNamespace) > 1)
		{
			$this->controller = '\\' . ltrim(str_replace('/', '\\', $defaultNamespace . $this->directory . $controllerName), '\\');
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Scans the controller directory, attempting to locate a controller matching the supplied uri $segments
	 *
	 * @param array $segments URI segments
	 *
	 * @return array returns an array of remaining uri segments that don't map onto a directory
	 *
	 * @deprecated this function name does not properly describe its behavior so it has been deprecated
	 *
	 * @codeCoverageIgnore
	 */
	protected function validateRequest(array $segments): array
	{
		return $this->scanControllers($segments);
	}

	//--------------------------------------------------------------------

	/**
	 * Scans the controller directory, attempting to locate a controller matching the supplied uri $segments
	 *
	 * @param array $segments URI segments
	 *
	 * @return array returns an array of remaining uri segments that don't map onto a directory
	 */
	protected function scanControllers(array $segments): array
	{
		$segments = array_filter($segments, function ($segment) {
			return $segment !== '';
		});
		// numerically reindex the array, removing gaps
		$segments = array_values($segments);

		// if a prior directory value has been set, just return segments and get out of here
		if (isset($this->directory))
		{
			return $segments;
		}

		// Loop through our segments and return as soon as a controller
		// is found or when such a directory doesn't exist
		$c = count($segments);
		while ($c-- > 0)
		{
			$segmentConvert = ucfirst($this->translateURIDashes === true ? str_replace('-', '_', $segments[0]) : $segments[0]);
			// as soon as we encounter any segment that is not PSR-4 compliant, stop searching
			if (! $this->isValidSegment($segmentConvert))
			{
				return $segments;
			}

			$test = APPPATH . 'Controllers/' . $this->directory . $segmentConvert;

			// as long as each segment is *not* a controller file but does match a directory, add it to $this->directory
			if (! is_file($test . '.php') && is_dir($test))
			{
				$this->setDirectory($segmentConvert, true, false);
				array_shift($segments);
				continue;
			}

			return $segments;
		}

		// This means that all segments were actually directories
		return $segments;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the sub-directory that the controller is in.
	 *
	 * @param string|null $dir
	 * @param boolean     $append
	 * @param boolean     $validate if true, checks to make sure $dir consists of only PSR4 compliant segments
	 */
	public function setDirectory(string $dir = null, bool $append = false, bool $validate = true)
	{
		if (empty($dir))
		{
			$this->directory = null;
			return;
		}

		if ($validate)
		{
			$segments = explode('/', trim($dir, '/'));
			foreach ($segments as $segment)
			{
				if (! $this->isValidSegment($segment))
				{
					return;
				}
			}
		}

		if ($append !== true || empty($this->directory))
		{
			$this->directory = trim($dir, '/') . '/';
		}
		else
		{
			$this->directory .= trim($dir, '/') . '/';
		}
	}

	/**
	 * Returns true if the supplied $segment string represents a valid PSR-4 compliant namespace/directory segment
	 *
	 * regex comes from https://www.php.net/manual/en/language.variables.basics.php
	 *
	 * @param  string $segment
	 * @return boolean
	 */
	private function isValidSegment(string $segment): bool
	{
		return (bool) preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/', $segment);
	}

	//--------------------------------------------------------------------

	/**
	 * Set request route
	 *
	 * Takes an array of URI segments as input and sets the class/method
	 * to be called.
	 *
	 * @param array $segments URI segments
	 */
	protected function setRequest(array $segments = [])
	{
		// If we don't have any segments - try the default controller;
		if (empty($segments))
		{
			$this->setDefaultController();

			return;
		}

		list($controller, $method) = array_pad(explode('::', $segments[0]), 2, null);

		$this->controller = $controller;

		// $this->method already contains the default method name,
		// so don't overwrite it with emptiness.
		if (! empty($method))
		{
			$this->method = $method;
		}

		array_shift($segments);

		$this->params = $segments;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the default controller based on the info set in the RouteCollection.
	 */
	protected function setDefaultController()
	{
		if (empty($this->controller))
		{
			throw RouterException::forMissingDefaultRoute();
		}

		// Is the method being specified?
		if (sscanf($this->controller, '%[^/]/%s', $class, $this->method) !== 2)
		{
			$this->method = 'index';
		}

		if (! is_file(APPPATH . 'Controllers/' . $this->directory . ucfirst($class) . '.php'))
		{
			return;
		}

		$this->controller = ucfirst($class);

		log_message('info', 'Used the default controller.');
	}

	//--------------------------------------------------------------------
}
