<?php namespace CodeIgniter\Router;

/**
 * Interface RouteCollectionInterface
 *
 * A Route Collection's sole job is to hold a series of routes. The required
 * number of methods is kept very small on purpose, but implementors may
 * add a number of additional methods to customize how the routes are defined.
 *
 * The RouteCollection provides the Router with the routes so that it can determine
 * which controller should be ran.
 *
 * @package CodeIgniter\Router
 */
class RouteCollection implements RouteCollectionInterface
{

	/**
	 * The namespace to be added to any controllers.
	 * Defaults to the global namespaces (\)
	 *
	 * @var string
	 */
	protected $defaultNamespace = '\\';

	/**
	 * The name of the default controller to use
	 * when no other controller is specified.
	 *
	 * Not used here. Pass-thru value for Router class.
	 *
	 * @var string
	 */
	protected $defaultController = 'Home';

	/**
	 * The name of the default method to use
	 * when no other method has been specified.
	 *
	 * Not used here. Pass-thru value for Router class.
	 *
	 * @var string
	 */
	protected $defaultMethod = 'index';

	/**
	 * Whether to convert dashes to underscores in URI.
	 *
	 * Not used here. Pass-thru value for Router class.
	 *
	 * @var bool
	 */
	protected $translateURIDashes = false;

	/**
	 * Whether to match URI against controllers
	 * when it doesn't match defined routes.
	 *
	 * Not used here. Pass-thru value for Router class.
	 *
	 * @var bool
	 */
	protected $autoRoute = true;

	/**
	 * Defined placeholders that can be used
	 * within the
	 *
	 * @var array
	 */
	protected $placeholders = [
		'any'      => '.*',
		'segment'  => '[^/]+',
		'num'      => '[0-9]+',
		'alpha'    => '[a-zA-Z]+',
		'alphanum' => '[a-zA-Z0-9]+',
	];

	/**
	 * An array of all routes and their mappings.
	 *
	 * @var array
	 */
	protected $routes = [];

	/**
	 * The current method that the script is being called by.
	 *
	 * @var
	 */
	protected $http_verb;

	/**
	 * The default list of HTTP methods (and CLI for command line usage)
	 * that is allowed if no other method is provided.
	 *
	 * @var array
	 */
	protected $default_http_methods = ['options', 'get', 'head', 'post', 'put', 'delete', 'trace', 'connect', 'cli'];

	//--------------------------------------------------------------------

	public function __construct()
	{
		// Get HTTP verb
		$this->http_verb = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'cli';
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a single route to the collection.
	 *
	 * This provides a fairly simplistic solution without a lot of options.
	 * A much more flexible version is to present an array elements and
	 * multiple options to the 'map' method below.
	 *
	 * @param string       $route  The pattern to match against the URI
	 * @param string       $map    The controller or path to map to.
	 * @param string|array $method One of more HTTP methods that are allowed. If one,
	 *                             present as a single string (i.e. 'get').
	 *                             Otherwise, present an array of methods.
	 *
	 * @return void
	 */
	public function add(string $route, $map, $methods = null)
	{
		// If methods is null, than it should work
		// for any of the available methods.
		if (empty($methods))
		{
			$methods = $this->default_http_methods;
		}

		if ( ! is_array($methods))
		{
			$methods = [$methods];
		}

		// Ensure that all of our methods are lower-cased for compatibility
		array_walk($methods, function (&$item)
		{
			$item = strtolower($item);
		}
		);

		// To save on memory and processing later, we only add
		// the routes that are actually available at this time.
		if ( ! in_array($this->http_verb, $methods))
		{
			return;
		}

		// Replace our regex pattern placeholders with the actual thing
		// so that the Router doesn't need to know about any of this.
		foreach ($this->placeholders as $tag => $pattern)
		{
			$route = str_ireplace(':'.$tag, $pattern, $route);
		}

		// We need to ensure that the current namespace is added to the final mapping
		// so that it won't try to use the current namespace for the class.
		if (is_string($map) && strpos($map, '\\') === false)
		{
			if ( ! empty($this->defaultNamespace))
			{
				$map = $this->defaultNamespace.'\\'.$map;

				// Trim out any double back-slashes
				$map = str_replace('\\\\', '\\', $map);
			}
		}

		// Ensure that any strings are prefixed with backslash to get
		// out of the current namespace and into the proper one.
		if (is_string($map))
		{
			$map = '\\'.ltrim($map, '\\ ');
		}

		$this->routes[$route] = $map;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds an array of routes to the class all at once. This allows additional
	 * settings to be specified for all incoming routes, including:
	 *
	 *  namespace  Sets the namespace for all routes
	 *  hostname   Route must be on the set domain
	 *  prefix     Sets a string that will be prefixed to all routes (left side)
	 *
	 * @param array|null $routes
	 *
	 * @return mixed
	 */
	public function map(array $routes = null, array $options = [])
	{
		if (empty($_SERVER['HTTP_HOST']))
		{
			$_SERVER['HTTP_HOST'] = null;
		}

		$current_host = ! empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null;

		// If a hostname is provided as an option,
		// then don't waste time if our hostname doesn't match.
		if ( ! empty($options['hostname']) && strtolower($current_host) != strtolower($options['hostname']))
		{
			return;
		}

		// Save the current default namespace so
		// that we are able to replace it with one
		// the user specifies here.
		$old_namespace = $this->defaultNamespace;

		if (isset($options['namespace']))
		{
			$this->defaultNamespace = $options['namespace'];
		}

		$prefix = ! empty($options['prefix']) ? $options['prefix'] : '';

		foreach ($routes as $route => $map)
		{
			// Also need to trim any leading slashes to ensure
			// that the add() method adds the namespace correctly.
			if (is_string($map) && ! empty($options['namespace']))
			{
				$map = ltrim($map, '\\ ');
			}

			// If an array is passed in, that means that we are
			// dealing with HTTP verb routing so we need
			// to send all of them into the add() method
			// separately.
			if (is_array($map))
			{
				foreach ($map as $verb => $right)
				{
					// $route will now be the HTTP verb used.
					$this->add($prefix.$route, $right, $verb);
				}

				continue;
			}

			$this->add($prefix.$route, $map);
		}

		// Put our namespace back.
		$this->defaultNamespace = $old_namespace;
	}

	//--------------------------------------------------------------------

	/**
	 * Registers a new constraint with the system. Constraints are used
	 * by the routes as placeholders for regular expressions to make defining
	 * the routes more human-friendly.
	 *
	 * Once created, they can be used within curly brackets in routes.
	 *
	 * You can pass an associative array as $placeholder, and have
	 * multiple placeholders added at once.
	 *
	 * @param string|array $placeholder
	 * @param string       $pattern
	 *
	 * @return mixed
	 */
	public function addPlaceholder($placeholder, string $pattern=null): RouteCollectionInterface
	{
		if (! is_array($placeholder))
		{
			$placeholder = [$placeholder => $pattern];
		}

		$this->placeholders = array_merge($this->placeholders, $placeholder);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the default namespace to use for controllers when no other
	 * namespace has been specified.
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function setDefaultNamespace(string $value): RouteCollectionInterface
	{
		$this->defaultNamespace = $value;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the default controller to use when no other controller has been
	 * specified.
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function setDefaultController(string $value): RouteCollectionInterface
	{
		$this->defaultController = $value;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the name of the default controller. With Namespace.
	 *
	 * @return string
	 */
	public function getDefaultController(): string
	{
		return $this->defaultController;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the name of the default method to use within the controller.
	 *
	 * @return string
	 */
	public function getDefaultMethod(): string
	{
		return $this->defaultMethod;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the flag that tells whether to autoRoute URI against controllers.
	 *
	 * @return bool
	 */
	public function shouldAutoRoute()
	{
		return $this->autoRoute;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the default method to call on the controller when no other
	 * method has been set in the route.
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function setDefaultMethod(string $value): RouteCollectionInterface
	{
		$this->defaultMethod = $value;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Tells the system whether to convert dashes in URI strings into
	 * underscores. In some search engines, including Google, dashes
	 * create more meaning and make it easier for the search engine to
	 * find words and meaning in the URI for better SEO. But it
	 * doesn't work well with PHP method names....
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function setTranslateURIDashes(bool $value): RouteCollectionInterface
	{
		$this->translateURIDashes = $value;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * If TRUE, the system will attempt to match the URI against
	 * controllers by matching each segment against folders/files
	 * in APPPATH/controllers, when a match wasn't found against
	 * defined routes.
	 *
	 * If FALSE, will stop searching and do NO automatic routing.
	 *
	 * @param bool $value
	 *
	 * @return RouteCollection
	 */
	public function setAutoRoute(bool $value): RouteCollectionInterface
	{
		$this->autoRoute = $value;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the raw array of available routes.
	 *
	 * @return array
	 */
	public function getRoutes()
	{
		return $this->routes;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the current HTTP Verb being used.
	 *
	 * @return string
	 */
	public function getHTTPVerb()
	{
		return $this->http_verb;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the current Translate URI Dashes setting.
	 *
	 * @return bool
	 */
	public function shouldTranslateURIDashes()
	{
		return $this->translateURIDashes;
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to look up a route based on it's destination.
	 *
	 * If a route exists:
	 *
	 *      'path/(:any)/(:any)' => 'Controller::method/$1/$2'
	 *
	 * This method allows you to know the Controller and method
	 * and get the route that leads to it.
	 *
	 *      // Equals 'path/$param1/$param2'
	 *      reverseRoute('Controller::method', $param1, $param2);
	 *
	 * @param string $route
	 * @param        ...$params
	 */
	public function reverseRoute(string $search, ...$params): string
	{
		foreach ($this->routes as $from => $to)
		{
			// Lose any namespace slash at beginning of strings
			// to ensure more consistent match.
			$to     = ltrim($to, '\\');
			$search = ltrim($search, '\\');

			// If there's any chance of a match, then it will
			// be with $search at the beginning of the $to string.
			if (strpos($to, $search) !== 0)
			{
				continue;
			}

			// Ensure that the number of $params given here
			// matches the number of back-references in the route
			if (substr_count($to, '$') != count($params))
			{
				continue;
			}

			// Find all of our back-references in the original route
			preg_match_all('/\(([^)]+)\)/', $from, $matches);

			if (empty($matches[0]))
			{
				continue;
			}

			// Build our resulting string, inserting the $params in
			// the appropriate places.
			$route = $from;

			foreach ($matches[0] as $index => $pattern)
			{
				// Ensure that the param we're inserting matches
				// the expected param type.
				if (preg_match("/{$pattern}/", $params[$index]))
				{
					$route = str_replace($pattern, $params[$index], $route);
				}
				else
				{
					throw new \LogicException('A parameter does not match the expected type.');
				}
			}

			return $route;
		}

		// If we're still here, then we did not find a match.
		throw new \InvalidArgumentException('Unable to locate a valid route.');
	}

	//--------------------------------------------------------------------

}