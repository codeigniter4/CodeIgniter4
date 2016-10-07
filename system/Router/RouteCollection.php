<?php namespace CodeIgniter\Router;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

/**
 * Class RouteCollection
 *
 * @todo Implement nested resource routing (See CakePHP)
 *
 * @package CodeIgniter\Router
 */
class RouteCollection implements RouteCollectionInterface
{
	/**
	 * The namespace to be added to any Controllers.
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
	 * Whether to match URI against Controllers
	 * when it doesn't match defined routes.
	 *
	 * Not used here. Pass-thru value for Router class.
	 *
	 * @var bool
	 */
	protected $autoRoute = true;

	/**
	 * A callable that will be shown
	 * when the route cannot be matched.
	 *
	 * @var string|closure
	 */
	protected $override404;

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
		'hash'     => '[^/]+',
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

	/**
	 * Stores copy of current options being
	 * applied during creation.
	 * @var null
	 */
	protected $currentOptions = null;

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 */
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
	public function addPlaceholder(string $placeholder, string $pattern = null): RouteCollectionInterface
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
	 * Sets the default namespace to use for Controllers when no other
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
	 * Controllers by matching each segment against folders/files
	 * in APPPATH/Controllers, when a match wasn't found against
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
	 * Sets the class/method that should be called if routing doesn't
	 * find a match. It can be either a closure or the controller/method
	 * name exactly like a route is defined: Users::index
	 *
	 * This setting is passed to the Router class and handled there.
	 *
	 * @param callable|null $callable
	 *
	 * @return $this
	 */
	public function set404Override($callable = null): RouteCollectionInterface
	{
	    $this->override404 = $callable;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the 404 Override setting, which can be null, a closure
	 * or the controller/string.
	 *
	 * @return string|\Closure|null
	 */
	public function get404Override()
	{
		return $this->override404;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the default constraint to be used in the system. Typically
	 * for use with the 'resources' method.
	 *
	 * @param $placeholder
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
	 * Returns the default namespace as set in the Routes config file.
	 *
	 * @return string
	 */
	public function getDefaultNamespace(): string
	{
	    return $this->defaultNamespace;
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
	 * Returns the flag that tells whether to autoRoute URI against Controllers.
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
		$routes = [];

		foreach ($this->routes as $r)
		{
			$key = key($r['route']);
			$routes[$key] = $r['route'][$key];
		}

		return $routes;
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
	 * A shortcut method to add a number of routes at a single time.
	 * It does not allow any options to be set on the route, or to
	 * define the method used.
	 *
	 * @param array $routes
	 * @param array $options
	 *
	 * @return RouteCollectionInterface
	 */
	public function map(array $routes = [], array $options = null): RouteCollectionInterface
	{
	    foreach ($routes as $from => $to)
	    {
		    $this->add($from, $to, $options);
	    }

		return $this;
	}

	//--------------------------------------------------------------------


	/**
	 * Adds a single route to the collection.
	 *
	 * Example:
	 *      $routes->add('news', 'Posts::index');
	 *
	 * @param string       $from
	 * @param array|string $to
	 * @param $options
	 *
	 * @return self RouteCollectionInterface
	 */
	public function add(string $from, $to, array $options = null): RouteCollectionInterface
	{
		$this->create($from, $to, $options);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a temporary redirect from one route to another. Used for
	 * redirecting traffic from old, non-existing routes to the new
	 * moved routes.
	 *
	 * @param string $from     The pattern to match against
	 * @param string $to       Either a route name or a URI to redirect to
	 * @param int    $status   The HTTP status code that should be returned with this redirect
	 */
	public function addRedirect(string $from, string $to, int $status = 302)
	{
		// Use the named route's pattern if this is a named route.
		if (array_key_exists($to, $this->routes))
		{
			$to = $this->routes[$to]['route'];
		}

	    $this->create($from, $to, ['redirect' => $status]);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Determines if the route is a redirecting route.
	 *
	 * @param string $from
	 *
	 * @return bool
	 */
	public function isRedirect(string $from): bool
	{
		foreach ($this->routes as $name => $route)
		{
			// Named route?
			if ($name == $from || key($route['route']) == $from)
			{
				return isset($route['redirect']) && is_numeric($route['redirect']);
			}
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Grabs the HTTP status code from a redirecting Route.
	 *
	 * @param string $from
	 *
	 * @return int
	 */
	public function getRedirectCode(string $from): int
	{
		foreach ($this->routes as $name => $route)
		{
			// Named route?
			if ($name == $from || key($route['route']) == $from)
			{
				return $route['redirect'] ?? 0;
			}
		}

		return 0;
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
	 * @param  $params
	 *
	 * @return void
	 */
	public function group($name, ...$params)
	{
		$oldGroup   = $this->group;
		$oldOptions = $this->currentOptions;

		// To register a route, we'll set a flag so that our router
		// so it will see the group name.
		$this->group = ltrim($oldGroup.'/'.$name, '/');

		$callback = array_pop($params);

		if (count($params) && is_array($params[0]))
		{
			$this->currentOptions = array_shift($params);
		}

		if (is_callable($callback))
		{
			$callback($this);
		}

		$this->group = $oldGroup;
		$this->currentOptions = $oldOptions;
	}

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// HTTP Verb-based routing
	//--------------------------------------------------------------------
	// Routing works here because, as the routes Config file is read in,
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
	 *      GET         /photos             listAll         display a list of photos
	 *      GET         /photos/{id}        show            display a specific photo
	 *      POST        /photos             create          create a new photo
	 *      PUT         /photos/{id}        update          update an existing photo
	 *      DELETE      /photos/{id}        delete          delete an existing photo
	 *
	 *  If 'websafe' option is present, the following paths are also available:
	 *
	 *      POST        /photos/{id}        update
	 *      DELETE      /photos/{id}/delete delete
	 *
	 * @param  string $name    The name of the controller to route to.
	 * @param  array  $options An list of possible ways to customize the routing.
	 *
	 * @return RouteCollectionInterface
	 */
	public function resource(string $name, array $options = null): RouteCollectionInterface
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
				'(:segment)';

		if (isset($options['placeholder'])) {
			$id = $options['placeholder'];
		}

		// Make sure we capture back-references
		$id = '('.trim($id, '()').')';

		$methods = isset($options['only'])
			? is_string($options['only']) ? explode(',', $options['only']) : $options['only']
			: ['listAll', 'show', 'create', 'update', 'delete'];

		if (in_array('listAll', $methods)) $this->get($name, $new_name . '::listAll', $options);
		if (in_array('show', $methods))    $this->get($name . '/' . $id, $new_name . '::show/$1', $options);
		if (in_array('create', $methods))  $this->post($name, $new_name . '::create', $options);
		if (in_array('update', $methods))  $this->put($name . '/' . $id, $new_name . '::update/$1', $options);
		if (in_array('delete', $methods))  $this->delete($name . '/' . $id, $new_name . '::delete/$1', $options);

		// Web Safe?
		if (isset($options['websafe']))
		{
			if (in_array('update', $methods))  $this->post($name . '/' . $id, $new_name . '::update/$1', $options);
			if (in_array('delete', $methods))  $this->post($name . '/' . $id .'/delete', $new_name . '::delete/$1', $options);
		}

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
	public function match(array $verbs = [], string $from, string $to, array $options = null): RouteCollectionInterface
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
	public function get(string $from, string $to, array $options = null): RouteCollectionInterface
	{
		if ($this->HTTPVerb == 'get')
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
	public function post(string $from, string $to, array $options = null): RouteCollectionInterface
	{
		if ($this->HTTPVerb == 'post')
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
	public function put(string $from, string $to, array $options = null): RouteCollectionInterface
	{
		if ($this->HTTPVerb == 'put')
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
	public function delete(string $from, string $to, array $options = null): RouteCollectionInterface
	{
		if ($this->HTTPVerb == 'delete')
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
	public function head(string $from, string $to, array $options = null): RouteCollectionInterface
	{
		if ($this->HTTPVerb == 'head')
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
	public function patch(string $from, string $to, array $options = null): RouteCollectionInterface
	{
		if ($this->HTTPVerb == 'patch')
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
	public function options(string $from, string $to, array $options = null): RouteCollectionInterface
	{
		if ($this->HTTPVerb == 'options')
		{
			$this->create($from, $to, $options);
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Specifies a route that is only available to command-line requests.
	 *
	 * @param       $from
	 * @param       $to
	 * @param array $options
	 */
	public function cli(string $from, string $to, array $options = null): RouteCollectionInterface
	{
		if ($this->HTTPVerb == 'cli')
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
	public function environment(string $env, \Closure $callback): RouteCollectionInterface
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
	 * @param string $search
	 * @param        ...$params
     *
     * @return string
	 */
	public function reverseRoute(string $search, ...$params): string
	{
		// Named routes get higher priority.
		if (array_key_exists($search, $this->routes))
		{
			return $this->fillRouteParams(key($this->routes[$search]['route']), $params);
		}

		// If it's not a named route, then loop over
		// all routes to find a match.
		foreach ($this->routes as $route)
		{
			$from = key($route['route']);
			$to   = $route['route'][$from];

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

			return $this->fillRouteParams($from, $params);
		}

		// If we're still here, then we did not find a match.
		throw new \InvalidArgumentException('Unable to locate a valid route.');
	}

	//--------------------------------------------------------------------

	/**
	 * Given a
	 *
	 * @param array      $from
	 * @param array|null $params
	 *
	 * @return string
	 */
	protected function fillRouteParams(string $from, array $params = null): string
	{
		// Find all of our back-references in the original route
		preg_match_all('/\(([^)]+)\)/', $from, $matches);

		if (empty($matches[0]))
		{
			return '/'.ltrim($from, '/');
		}

		// Build our resulting string, inserting the $params in
		// the appropriate places.
		foreach ($matches[0] as $index => $pattern)
		{
			// Ensure that the param we're inserting matches
			// the expected param type.
			if (preg_match("/{$pattern}/", $params[$index]))
			{
				$from = str_replace($pattern, $params[$index], $from);
			}
			else
			{
				throw new \LogicException('A parameter does not match the expected type.');
			}
		}

		return '/'.ltrim($from, '/');
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
	protected function create(string $from, $to, array $options = null)
	{
		$prefix = is_null($this->group) ? '' : $this->group.'/';

		$from = filter_var($prefix.$from, FILTER_SANITIZE_STRING);

		if (is_null($options))
		{
			$options = $this->currentOptions;
		}

		// Hostname limiting?
		if (isset($options['hostname']) && ! empty($options['hostname']))
		{
			// @todo determine if there's a way to whitelist hosts?
			if (strtolower($_SERVER['HTTP_HOST']) != strtolower($options['hostname']))
			{
				return;
			}
		}

		// Limiting to subdomains?
		else if (isset($options['subdomain']) && ! empty($options['subdomain']))
		{
			// If we don't match the current subdomain, then
			// we don't need to add the route.
			if ( ! $this->checkSubdomains($options['subdomain']))
			{
				return;
			}
		}

		// Are we offsetting the binds?
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
		foreach ($this->placeholders as $tag => $pattern)
		{
			$from = str_ireplace(':'.$tag, $pattern, $from);
		}

		// If no namespace found, add the default namespace
		if (is_string($to) && strpos($to, '\\') === false)
		{
			$namespace = $options['namespace'] ?? $this->defaultNamespace;
			$to = trim($namespace, '\\') .'\\'.$to;
		}

		// Always ensure that we escape our namespace so we're not pointing to
		// \CodeIgniter\Routes\Controller::method.
		if (is_string($to))
		{
			$to = '\\'.ltrim($to, '\\');
		}

		$name = $options['as'] ?? $from;

		$this->routes[$name] = [
			'route' => [$from => $to]
		];

		// Is this a redirect?
		if (isset($options['redirect']) && is_numeric($options['redirect']))
		{
			$this->routes[$name]['redirect'] = $options['redirect'];
		}
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
