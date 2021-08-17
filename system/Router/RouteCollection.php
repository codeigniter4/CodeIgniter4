<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Router;

use Closure;
use CodeIgniter\Autoloader\FileLocator;
use CodeIgniter\Router\Exceptions\RouterException;
use Config\Modules;
use Config\Services;
use InvalidArgumentException;

/**
 * Class RouteCollection
 *
 * @todo Implement nested resource routing (See CakePHP)
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
     * @var Closure|string
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
        'alphanum' => '[a-zA-Z0-9]+',
        'num'      => '[0-9]+',
        'alpha'    => '[a-zA-Z]+',
        'hash'     => '[^/]+',
    ];

    /**
     * An array of all routes and their mappings.
     *
     * @var array
     */
    protected $routes = [
        '*'       => [],
        'options' => [],
        'get'     => [],
        'head'    => [],
        'post'    => [],
        'put'     => [],
        'delete'  => [],
        'trace'   => [],
        'connect' => [],
        'cli'     => [],
    ];

    /**
     * Array of routes options
     *
     * @var array
     */
    protected $routesOptions = [];

    /**
     * The current method that the script is being called by.
     *
     * @var string
     */
    protected $HTTPVerb = '*';

    /**
     * The default list of HTTP methods (and CLI for command line usage)
     * that is allowed if no other method is provided.
     *
     * @var array
     */
    protected $defaultHTTPMethods = [
        'options',
        'get',
        'head',
        'post',
        'put',
        'delete',
        'trace',
        'connect',
        'cli',
    ];

    /**
     * The name of the current group, if any.
     *
     * @var string|null
     */
    protected $group;

    /**
     * The current subdomain.
     *
     * @var string|null
     */
    protected $currentSubdomain;

    /**
     * Stores copy of current options being
     * applied during creation.
     *
     * @var array|null
     */
    protected $currentOptions;

    /**
     * A little performance booster.
     *
     * @var bool
     */
    protected $didDiscover = false;

    /**
     * Handle to the file locator to use.
     *
     * @var FileLocator
     */
    protected $fileLocator;

    /**
     * Handle to the modules config.
     *
     * @var Modules
     */
    protected $moduleConfig;

    /**
     * Flag for sorting routes by priority.
     *
     * @var bool
     */
    protected $prioritize = false;

    /**
     * Route priority detection flag.
     *
     * @var bool
     */
    protected $prioritizeDetected = false;

    /**
     * Constructor
     */
    public function __construct(FileLocator $locator, Modules $moduleConfig)
    {
        $this->fileLocator  = $locator;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * Registers a new constraint with the system. Constraints are used
     * by the routes as placeholders for regular expressions to make defining
     * the routes more human-friendly.
     *
     * You can pass an associative array as $placeholder, and have
     * multiple placeholders added at once.
     *
     * @param array|string $placeholder
     */
    public function addPlaceholder($placeholder, ?string $pattern = null): RouteCollectionInterface
    {
        if (! is_array($placeholder)) {
            $placeholder = [$placeholder => $pattern];
        }

        $this->placeholders = array_merge($this->placeholders, $placeholder);

        return $this;
    }

    /**
     * Sets the default namespace to use for Controllers when no other
     * namespace has been specified.
     */
    public function setDefaultNamespace(string $value): RouteCollectionInterface
    {
        $this->defaultNamespace = esc(strip_tags($value));
        $this->defaultNamespace = rtrim($this->defaultNamespace, '\\') . '\\';

        return $this;
    }

    /**
     * Sets the default controller to use when no other controller has been
     * specified.
     */
    public function setDefaultController(string $value): RouteCollectionInterface
    {
        $this->defaultController = esc(strip_tags($value));

        return $this;
    }

    /**
     * Sets the default method to call on the controller when no other
     * method has been set in the route.
     */
    public function setDefaultMethod(string $value): RouteCollectionInterface
    {
        $this->defaultMethod = esc(strip_tags($value));

        return $this;
    }

    /**
     * Tells the system whether to convert dashes in URI strings into
     * underscores. In some search engines, including Google, dashes
     * create more meaning and make it easier for the search engine to
     * find words and meaning in the URI for better SEO. But it
     * doesn't work well with PHP method names....
     */
    public function setTranslateURIDashes(bool $value): RouteCollectionInterface
    {
        $this->translateURIDashes = $value;

        return $this;
    }

    /**
     * If TRUE, the system will attempt to match the URI against
     * Controllers by matching each segment against folders/files
     * in APPPATH/Controllers, when a match wasn't found against
     * defined routes.
     *
     * If FALSE, will stop searching and do NO automatic routing.
     */
    public function setAutoRoute(bool $value): RouteCollectionInterface
    {
        $this->autoRoute = $value;

        return $this;
    }

    /**
     * Sets the class/method that should be called if routing doesn't
     * find a match. It can be either a closure or the controller/method
     * name exactly like a route is defined: Users::index
     *
     * This setting is passed to the Router class and handled there.
     *
     * @param callable|null $callable
     */
    public function set404Override($callable = null): RouteCollectionInterface
    {
        $this->override404 = $callable;

        return $this;
    }

    /**
     * Returns the 404 Override setting, which can be null, a closure
     * or the controller/string.
     *
     * @return Closure|string|null
     */
    public function get404Override()
    {
        return $this->override404;
    }

    /**
     * Will attempt to discover any additional routes, either through
     * the local PSR4 namespaces, or through selected Composer packages.
     */
    protected function discoverRoutes()
    {
        if ($this->didDiscover) {
            return;
        }

        // We need this var in local scope
        // so route files can access it.
        $routes = $this;

        if ($this->moduleConfig->shouldDiscover('routes')) {
            $files = $this->fileLocator->search('Config/Routes.php');

            foreach ($files as $file) {
                // Don't include our main file again...
                if ($file === APPPATH . 'Config/Routes.php') {
                    continue;
                }

                include $file;
            }
        }

        $this->didDiscover = true;
    }

    /**
     * Sets the default constraint to be used in the system. Typically
     * for use with the 'resource' method.
     */
    public function setDefaultConstraint(string $placeholder): RouteCollectionInterface
    {
        if (array_key_exists($placeholder, $this->placeholders)) {
            $this->defaultPlaceholder = $placeholder;
        }

        return $this;
    }

    /**
     * Returns the name of the default controller. With Namespace.
     */
    public function getDefaultController(): string
    {
        return $this->defaultController;
    }

    /**
     * Returns the name of the default method to use within the controller.
     */
    public function getDefaultMethod(): string
    {
        return $this->defaultMethod;
    }

    /**
     * Returns the default namespace as set in the Routes config file.
     */
    public function getDefaultNamespace(): string
    {
        return $this->defaultNamespace;
    }

    /**
     * Returns the current value of the translateURIDashes setting.
     */
    public function shouldTranslateURIDashes(): bool
    {
        return $this->translateURIDashes;
    }

    /**
     * Returns the flag that tells whether to autoRoute URI against Controllers.
     */
    public function shouldAutoRoute(): bool
    {
        return $this->autoRoute;
    }

    /**
     * Returns the raw array of available routes.
     */
    public function getRoutes(?string $verb = null): array
    {
        if (empty($verb)) {
            $verb = $this->getHTTPVerb();
        }

        // Since this is the entry point for the Router,
        // take a moment to do any route discovery
        // we might need to do.
        $this->discoverRoutes();

        $routes     = [];
        $collection = [];

        if (isset($this->routes[$verb])) {
            // Keep current verb's routes at the beginning so they're matched
            // before any of the generic, "add" routes.
            if (isset($this->routes['*'])) {
                $extraRules = array_diff_key($this->routes['*'], $this->routes[$verb]);
                $collection = array_merge($this->routes[$verb], $extraRules);
            }

            foreach ($collection as $r) {
                $key          = key($r['route']);
                $routes[$key] = $r['route'][$key];
            }
        }

        // sorting routes by priority
        if ($this->prioritizeDetected && $this->prioritize && $routes !== []) {
            $order = [];

            foreach ($routes as $key => $value) {
                $key                    = $key === '/' ? $key : ltrim($key, '/ ');
                $priority               = $this->getRoutesOptions($key, $verb)['priority'] ?? 0;
                $order[$priority][$key] = $value;
            }

            ksort($order);
            $routes = array_merge(...$order);
        }

        return $routes;
    }

    /**
     * Returns one or all routes options
     */
    public function getRoutesOptions(?string $from = null, ?string $verb = null): array
    {
        $options = $this->loadRoutesOptions($verb);

        return $from ? $options[$from] ?? [] : $options;
    }

    /**
     * Returns the current HTTP Verb being used.
     */
    public function getHTTPVerb(): string
    {
        return $this->HTTPVerb;
    }

    /**
     * Sets the current HTTP verb.
     * Used primarily for testing.
     *
     * @return $this
     */
    public function setHTTPVerb(string $verb)
    {
        $this->HTTPVerb = $verb;

        return $this;
    }

    /**
     * A shortcut method to add a number of routes at a single time.
     * It does not allow any options to be set on the route, or to
     * define the method used.
     */
    public function map(array $routes = [], ?array $options = null): RouteCollectionInterface
    {
        foreach ($routes as $from => $to) {
            $this->add($from, $to, $options);
        }

        return $this;
    }

    /**
     * Adds a single route to the collection.
     *
     * Example:
     *      $routes->add('news', 'Posts::index');
     *
     * @param array|string $to
     */
    public function add(string $from, $to, ?array $options = null): RouteCollectionInterface
    {
        $this->create('*', $from, $to, $options);

        return $this;
    }

    /**
     * Adds a temporary redirect from one route to another. Used for
     * redirecting traffic from old, non-existing routes to the new
     * moved routes.
     *
     * @param string $from   The pattern to match against
     * @param string $to     Either a route name or a URI to redirect to
     * @param int    $status The HTTP status code that should be returned with this redirect
     *
     * @return RouteCollection
     */
    public function addRedirect(string $from, string $to, int $status = 302)
    {
        // Use the named route's pattern if this is a named route.
        if (array_key_exists($to, $this->routes['*'])) {
            $to = $this->routes['*'][$to]['route'];
        } elseif (array_key_exists($to, $this->routes['get'])) {
            $to = $this->routes['get'][$to]['route'];
        }

        $this->create('*', $from, $to, ['redirect' => $status]);

        return $this;
    }

    /**
     * Determines if the route is a redirecting route.
     */
    public function isRedirect(string $from): bool
    {
        foreach ($this->routes['*'] as $name => $route) {
            // Named route?
            if ($name === $from || key($route['route']) === $from) {
                return isset($route['redirect']) && is_numeric($route['redirect']);
            }
        }

        return false;
    }

    /**
     * Grabs the HTTP status code from a redirecting Route.
     */
    public function getRedirectCode(string $from): int
    {
        foreach ($this->routes['*'] as $name => $route) {
            // Named route?
            if ($name === $from || key($route['route']) === $from) {
                return $route['redirect'] ?? 0;
            }
        }

        return 0;
    }

    /**
     * Group a series of routes under a single URL segment. This is handy
     * for grouping items into an admin area, like:
     *
     * Example:
     *     // Creates route: admin/users
     *     $route->group('admin', function() {
     *            $route->resource('users');
     *     });
     *
     * @param string         $name      The name to group/prefix the routes with.
     * @param array|callable ...$params
     */
    public function group(string $name, ...$params)
    {
        $oldGroup   = $this->group;
        $oldOptions = $this->currentOptions;

        // To register a route, we'll set a flag so that our router
        // so it will see the group name.
        // If the group name is empty, we go on using the previously built group name.
        $this->group = $name ? ltrim($oldGroup . '/' . $name, '/') : $oldGroup;

        $callback = array_pop($params);

        if ($params && is_array($params[0])) {
            $this->currentOptions = array_shift($params);
        }

        if (is_callable($callback)) {
            $callback($this);
        }

        $this->group          = $oldGroup;
        $this->currentOptions = $oldOptions;
    }

    /*
     * --------------------------------------------------------------------
     *  HTTP Verb-based routing
     * --------------------------------------------------------------------
     * Routing works here because, as the routes Config file is read in,
     * the various HTTP verb-based routes will only be added to the in-memory
     * routes if it is a call that should respond to that verb.
     *
     * The options array is typically used to pass in an 'as' or var, but may
     * be expanded in the future. See the docblock for 'add' method above for
     * current list of globally available options.
     */

    /**
     * Creates a collections of HTTP-verb based routes for a controller.
     *
     * Possible Options:
     *      'controller'    - Customize the name of the controller used in the 'to' route
     *      'placeholder'   - The regex used by the Router. Defaults to '(:any)'
     *      'websafe'   -	- '1' if only GET and POST HTTP verbs are supported
     *
     * Example:
     *
     *      $route->resource('photos');
     *
     *      // Generates the following routes:
     *      HTTP Verb | Path        | Action        | Used for...
     *      ----------+-------------+---------------+-----------------
     *      GET         /photos             index           an array of photo objects
     *      GET         /photos/new         new             an empty photo object, with default properties
     *      GET         /photos/{id}/edit   edit            a specific photo object, editable properties
     *      GET         /photos/{id}        show            a specific photo object, all properties
     *      POST        /photos             create          a new photo object, to add to the resource
     *      DELETE      /photos/{id}        delete          deletes the specified photo object
     *      PUT/PATCH   /photos/{id}        update          replacement properties for existing photo
     *
     *  If 'websafe' option is present, the following paths are also available:
     *
     *      POST		/photos/{id}/delete delete
     *      POST        /photos/{id}        update
     *
     * @param string     $name    The name of the resource/controller to route to.
     * @param array|null $options An list of possible ways to customize the routing.
     */
    public function resource(string $name, ?array $options = null): RouteCollectionInterface
    {
        // In order to allow customization of the route the
        // resources are sent to, we need to have a new name
        // to store the values in.
        $newName = implode('\\', array_map('ucfirst', explode('/', $name)));
        // If a new controller is specified, then we replace the
        // $name value with the name of the new controller.
        if (isset($options['controller'])) {
            $newName = ucfirst(filter_var($options['controller'], FILTER_SANITIZE_STRING));
        }

        // In order to allow customization of allowed id values
        // we need someplace to store them.
        $id = $options['placeholder'] ?? $this->placeholders[$this->defaultPlaceholder] ?? '(:segment)';

        // Make sure we capture back-references
        $id = '(' . trim($id, '()') . ')';

        $methods = isset($options['only']) ? (is_string($options['only']) ? explode(',', $options['only']) : $options['only']) : ['index', 'show', 'create', 'update', 'delete', 'new', 'edit'];

        if (isset($options['except'])) {
            $options['except'] = is_array($options['except']) ? $options['except'] : explode(',', $options['except']);

            foreach ($methods as $i => $method) {
                if (in_array($method, $options['except'], true)) {
                    unset($methods[$i]);
                }
            }
        }

        if (in_array('index', $methods, true)) {
            $this->get($name, $newName . '::index', $options);
        }
        if (in_array('new', $methods, true)) {
            $this->get($name . '/new', $newName . '::new', $options);
        }
        if (in_array('edit', $methods, true)) {
            $this->get($name . '/' . $id . '/edit', $newName . '::edit/$1', $options);
        }
        if (in_array('show', $methods, true)) {
            $this->get($name . '/' . $id, $newName . '::show/$1', $options);
        }
        if (in_array('create', $methods, true)) {
            $this->post($name, $newName . '::create', $options);
        }
        if (in_array('update', $methods, true)) {
            $this->put($name . '/' . $id, $newName . '::update/$1', $options);
            $this->patch($name . '/' . $id, $newName . '::update/$1', $options);
        }
        if (in_array('delete', $methods, true)) {
            $this->delete($name . '/' . $id, $newName . '::delete/$1', $options);
        }

        // Web Safe? delete needs checking before update because of method name
        if (isset($options['websafe'])) {
            if (in_array('delete', $methods, true)) {
                $this->post($name . '/' . $id . '/delete', $newName . '::delete/$1', $options);
            }
            if (in_array('update', $methods, true)) {
                $this->post($name . '/' . $id, $newName . '::update/$1', $options);
            }
        }

        return $this;
    }

    /**
     * Creates a collections of HTTP-verb based routes for a presenter controller.
     *
     * Possible Options:
     *      'controller'    - Customize the name of the controller used in the 'to' route
     *      'placeholder'   - The regex used by the Router. Defaults to '(:any)'
     *
     * Example:
     *
     *      $route->presenter('photos');
     *
     *      // Generates the following routes:
     *      HTTP Verb | Path        | Action        | Used for...
     *      ----------+-------------+---------------+-----------------
     *      GET         /photos             index           showing all array of photo objects
     *      GET         /photos/show/{id}   show            showing a specific photo object, all properties
     *      GET         /photos/new         new             showing a form for an empty photo object, with default properties
     *      POST        /photos/create      create          processing the form for a new photo
     *      GET         /photos/edit/{id}   edit            show an editing form for a specific photo object, editable properties
     *      POST        /photos/update/{id} update          process the editing form data
     *      GET         /photos/remove/{id} remove          show a form to confirm deletion of a specific photo object
     *      POST        /photos/delete/{id} delete          deleting the specified photo object
     *
     * @param string     $name    The name of the controller to route to.
     * @param array|null $options An list of possible ways to customize the routing.
     */
    public function presenter(string $name, ?array $options = null): RouteCollectionInterface
    {
        // In order to allow customization of the route the
        // resources are sent to, we need to have a new name
        // to store the values in.
        $newName = implode('\\', array_map('ucfirst', explode('/', $name)));
        // If a new controller is specified, then we replace the
        // $name value with the name of the new controller.
        if (isset($options['controller'])) {
            $newName = ucfirst(filter_var($options['controller'], FILTER_SANITIZE_STRING));
        }

        // In order to allow customization of allowed id values
        // we need someplace to store them.
        $id = $options['placeholder'] ?? $this->placeholders[$this->defaultPlaceholder] ?? '(:segment)';

        // Make sure we capture back-references
        $id = '(' . trim($id, '()') . ')';

        $methods = isset($options['only']) ? (is_string($options['only']) ? explode(',', $options['only']) : $options['only']) : ['index', 'show', 'new', 'create', 'edit', 'update', 'remove', 'delete'];

        if (isset($options['except'])) {
            $options['except'] = is_array($options['except']) ? $options['except'] : explode(',', $options['except']);

            foreach ($methods as $i => $method) {
                if (in_array($method, $options['except'], true)) {
                    unset($methods[$i]);
                }
            }
        }

        if (in_array('index', $methods, true)) {
            $this->get($name, $newName . '::index', $options);
        }
        if (in_array('show', $methods, true)) {
            $this->get($name . '/show/' . $id, $newName . '::show/$1', $options);
        }
        if (in_array('new', $methods, true)) {
            $this->get($name . '/new', $newName . '::new', $options);
        }
        if (in_array('create', $methods, true)) {
            $this->post($name . '/create', $newName . '::create', $options);
        }
        if (in_array('edit', $methods, true)) {
            $this->get($name . '/edit/' . $id, $newName . '::edit/$1', $options);
        }
        if (in_array('update', $methods, true)) {
            $this->post($name . '/update/' . $id, $newName . '::update/$1', $options);
        }
        if (in_array('remove', $methods, true)) {
            $this->get($name . '/remove/' . $id, $newName . '::remove/$1', $options);
        }
        if (in_array('delete', $methods, true)) {
            $this->post($name . '/delete/' . $id, $newName . '::delete/$1', $options);
        }
        if (in_array('show', $methods, true)) {
            $this->get($name . '/' . $id, $newName . '::show/$1', $options);
        }
        if (in_array('create', $methods, true)) {
            $this->post($name, $newName . '::create', $options);
        }

        return $this;
    }

    /**
     * Specifies a single route to match for multiple HTTP Verbs.
     *
     * Example:
     *  $route->match( ['get', 'post'], 'users/(:num)', 'users/$1);
     *
     * @param array|string $to
     */
    public function match(array $verbs = [], string $from = '', $to = '', ?array $options = null): RouteCollectionInterface
    {
        if (empty($from) || empty($to)) {
            throw new InvalidArgumentException('You must supply the parameters: from, to.');
        }

        foreach ($verbs as $verb) {
            $verb = strtolower($verb);

            $this->{$verb}($from, $to, $options);
        }

        return $this;
    }

    /**
     * Specifies a route that is only available to GET requests.
     *
     * @param array|string $to
     */
    public function get(string $from, $to, ?array $options = null): RouteCollectionInterface
    {
        $this->create('get', $from, $to, $options);

        return $this;
    }

    /**
     * Specifies a route that is only available to POST requests.
     *
     * @param array|string $to
     */
    public function post(string $from, $to, ?array $options = null): RouteCollectionInterface
    {
        $this->create('post', $from, $to, $options);

        return $this;
    }

    /**
     * Specifies a route that is only available to PUT requests.
     *
     * @param array|string $to
     */
    public function put(string $from, $to, ?array $options = null): RouteCollectionInterface
    {
        $this->create('put', $from, $to, $options);

        return $this;
    }

    /**
     * Specifies a route that is only available to DELETE requests.
     *
     * @param array|string $to
     */
    public function delete(string $from, $to, ?array $options = null): RouteCollectionInterface
    {
        $this->create('delete', $from, $to, $options);

        return $this;
    }

    /**
     * Specifies a route that is only available to HEAD requests.
     *
     * @param array|string $to
     */
    public function head(string $from, $to, ?array $options = null): RouteCollectionInterface
    {
        $this->create('head', $from, $to, $options);

        return $this;
    }

    /**
     * Specifies a route that is only available to PATCH requests.
     *
     * @param array|string $to
     */
    public function patch(string $from, $to, ?array $options = null): RouteCollectionInterface
    {
        $this->create('patch', $from, $to, $options);

        return $this;
    }

    /**
     * Specifies a route that is only available to OPTIONS requests.
     *
     * @param array|string $to
     */
    public function options(string $from, $to, ?array $options = null): RouteCollectionInterface
    {
        $this->create('options', $from, $to, $options);

        return $this;
    }

    /**
     * Specifies a route that is only available to command-line requests.
     *
     * @param array|string $to
     */
    public function cli(string $from, $to, ?array $options = null): RouteCollectionInterface
    {
        $this->create('cli', $from, $to, $options);

        return $this;
    }

    /**
     * Limits the routes to a specified ENVIRONMENT or they won't run.
     */
    public function environment(string $env, Closure $callback): RouteCollectionInterface
    {
        if ($env === ENVIRONMENT) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Attempts to look up a route based on its destination.
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
     * @param mixed ...$params
     *
     * @return false|string
     */
    public function reverseRoute(string $search, ...$params)
    {
        // Named routes get higher priority.
        foreach ($this->routes as $collection) {
            if (array_key_exists($search, $collection)) {
                $route = $this->fillRouteParams(key($collection[$search]['route']), $params);

                return $this->localizeRoute($route);
            }
        }

        // If it's not a named route, then loop over
        // all routes to find a match.
        foreach ($this->routes as $collection) {
            foreach ($collection as $route) {
                $from = key($route['route']);
                $to   = $route['route'][$from];

                // ignore closures
                if (! is_string($to)) {
                    continue;
                }

                // Lose any namespace slash at beginning of strings
                // to ensure more consistent match.
                $to     = ltrim($to, '\\');
                $search = ltrim($search, '\\');

                // If there's any chance of a match, then it will
                // be with $search at the beginning of the $to string.
                if (strpos($to, $search) !== 0) {
                    continue;
                }

                // Ensure that the number of $params given here
                // matches the number of back-references in the route
                if (substr_count($to, '$') !== count($params)) {
                    continue;
                }

                $route = $this->fillRouteParams($from, $params);

                return $this->localizeRoute($route);
            }
        }

        // If we're still here, then we did not find a match.
        return false;
    }

    /**
     * Replaces the {locale} tag with the current application locale
     */
    protected function localizeRoute(string $route): string
    {
        return strtr($route, ['{locale}' => Services::request()->getLocale()]);
    }

    /**
     * Checks a route (using the "from") to see if it's filtered or not.
     */
    public function isFiltered(string $search, ?string $verb = null): bool
    {
        $options = $this->loadRoutesOptions($verb);

        return isset($options[$search]['filter']);
    }

    /**
     * Returns the filter that should be applied for a single route, along
     * with any parameters it might have. Parameters are found by splitting
     * the parameter name on a colon to separate the filter name from the parameter list,
     * and the splitting the result on commas. So:
     *
     *    'role:admin,manager'
     *
     * has a filter of "role", with parameters of ['admin', 'manager'].
     */
    public function getFilterForRoute(string $search, ?string $verb = null): string
    {
        $options = $this->loadRoutesOptions($verb);

        return $options[$search]['filter'] ?? '';
    }

    /**
     * Given a
     *
     * @throws RouterException
     */
    protected function fillRouteParams(string $from, ?array $params = null): string
    {
        // Find all of our back-references in the original route
        preg_match_all('/\(([^)]+)\)/', $from, $matches);

        if (empty($matches[0])) {
            return '/' . ltrim($from, '/');
        }

        // Build our resulting string, inserting the $params in
        // the appropriate places.
        foreach ($matches[0] as $index => $pattern) {
            if (! preg_match('#^' . $pattern . '$#u', $params[$index])) {
                throw RouterException::forInvalidParameterType();
            }

            // Ensure that the param we're inserting matches
            // the expected param type.
            $pos  = strpos($from, $pattern);
            $from = substr_replace($from, $params[$index], $pos, strlen($pattern));
        }

        return '/' . ltrim($from, '/');
    }

    /**
     * Does the heavy lifting of creating an actual route. You must specify
     * the request method(s) that this route will work for. They can be separated
     * by a pipe character "|" if there is more than one.
     *
     * @param array|string $to
     */
    protected function create(string $verb, string $from, $to, ?array $options = null)
    {
        $overwrite = false;
        $prefix    = $this->group === null ? '' : $this->group . '/';

        $from = esc(strip_tags($prefix . $from));

        // While we want to add a route within a group of '/',
        // it doesn't work with matching, so remove them...
        if ($from !== '/') {
            $from = trim($from, '/');
        }

        $options = array_merge($this->currentOptions ?? [], $options ?? []);

        // Route priority detect
        if (isset($options['priority'])) {
            $options['priority'] = abs((int) $options['priority']);

            if ($options['priority'] > 0) {
                $this->prioritizeDetected = true;
            }
        }

        // Hostname limiting?
        if (! empty($options['hostname'])) {
            // @todo determine if there's a way to whitelist hosts?
            if (isset($_SERVER['HTTP_HOST']) && strtolower($_SERVER['HTTP_HOST']) !== strtolower($options['hostname'])) {
                return;
            }

            $overwrite = true;
        }

        // Limiting to subdomains?
        elseif (! empty($options['subdomain'])) {
            // If we don't match the current subdomain, then
            // we don't need to add the route.
            if (! $this->checkSubdomains($options['subdomain'])) {
                return;
            }

            $overwrite = true;
        }

        // Are we offsetting the binds?
        // If so, take care of them here in one
        // fell swoop.
        if (isset($options['offset']) && is_string($to)) {
            // Get a constant string to work with.
            $to = preg_replace('/(\$\d+)/', '$X', $to);

            for ($i = (int) $options['offset'] + 1; $i < (int) $options['offset'] + 7; $i++) {
                $to = preg_replace_callback(
                    '/\$X/',
                    static function ($m) use ($i) {
                        return '$' . $i;
                    },
                    $to,
                    1
                );
            }
        }

        // Replace our regex pattern placeholders with the actual thing
        // so that the Router doesn't need to know about any of this.
        foreach ($this->placeholders as $tag => $pattern) {
            $from = str_ireplace(':' . $tag, $pattern, $from);
        }

        // If is redirect, No processing
        if (! isset($options['redirect']) && is_string($to)) {
            // If no namespace found, add the default namespace
            if (strpos($to, '\\') === false || strpos($to, '\\') > 0) {
                $namespace = $options['namespace'] ?? $this->defaultNamespace;
                $to        = trim($namespace, '\\') . '\\' . $to;
            }
            // Always ensure that we escape our namespace so we're not pointing to
            // \CodeIgniter\Routes\Controller::method.
            $to = '\\' . ltrim($to, '\\');
        }

        $name = $options['as'] ?? $from;

        // Don't overwrite any existing 'froms' so that auto-discovered routes
        // do not overwrite any app/Config/Routes settings. The app
        // routes should always be the "source of truth".
        // this works only because discovered routes are added just prior
        // to attempting to route the request.
        if (isset($this->routes[$verb][$name]) && ! $overwrite) {
            return;
        }

        $this->routes[$verb][$name] = [
            'route' => [$from => $to],
        ];

        $this->routesOptions[$verb][$from] = $options;

        // Is this a redirect?
        if (isset($options['redirect']) && is_numeric($options['redirect'])) {
            $this->routes['*'][$name]['redirect'] = $options['redirect'];
        }
    }

    /**
     * Compares the subdomain(s) passed in against the current subdomain
     * on this page request.
     *
     * @param mixed $subdomains
     */
    private function checkSubdomains($subdomains): bool
    {
        // CLI calls can't be on subdomain.
        if (! isset($_SERVER['HTTP_HOST'])) {
            return false;
        }

        if ($this->currentSubdomain === null) {
            $this->currentSubdomain = $this->determineCurrentSubdomain();
        }

        if (! is_array($subdomains)) {
            $subdomains = [$subdomains];
        }

        // Routes can be limited to any sub-domain. In that case, though,
        // it does require a sub-domain to be present.
        if (! empty($this->currentSubdomain) && in_array('*', $subdomains, true)) {
            return true;
        }

        return in_array($this->currentSubdomain, $subdomains, true);
    }

    /**
     * Examines the HTTP_HOST to get a best match for the subdomain. It
     * won't be perfect, but should work for our needs.
     *
     * It's especially not perfect since it's possible to register a domain
     * with a period (.) as part of the domain name.
     *
     * @return mixed
     */
    private function determineCurrentSubdomain()
    {
        // We have to ensure that a scheme exists
        // on the URL else parse_url will mis-interpret
        // 'host' as the 'path'.
        $url = $_SERVER['HTTP_HOST'];
        if (strpos($url, 'http') !== 0) {
            $url = 'http://' . $url;
        }

        $parsedUrl = parse_url($url);

        $host = explode('.', $parsedUrl['host']);

        if ($host[0] === 'www') {
            unset($host[0]);
        }

        // Get rid of any domains, which will be the last
        unset($host[count($host)]);

        // Account for .co.uk, .co.nz, etc. domains
        if (end($host) === 'co') {
            $host = array_slice($host, 0, -1);
        }

        // If we only have 1 part left, then we don't have a sub-domain.
        if (count($host) === 1) {
            // Set it to false so we don't make it back here again.
            return false;
        }

        return array_shift($host);
    }

    /**
     * Reset the routes, so that a test case can provide the
     * explicit ones needed for it.
     */
    public function resetRoutes()
    {
        $this->routes = ['*' => []];

        foreach ($this->defaultHTTPMethods as $verb) {
            $this->routes[$verb] = [];
        }

        $this->prioritizeDetected = false;
    }

    /**
     * Load routes options based on verb
     */
    protected function loadRoutesOptions(?string $verb = null): array
    {
        $verb = $verb ?: $this->getHTTPVerb();

        $options = $this->routesOptions[$verb] ?? [];

        if (isset($this->routesOptions['*'])) {
            foreach ($this->routesOptions['*'] as $key => $val) {
                if (isset($options[$key])) {
                    $extraOptions  = array_diff_key($val, $options[$key]);
                    $options[$key] = array_merge($options[$key], $extraOptions);
                } else {
                    $options[$key] = $val;
                }
            }
        }

        return $options;
    }

    /**
     * Enable or Disable sorting routes by priority
     *
     * @param bool $enabled The value status
     *
     * @return $this
     */
    public function setPrioritize(bool $enabled = true)
    {
        $this->prioritize = $enabled;

        return $this;
    }
}
