<?php namespace CodeIgniter\Router;

class AltCollection implements RouteCollectionInterface
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
	 * The placeholder used when routing 'resources'
	 * when no other placeholder has been specified.
	 *
	 * @var string
	 */
	protected $defaultPlaceholder = 'any';

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
	protected $HTTPVerb;

	/**
	 * The default list of HTTP methods (and CLI for command line usage)
	 * that is allowed if no other method is provided.
	 *
	 * @var array
	 */
	protected $defaultHTTPMethods = ['options', 'get', 'head', 'post', 'put', 'delete', 'trace', 'connect', 'cli'];

	/**
	 * The name of the current group, if any.
	 *
	 * @var string
	 */
	protected $group = null;

	/**
	 * The current subdomain.
	 *
	 * @var string
	 */
	protected $currentSubdomain = null;

	//--------------------------------------------------------------------

	public function __construct()
	{
		// Get HTTP verb
		$this->HTTPVerb = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'cli';
	}

	//--------------------------------------------------------------------

	/**
	 * Registers a new constraint with the system. Constraints are used
	 * by the routes as placeholders for regular expressions to make defining
	 * the routes more human-friendly.
	 *
	 * You can pass an associative array as $placeholder, and have
	 * multiple placeholders added at once.
	 *
	 * @param string|array $placeholder
	 * @param string       $pattern
	 *
	 * @return mixed
	 */
	public function addPlaceholder($placeholder, string $pattern = null): RouteCollectionInterface
	{
		if ( ! is_array($placeholder))
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
		$this->defaultNamespace = filter_var($value, FILTER_SANITIZE_STRING);
		$this->defaultNamespace = rtrim($this->defaultNamespace, '\\') . '\\';

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
		$this->defaultController = filter_var($value, FILTER_SANITIZE_STRING);

		return $this;
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
		$this->defaultMethod = filter_var($value, FILTER_SANITIZE_STRING);

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
	 * @param bool $value
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
	 * Sets the default constraint to be used in the system. Typically
	 * for use with the 'resources' method.
	 *
	 * @param $constraint
	 */
	public function setDefaultConstraint(string $placeholder): RouteCollectionInterface
	{
		if (array_key_exists($placeholder, $this->placeholders)) {
			$this->defaultPlaceholder = $placeholder;
		}

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
	 * Returns the current value of the translateURIDashses setting.
	 *
	 * @param bool|false $val
	 *
	 * @return mixed
	 */
	public function shouldTranslateURIDashes(): bool
	{
		return $this->translateURIDashes;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the flag that tells whether to autoRoute URI against controllers.
	 *
	 * @return bool
	 */
	public function shouldAutoRoute(): bool
	{
		return $this->autoRoute;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the raw array of available routes.
	 *
	 * @return array
	 */
	public function getRoutes(): array
	{
		return $this->routes;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the current HTTP Verb being used.
	 *
	 * @return string
	 */
	public function getHTTPVerb(): string
	{
		return $this->HTTPVerb;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a single route to the collection.
	 *
	 * Example:
	 *      $routes->add('news', 'Posts::index');
	 *
	 * @param string       $route
	 * @param array|string $map
	 * @param string|array $method
	 *
	 * @return self RouteCollectionInterface
	 */
	public function add(string $from, $to, array $options = []): RouteCollectionInterface
	{
		$this->create($from, $to, $options);

		return $this;
	}

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Grouping Routes
	//--------------------------------------------------------------------

	/**
	 * Group a series of routes under a single URL segment. This is handy
	 * for grouping items into an admin area, like:
	 *
	 * Example:
	 *     // Creates route: admin/users
	 *     $route->group('admin', function() {
	 *            $route->resources('users');
	 *     });
	 *
	 * @param  string   $name     The name to group/prefix the routes with.
	 * @param  \Closure $callback An anonymous function that allows you route inside of this group.
	 *
	 * @return void
	 */
	public function group($name, \Closure $callback)
	{
		$old_group = $this->group;

		// To register a route, we'll set a flag so that our router
		// so it will see the group name.
		$this->group = ltrim($old_group.'/'.$name, '/');

		call_user_func($callback, $this);

		// Make sure to clear the group name so we don't accidentally
		// group any ones we didn't want to.
		$this->group = $old_group;
	}

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// HTTP Verb-based routing
	//--------------------------------------------------------------------
	// Routing works here because, as the routes config file is read in,
	// the various HTTP verb-based routes will only be added to the in-memory
	// routes if it is a call that should respond to that verb.
	//
	// The options array is typically used to pass in an 'as' or var, but may
	// be expanded in the future. See the docblock for 'add' method above for
	// current list of globally available options.
	//

	/**
	 * Creates a collections of HTTP-verb based routes for a controller.
	 *
	 * Possible Options:
	 *      'controller'    - Customize the name of the controller used in the 'to' route
	 *      'placeholder'   - The regex used by the Router. Defaults to '(:any)'
	 *
	 * Example:
	 *      $route->resources('photos');
	 *
	 *      // Generates the following routes:
	 *      HTTP Verb | Path        | Action        | Used for...
	 *      ----------+-------------+---------------+-----------------
	 *      GET         /photos             list_all        display a list of photos
	 *      GET         /photos/{id}        show            display a specific photo
	 *      POST        /photos             create          create a new photo
	 *      PUT         /photos/{id}        update          update an existing photo
	 *      DELETE      /photos/{id}/delete delete          delete an existing photo
	 *
	 * @param  string $name    The name of the controller to route to.
	 * @param  array  $options An list of possible ways to customize the routing.
	 *
	 * @return RouteCollectionInterface
	 */
	public function resources($name, $options = []): RouteCollectionInterface
	{
		// In order to allow customization of the route the
		// resources are sent to, we need to have a new name
		// to store the values in.
		$new_name = ucfirst($name);

		// If a new controller is specified, then we replace the
		// $name value with the name of the new controller.
		if (isset($options['controller'])) {
			$new_name = ucfirst(filter_var($options['controller'], FILTER_SANITIZE_STRING));
		}

		// In order to allow customization of allowed id values
		// we need someplace to store them.
		$id = isset($this->placeholders[$this->defaultPlaceholder]) ? $this->placeholders[$this->defaultPlaceholder] :
				'(:any)';

		if (isset($options['placeholder'])) {
			$id = $options['placeholder'];
		}

		// Make sure we capture back-references
		$id = '('.trim($id, '()').')';

		$this->get($name, $new_name . '::list_all', $options)
		     ->get($name . '/' . $id, $new_name . '::show/$1', $options)
		     ->post($name, $new_name . '::create', $options)
		     ->put($name . '/' . $id, $new_name . '::update/$1', $options)
		     ->delete($name . '/' . $id, $new_name . '::delete/$1', $options);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Specifies a single route to match for multiple HTTP Verbs.
	 *
	 * Example:
	 *  $route->match( ['get', 'post'], 'users/(:num)', 'users/$1);
	 *
	 * @param array $verbs
	 * @param       $from
	 * @param       $to
	 * @param array $options
	 */
	public function match($verbs = [], $from, $to, $options = []): RouteCollectionInterface
	{
		foreach ($verbs as $verb)
		{
			$verb = strtolower($verb);

			$this->{$verb}($from, $to, $options);
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Specifies a route that is only available to GET requests.
	 *
	 * @param       $from
	 * @param       $to
	 * @param array $options
	 */
	public function get($from, $to, $options = []): RouteCollectionInterface
	{
		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET')
		{
			$this->create($from, $to, $options);
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Specifies a route that is only available to POST requests.
	 *
	 * @param       $from
	 * @param       $to
	 * @param array $options
	 */
	public function post($from, $to, $options = []): RouteCollectionInterface
	{
		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$this->create($from, $to, $options);
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Specifies a route that is only available to PUT requests.
	 *
	 * @param       $from
	 * @param       $to
	 * @param array $options
	 */
	public function put($from, $to, $options = []): RouteCollectionInterface
	{
		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'PUT')
		{
			$this->create($from, $to, $options);
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Specifies a route that is only available to DELETE requests.
	 *
	 * @param       $from
	 * @param       $to
	 * @param array $options
	 */
	public function delete($from, $to, $options = []): RouteCollectionInterface
	{
		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'DELETE')
		{
			$this->create($from, $to, $options);
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Specifies a route that is only available to HEAD requests.
	 *
	 * @param       $from
	 * @param       $to
	 * @param array $options
	 */
	public function head($from, $to, $options = []): RouteCollectionInterface
	{
		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'HEAD')
		{
			$this->create($from, $to, $options);
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Specifies a route that is only available to PATCH requests.
	 *
	 * @param       $from
	 * @param       $to
	 * @param array $options
	 */
	public function patch($from, $to, $options = []): RouteCollectionInterface
	{
		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'PATCH')
		{
			$this->create($from, $to, $options);
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Specifies a route that is only available to OPTIONS requests.
	 *
	 * @param       $from
	 * @param       $to
	 * @param array $options
	 */
	public function options($from, $to, $options = []): RouteCollectionInterface
	{
		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'OPTIONS')
		{
			$this->create($from, $to, $options);
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Limits the routes to a specified ENVIRONMENT or they won't run.
	 *
	 * @param $env
	 * @param callable $callback
	 *
	 * @return $this
	 */
	public function environment($env, \Closure $callback): RouteCollectionInterface
	{
		if (ENVIRONMENT == $env)
		{
			call_user_func($callback, $this);
		}

		return $this;
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

	/**
	 * Does the heavy lifting of creating an actual route. You must specify
	 * the request method(s) that this route will work for. They can be separated
	 * by a pipe character "|" if there is more than one.
	 *
	 * @param  string $from
	 * @param  array  $to
	 * @param array   $options
	 */
	protected function create(string $from, $to, array $options = [])
	{
		$prefix = is_null($this->group) ? '' : $this->group.'/';

		$from = filter_var($prefix.$from, FILTER_SANITIZE_STRING);

		// Limiting to subdomains?
		if (isset($options['subdomain']) && ! empty($options['subdomain']))
		{
			// If we don't match the current subdomain, then
			// we don't need to add the route.
			if ( ! $this->checkSubdomains($options['subdomain']))
			{
				return;
			}
		}

		// Are we offsetting the parameters?
		// If so, take care of them here in one
		// fell swoop.
		if (isset($options['offset']) && is_string($to))
		{
			// Get a constant string to work with.
			$to = preg_replace('/(\$\d+)/', '$X', $to);

			for ($i = (int)$options['offset'] + 1; $i < (int)$options['offset'] + 7; $i++)
			{
				$to = preg_replace_callback(
					'/\$X/',
					function ($m) use ($i)
					{
						return '$'.$i;
					},
					$to,
					1
				);
			}
		}

		// Replace our regex pattern placeholders with the actual thing
		// so that the Router doesn't need to know about any of this.
		// @todo - Check if strtr is any faster
		foreach ($this->placeholders as $tag => $pattern)
		{
			$from = str_ireplace(':'.$tag, $pattern, $from);
		}

		// If no namespace found, add the default namespace
		if (is_string($to) && strpos($to, '\\') === false)
		{
			$to = $this->defaultNamespace.$to;
		}

		// Always ensure that we escape our namespace so we're not pointing to
		// \CodeIgniter\Routes\Controller::method.
		if (is_string($to))
		{
			$to = '\\'.ltrim($to, '\\');
		}

		$this->routes[$from] = $to;
	}

	//--------------------------------------------------------------------

	/**
	 * Compares the subdomain(s) passed in against the current subdomain
	 * on this page request.
	 *
	 * @param $subdomains
	 *
	 * @return bool
	 */
	private function checkSubdomains($subdomains)
	{
		if (is_null($this->currentSubdomain))
		{
			$this->currentSubdomain = $this->determineCurrentSubdomain();
		}

		if ( ! is_array($subdomains))
		{
			$subdomains = [$subdomains];
		}

		// Routes can be limited to any sub-domain. In that case, though,
		// it does require a sub-domain to be present.
		if (! empty($this->currentSubdomain) && in_array('*', $subdomains))
		{
			return true;
		}

		foreach ($subdomains as $subdomain)
		{
			if ($subdomain == $this->currentSubdomain)
			{
				return true;
			}
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Examines the HTTP_HOST to get a best match for the subdomain. It
	 * won't be perfect, but should work for our needs.
	 *
	 * It's especially not perfect since it's possible to register a domain
	 * with a period (.) as part of the domain name.
	 */
	private function determineCurrentSubdomain()
	{
		$parsedUrl = parse_url($_SERVER['HTTP_HOST']);

		$host = explode('.', $parsedUrl['host']);

		if ($host[0] == 'www') unset($host[0]);

		// Get rid of any domains, which will be the last
		unset($host[count($host)]);

		// Account for .co.uk, .co.nz, etc. domains
		if (end($host) == 'co') $host = array_slice($host, 0, -1);

		// If we only have 1 part left, then we don't have a sub-domain.
		if (count($host) == 1)
		{
			// Set it to false so we don't make it back here again.
			return false;
		}

		return array_shift($host);
	}
	//--------------------------------------------------------------------
}