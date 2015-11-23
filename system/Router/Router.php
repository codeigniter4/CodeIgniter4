<?php namespace CodeIgniter\Router;

class Router implements RouterInterface
{
	/**
	 * A RouteCollection instance.
	 *
	 * @var RouteCollectionInterface
	 */
	protected $collection;

	protected $controller;

	protected $method;

	protected $params = [];

	protected $indexPage = 'index.php';

	protected $translateURIDashes = false;

	//--------------------------------------------------------------------

	/**
	 * Stores a reference to the RouteCollection object.
	 *
	 * @param RouteCollectionInterface $routes
	 */
	public function __construct(RouteCollectionInterface $routes)
	{
		$this->collection = $routes;

		$this->controller = $this->collection->defaultController();
		$this->method     = $this->collection->defaultMethod();
	}

	//--------------------------------------------------------------------

	/**
	 * Scans the URI and attempts to match the current URI to the
	 * one of the defined routes in the RouteCollection.
	 *
	 * @param null $uri
	 *
	 * @return mixed
	 */
	public function handle(string $uri = null)
	{
		// If we cannot find a URI to match against, then
		// everything runs off of it's default settings.
		if (empty($uri))
		{
			return;
		}

		if ($this->checkRoutes($uri))
		{
			return;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the name of the matched controller.
	 *
	 * @return mixed
	 */
	public function controllerName()
	{
		return $this->controller;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the name of the method to run in the
	 * chosen container.
	 *
	 * @return mixed
	 */
	public function methodName(): string
	{
		return $this->method;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the parameters that have been matched and collected
	 * during the parsing process as an array, ready to send to
	 * call_user_func_array().
	 *
	 * @return mixed
	 */
	public function params(): array
	{
		return $this->params;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the value that should be used to match the index.php file. Defaults
	 * to index.php but this allows you to modify it in case your are using
	 * something like mod_rewrite to remove the page. This allows you to set
	 * it a blank.
	 *
	 * @param $page
	 *
	 * @return mixed
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
	 * @param bool|false $val
	 *
	 * @return $this
	 */
	public function setTranslateURIDashes($val = false): self
	{
		$this->translateURIDashes = (bool)$val;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Compares the uri string against the routes that the
	 * RouteCollection class defined for us, attempting to find a match.
	 * This method will modify $this->controller, etal as needed.
	 *
	 * @param string $uri The URI path to compare against the routes
	 *
	 * @return bool Whether the route was matched or not.
	 */
	protected function checkRoutes(string $uri)
	{
		$routes = $this->collection->routes();

		// Don't waste any time
		if (empty($routes))
		{
			return false;
		}

		// Loop through the route array looking for wildcards
		foreach ($routes as $key => $val)
		{
			// Does the RegEx match?
			if (preg_match('#^'.$key.'$#', $uri, $matches))
			{
				// Are we using Closures? If so, then we need
				// to collect the params into an array
				// so it can be passed to the controller method later.
				if ( ! is_string($val) && is_callable($val))
				{
					$this->controller = $val;

					// Remove the original string from the matches array
					array_shift($matches);

					$this->params = $matches;

					return true;
				}
				// Are we using the default method for back-references?
				elseif (strpos($val, '$') !== false && strpos($key, '(') !== false)
				{
					$val = preg_replace('#^'.$key.'$#', $val, $uri);
				}

				$this->setRequest(explode('/', $val));

				return true;
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Set request route
	 *
	 * Takes an array of URI segments as input and sets the class/method
	 * to be called.
	 *
	 * @param    array $segments URI segments
	 */
	protected function setRequest(array $segments = [])
	{
		// If we don't have any segments - try the default controller;
		if (empty($segments))
		{
			$this->setDefaultController();

			return;
		}

		if ($this->translateURIDashes === true)
		{
			$segments[0] = str_replace('-', '_', $segments[0]);
			if (isset($segments[1]))
			{
				$segments[1] = str_replace('-', '_', $segments[1]);
			}
		}

		list($controller, $method) = explode('::', $segments[0]);

		$this->controller = $controller;

		// $this->method already contains the default method name,
		// so don't overwrite it with emptiness.
		if ( ! empty($method))
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
			throw new \RuntimeException('Unable to determine what should be displayed. A default route has not been specified in the routing file.');
		}

		// Is the method being specified?
		if (sscanf($this->controller, '%[^/]/%s') !== 2)
		{
			$this->method = 'index';
		}
		// @todo log that NO URI present and using default controller.
	}

	//--------------------------------------------------------------------
}