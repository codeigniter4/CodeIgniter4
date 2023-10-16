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
use Config\App;
use Config\Modules;
use Config\Routing;
use Config\Services;
use InvalidArgumentException;

/**
 * @todo Implement nested resource routing (See CakePHP)
 * @see \CodeIgniter\Router\RouteCollectionTest
 */
class RouteCollection implements RouteCollectionInterface
{
    /**
     * The namespace to be added to any Controllers.
     * Defaults to the global namespaces (\).
     *
     * This must have a trailing backslash (\).
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
    protected $autoRoute = false;

    /**
     * A callable that will be shown
     * when the route cannot be matched.
     *
     * @var Closure|string
     */
    protected $override404;

    /**
     * An array of files that would contain route definitions.
     */
    protected array $routeFiles = [];

    /**
     * Defined placeholders that can be used
     * within the
     *
     * @var array<string, string>
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
     *
     * [
     *     verb => [
     *         routeKey(regex) => [
     *             'name'    => routeName
     *             'handler' => handler,
     *             'from'    => from,
     *         ],
     *     ],
     *     // redirect route
     *     '*' => [
     *          routeKey(regex)(from) => [
     *             'name'     => routeName
     *             'handler'  => [routeKey(regex)(to) => handler],
     *             'from'     => from,
     *             'redirect' => statusCode,
     *         ],
     *     ],
     * ]
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
     * Array of routes names
     *
     * @var array
     *
     * [
     *     verb => [
     *         routeName => routeKey(regex)
     *     ],
     * ]
     */
    protected $routesNames = [
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
     *
     * [
     *     verb => [
     *         routeKey(regex) => [
     *             key => value,
     *         ]
     *     ],
     * ]
     */
    protected $routesOptions = [];

    /**
     * The current method that the script is being called by.
     *
     * @var string HTTP verb (lower case) like `get`,`post` or `*`
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
     * The current hostname from $_SERVER['HTTP_HOST']
     */
    private ?string $httpHost = null;

    /**
     * Flag to limit or not the routes with {locale} placeholder to App::$supportedLocales
     */
    protected bool $useSupportedLocalesOnly = false;

    /**
     * Constructor
     */
    public function __construct(FileLocator $locator, Modules $moduleConfig, Routing $routing)
    {
        $this->fileLocator  = $locator;
        $this->moduleConfig = $moduleConfig;

        $this->httpHost = Services::request()->getServer('HTTP_HOST');

        // Setup based on config file. Let routes file override.
        $this->defaultNamespace   = rtrim($routing->defaultNamespace, '\\') . '\\';
        $this->defaultController  = $routing->defaultController;
        $this->defaultMethod      = $routing->defaultMethod;
        $this->translateURIDashes = $routing->translateURIDashes;
        $this->override404        = $routing->override404;
        $this->autoRoute          = $routing->autoRoute;
        $this->routeFiles         = $routing->routeFiles;
        $this->prioritize         = $routing->prioritize;

        // Normalize the path string in routeFiles array.
        foreach ($this->routeFiles as $routeKey => $routesFile) {
            $realpath                    = realpath($routesFile);
            $this->routeFiles[$routeKey] = ($realpath === false) ? $routesFile : $realpath;
        }
    }

    /**
     * Loads main routes file and discover routes.
     *
     * Loads only once unless reset.
     *
     * @return $this
     */
    public function loadRoutes(string $routesFile = APPPATH . 'Config/Routes.php')
    {
        if ($this->didDiscover) {
            return $this;
        }

        // Normalize the path string in routesFile
        $realpath   = realpath($routesFile);
        $routesFile = ($realpath === false) ? $routesFile : $realpath;

        // Include the passed in routesFile if it doesn't exist.
        // Only keeping that around for BC purposes for now.
        $routeFiles = $this->routeFiles;
        if (! in_array($routesFile, $routeFiles, true)) {
            $routeFiles[] = $routesFile;
        }

        // We need this var in local scope
        // so route files can access it.
        $routes = $this;

        foreach ($routeFiles as $routesFile) {
            if (! is_file($routesFile)) {
                log_message('warning', sprintf('Routes file not found: "%s"', $routesFile));

                continue;
            }

            require $routesFile;
        }

        $this->discoverRoutes();

        return $this;
    }

    /**
     * Will attempt to discover any additional routes, either through
     * the local PSR4 namespaces, or through selected Composer packages.
     *
     * @return void
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
                if (in_array($file, $this->routeFiles, true)) {
                    continue;
                }

                include $file;
            }
        }

        $this->didDiscover = true;
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
     * For `spark routes`
     *
     * @return array<string, string>
     *
     * @internal
     */
    public function getPlaceholders(): array
    {
        return $this->placeholders;
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
     * @param callable|string|null $callable
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
     *
     * @param bool $includeWildcard Whether to include '*' routes.
     */
    public function getRoutes(?string $verb = null, bool $includeWildcard = true): array
    {
        if (empty($verb)) {
            $verb = $this->getHTTPVerb();
        }

        // Since this is the entry point for the Router,
        // take a moment to do any route discovery
        // we might need to do.
        $this->discoverRoutes();

        $routes = [];

        if (isset($this->routes[$verb])) {
            // Keep current verb's routes at the beginning, so they're matched
            // before any of the generic, "add" routes.
            $collection = $includeWildcard ? $this->routes[$verb] + ($this->routes['*'] ?? []) : $this->routes[$verb];

            foreach ($collection as $routeKey => $r) {
                $routes[$routeKey] = $r['handler'];
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
     *
     * @return array<string, int|string> [key => value]
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
     * @param string $verb HTTP verb
     *
     * @return $this
     */
    public function setHTTPVerb(string $verb)
    {
        $this->HTTPVerb = strtolower($verb);

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
     * @param array|Closure|string $to
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
        if (array_key_exists($to, $this->routesNames['*'])) {
            $routeName  = $to;
            $routeKey   = $this->routesNames['*'][$routeName];
            $redirectTo = [$routeKey => $this->routes['*'][$routeKey]['handler']];
        } elseif (array_key_exists($to, $this->routesNames['get'])) {
            $routeName  = $to;
            $routeKey   = $this->routesNames['get'][$routeName];
            $redirectTo = [$routeKey => $this->routes['get'][$routeKey]['handler']];
        } else {
            // The named route is not found.
            $redirectTo = $to;
        }

        $this->create('*', $from, $redirectTo, ['redirect' => $status]);

        return $this;
    }

    /**
     * Determines if the route is a redirecting route.
     *
     * @param string $routeKey routeKey or route name
     */
    public function isRedirect(string $routeKey): bool
    {
        if (isset($this->routes['*'][$routeKey]['redirect'])) {
            return true;
        }

        // This logic is not used. Should be deprecated?
        $routeName = $this->routes['*'][$routeKey]['name'] ?? null;
        if ($routeName === $routeKey) {
            $routeKey = $this->routesNames['*'][$routeName];

            return isset($this->routes['*'][$routeKey]['redirect']);
        }

        return false;
    }

    /**
     * Grabs the HTTP status code from a redirecting Route.
     *
     * @param string $routeKey routeKey or route name
     */
    public function getRedirectCode(string $routeKey): int
    {
        if (isset($this->routes['*'][$routeKey]['redirect'])) {
            return $this->routes['*'][$routeKey]['redirect'];
        }

        // This logic is not used. Should be deprecated?
        $routeName = $this->routes['*'][$routeKey]['name'] ?? null;
        if ($routeName === $routeKey) {
            $routeKey = $this->routesNames['*'][$routeName];

            return $this->routes['*'][$routeKey]['redirect'];
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
     *
     * @return void
     */
    public function group(string $name, ...$params)
    {
        $oldGroup   = $this->group;
        $oldOptions = $this->currentOptions;

        // To register a route, we'll set a flag so that our router
        // will see the group name.
        // If the group name is empty, we go on using the previously built group name.
        $this->group = $name ? trim($oldGroup . '/' . $name, '/') : $oldGroup;

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
     * @param array|null $options A list of possible ways to customize the routing.
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
            $newName = ucfirst(esc(strip_tags($options['controller'])));
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
     * @param array|null $options A list of possible ways to customize the routing.
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
            $newName = ucfirst(esc(strip_tags($options['controller'])));
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
     * @param array|Closure|string $to
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
     * @param array|Closure|string $to
     */
    public function get(string $from, $to, ?array $options = null): RouteCollectionInterface
    {
        $this->create('get', $from, $to, $options);

        return $this;
    }

    /**
     * Specifies a route that is only available to POST requests.
     *
     * @param array|Closure|string $to
     */
    public function post(string $from, $to, ?array $options = null): RouteCollectionInterface
    {
        $this->create('post', $from, $to, $options);

        return $this;
    }

    /**
     * Specifies a route that is only available to PUT requests.
     *
     * @param array|Closure|string $to
     */
    public function put(string $from, $to, ?array $options = null): RouteCollectionInterface
    {
        $this->create('put', $from, $to, $options);

        return $this;
    }

    /**
     * Specifies a route that is only available to DELETE requests.
     *
     * @param array|Closure|string $to
     */
    public function delete(string $from, $to, ?array $options = null): RouteCollectionInterface
    {
        $this->create('delete', $from, $to, $options);

        return $this;
    }

    /**
     * Specifies a route that is only available to HEAD requests.
     *
     * @param array|Closure|string $to
     */
    public function head(string $from, $to, ?array $options = null): RouteCollectionInterface
    {
        $this->create('head', $from, $to, $options);

        return $this;
    }

    /**
     * Specifies a route that is only available to PATCH requests.
     *
     * @param array|Closure|string $to
     */
    public function patch(string $from, $to, ?array $options = null): RouteCollectionInterface
    {
        $this->create('patch', $from, $to, $options);

        return $this;
    }

    /**
     * Specifies a route that is only available to OPTIONS requests.
     *
     * @param array|Closure|string $to
     */
    public function options(string $from, $to, ?array $options = null): RouteCollectionInterface
    {
        $this->create('options', $from, $to, $options);

        return $this;
    }

    /**
     * Specifies a route that is only available to command-line requests.
     *
     * @param array|Closure|string $to
     */
    public function cli(string $from, $to, ?array $options = null): RouteCollectionInterface
    {
        $this->create('cli', $from, $to, $options);

        return $this;
    }

    /**
     * Specifies a route that will only display a view.
     * Only works for GET requests.
     */
    public function view(string $from, string $view, ?array $options = null): RouteCollectionInterface
    {
        $to = static fn (...$data) => Services::renderer()
            ->setData(['segments' => $data], 'raw')
            ->render($view, $options);

        $routeOptions = $options ?? [];
        $routeOptions = array_merge($routeOptions, ['view' => $view]);

        $this->create('get', $from, $to, $routeOptions);

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
     * @param string     $search    Route name or Controller::method
     * @param int|string ...$params One or more parameters to be passed to the route.
     *                              The last parameter allows you to set the locale.
     *
     * @return false|string The route (URI path relative to baseURL) or false if not found.
     */
    public function reverseRoute(string $search, ...$params)
    {
        if ($search === '') {
            return false;
        }

        // Named routes get higher priority.
        foreach ($this->routesNames as $verb => $collection) {
            if (array_key_exists($search, $collection)) {
                $routeKey = $collection[$search];

                $from = $this->routes[$verb][$routeKey]['from'];

                return $this->buildReverseRoute($from, $params);
            }
        }

        // Add the default namespace if needed.
        $namespace = trim($this->defaultNamespace, '\\') . '\\';
        if (
            substr($search, 0, 1) !== '\\'
            && substr($search, 0, strlen($namespace)) !== $namespace
        ) {
            $search = $namespace . $search;
        }

        // If it's not a named route, then loop over
        // all routes to find a match.
        foreach ($this->routes as $collection) {
            foreach ($collection as $route) {
                $to   = $route['handler'];
                $from = $route['from'];

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

                return $this->buildReverseRoute($from, $params);
            }
        }

        // If we're still here, then we did not find a match.
        return false;
    }

    /**
     * Replaces the {locale} tag with the current application locale
     *
     * @deprecated Unused.
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
     *
     * @deprecated Use getFiltersForRoute()
     */
    public function getFilterForRoute(string $search, ?string $verb = null): string
    {
        $options = $this->loadRoutesOptions($verb);

        return $options[$search]['filter'] ?? '';
    }

    /**
     * Returns the filters that should be applied for a single route, along
     * with any parameters it might have. Parameters are found by splitting
     * the parameter name on a colon to separate the filter name from the parameter list,
     * and the splitting the result on commas. So:
     *
     *    'role:admin,manager'
     *
     * has a filter of "role", with parameters of ['admin', 'manager'].
     *
     * @param string $search routeKey
     *
     * @return array<int, string> filter_name or filter_name:arguments like 'role:admin,manager'
     * @phpstan-return list<string>
     */
    public function getFiltersForRoute(string $search, ?string $verb = null): array
    {
        $options = $this->loadRoutesOptions($verb);

        if (! array_key_exists($search, $options) || ! array_key_exists('filter', $options[$search])) {
            return [];
        }

        if (is_string($options[$search]['filter'])) {
            return [$options[$search]['filter']];
        }

        return $options[$search]['filter'];
    }

    /**
     * Given a
     *
     * @throws RouterException
     *
     * @deprecated Unused. Now uses buildReverseRoute().
     */
    protected function fillRouteParams(string $from, ?array $params = null): string
    {
        // Find all of our back-references in the original route
        preg_match_all('/\(([^)]+)\)/', $from, $matches);

        if (empty($matches[0])) {
            return '/' . ltrim($from, '/');
        }

        /**
         * Build our resulting string, inserting the $params in
         * the appropriate places.
         *
         * @var array<int, string> $patterns
         * @phpstan-var list<string> $patterns
         */
        $patterns = $matches[0];

        foreach ($patterns as $index => $pattern) {
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
     * Builds reverse route
     *
     * @param array $params One or more parameters to be passed to the route.
     *                      The last parameter allows you to set the locale.
     */
    protected function buildReverseRoute(string $from, array $params): string
    {
        $locale = null;

        // Find all of our back-references in the original route
        preg_match_all('/\(([^)]+)\)/', $from, $matches);

        if (empty($matches[0])) {
            if (strpos($from, '{locale}') !== false) {
                $locale = $params[0] ?? null;
            }

            $from = $this->replaceLocale($from, $locale);

            return '/' . ltrim($from, '/');
        }

        // Locale is passed?
        $placeholderCount = count($matches[0]);
        if (count($params) > $placeholderCount) {
            $locale = $params[$placeholderCount];
        }

        /**
         * Build our resulting string, inserting the $params in
         * the appropriate places.
         *
         * @var array<int, string> $placeholders
         * @phpstan-var list<string> $placeholders
         */
        $placeholders = $matches[0];

        foreach ($placeholders as $index => $placeholder) {
            if (! isset($params[$index])) {
                throw new InvalidArgumentException(
                    'Missing argument for "' . $placeholder . '" in route "' . $from . '".'
                );
            }

            // Remove `(:` and `)` when $placeholder is a placeholder.
            $placeholderName = substr($placeholder, 2, -1);
            // or maybe $placeholder is not a placeholder, but a regex.
            $pattern = $this->placeholders[$placeholderName] ?? $placeholder;

            if (! preg_match('#^' . $pattern . '$#u', $params[$index])) {
                throw RouterException::forInvalidParameterType();
            }

            // Ensure that the param we're inserting matches
            // the expected param type.
            $pos  = strpos($from, $placeholder);
            $from = substr_replace($from, $params[$index], $pos, strlen($placeholder));
        }

        $from = $this->replaceLocale($from, $locale);

        return '/' . ltrim($from, '/');
    }

    /**
     * Replaces the {locale} tag with the locale
     */
    private function replaceLocale(string $route, ?string $locale = null): string
    {
        if (strpos($route, '{locale}') === false) {
            return $route;
        }

        // Check invalid locale
        if ($locale !== null) {
            $config = config(App::class);
            if (! in_array($locale, $config->supportedLocales, true)) {
                $locale = null;
            }
        }

        if ($locale === null) {
            $locale = Services::request()->getLocale();
        }

        return strtr($route, ['{locale}' => $locale]);
    }

    /**
     * Does the heavy lifting of creating an actual route. You must specify
     * the request method(s) that this route will work for. They can be separated
     * by a pipe character "|" if there is more than one.
     *
     * @param array|Closure|string $to
     *
     * @return void
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

        // When redirecting to named route, $to is an array like `['zombies' => '\Zombies::index']`.
        if (is_array($to) && isset($to[0])) {
            $to = $this->processArrayCallableSyntax($from, $to);
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
            if (! $this->checkHostname($options['hostname'])) {
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
                    static fn ($m) => '$' . $i,
                    $to,
                    1
                );
            }
        }

        $routeKey = $from;

        // Replace our regex pattern placeholders with the actual thing
        // so that the Router doesn't need to know about any of this.
        foreach ($this->placeholders as $tag => $pattern) {
            $routeKey = str_ireplace(':' . $tag, $pattern, $routeKey);
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

        $name = $options['as'] ?? $routeKey;

        helper('array');

        // Don't overwrite any existing 'froms' so that auto-discovered routes
        // do not overwrite any app/Config/Routes settings. The app
        // routes should always be the "source of truth".
        // this works only because discovered routes are added just prior
        // to attempting to route the request.
        $routeKeyExists = isset($this->routes[$verb][$routeKey]);
        if ((isset($this->routesNames[$verb][$name]) || $routeKeyExists) && ! $overwrite) {
            return;
        }

        $this->routes[$verb][$routeKey] = [
            'name'    => $name,
            'handler' => $to,
            'from'    => $from,
        ];
        $this->routesOptions[$verb][$routeKey] = $options;
        $this->routesNames[$verb][$name]       = $routeKey;

        // Is this a redirect?
        if (isset($options['redirect']) && is_numeric($options['redirect'])) {
            $this->routes['*'][$routeKey]['redirect'] = $options['redirect'];
        }
    }

    /**
     * Compares the hostname passed in against the current hostname
     * on this page request.
     *
     * @param string $hostname Hostname in route options
     */
    private function checkHostname($hostname): bool
    {
        // CLI calls can't be on hostname.
        if (! isset($this->httpHost)) {
            return false;
        }

        return strtolower($this->httpHost) === strtolower($hostname);
    }

    private function processArrayCallableSyntax(string $from, array $to): string
    {
        // [classname, method]
        // eg, [Home::class, 'index']
        if (is_callable($to, true, $callableName)) {
            // If the route has placeholders, add params automatically.
            $params = $this->getMethodParams($from);

            return '\\' . $callableName . $params;
        }

        // [[classname, method], params]
        // eg, [[Home::class, 'index'], '$1/$2']
        if (
            isset($to[0], $to[1])
            && is_callable($to[0], true, $callableName)
            && is_string($to[1])
        ) {
            $to = '\\' . $callableName . '/' . $to[1];
        }

        return $to;
    }

    /**
     * Returns the method param string like `/$1/$2` for placeholders
     */
    private function getMethodParams(string $from): string
    {
        preg_match_all('/\(.+?\)/', $from, $matches);
        $count = is_countable($matches[0]) ? count($matches[0]) : 0;

        $params = '';

        for ($i = 1; $i <= $count; $i++) {
            $params .= '/$' . $i;
        }

        return $params;
    }

    /**
     * Compares the subdomain(s) passed in against the current subdomain
     * on this page request.
     *
     * @param string|string[] $subdomains
     */
    private function checkSubdomains($subdomains): bool
    {
        // CLI calls can't be on subdomain.
        if (! isset($this->httpHost)) {
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
     * Examines the HTTP_HOST to get the best match for the subdomain. It
     * won't be perfect, but should work for our needs.
     *
     * It's especially not perfect since it's possible to register a domain
     * with a period (.) as part of the domain name.
     *
     * @return false|string the subdomain
     */
    private function determineCurrentSubdomain()
    {
        // We have to ensure that a scheme exists
        // on the URL else parse_url will mis-interpret
        // 'host' as the 'path'.
        $url = $this->httpHost;
        if (strpos($url, 'http') !== 0) {
            $url = 'http://' . $url;
        }

        $parsedUrl = parse_url($url);

        $host = explode('.', $parsedUrl['host']);

        if ($host[0] === 'www') {
            unset($host[0]);
        }

        // Get rid of any domains, which will be the last
        unset($host[count($host) - 1]);

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
     *
     * @return void
     */
    public function resetRoutes()
    {
        $this->routes = $this->routesNames = ['*' => []];

        foreach ($this->defaultHTTPMethods as $verb) {
            $this->routes[$verb]      = [];
            $this->routesNames[$verb] = [];
        }

        $this->routesOptions = [];

        $this->prioritizeDetected = false;
        $this->didDiscover        = false;
    }

    /**
     * Load routes options based on verb
     *
     * @return array<string, array<string, array|int|string>> [routeKey(or from) => [key => value]]
     * @phpstan-return array<
     *     string,
     *     array{
     *         filter?: string|list<string>, namespace?: string, hostname?: string,
     *         subdomain?: string, offset?: int, priority?: int, as?: string,
     *         redirect?: int
     *     }
     * >
     */
    protected function loadRoutesOptions(?string $verb = null): array
    {
        $verb ??= $this->getHTTPVerb();

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

    /**
     * Get all controllers in Route Handlers
     *
     * @param string|null $verb HTTP verb. `'*'` returns all controllers in any verb.
     *
     * @return array<int, string> controller name list
     * @phpstan-return list<string>
     */
    public function getRegisteredControllers(?string $verb = '*'): array
    {
        $controllers = [];

        if ($verb === '*') {
            foreach ($this->defaultHTTPMethods as $tmpVerb) {
                foreach ($this->routes[$tmpVerb] as $route) {
                    $controller = $this->getControllerName($route['handler']);
                    if ($controller !== null) {
                        $controllers[] = $controller;
                    }
                }
            }
        } else {
            $routes = $this->getRoutes($verb);

            foreach ($routes as $handler) {
                $controller = $this->getControllerName($handler);
                if ($controller !== null) {
                    $controllers[] = $controller;
                }
            }
        }

        return array_unique($controllers);
    }

    /**
     * @param Closure|string $handler Handler
     *
     * @return string|null Controller classname
     */
    private function getControllerName($handler)
    {
        if (! is_string($handler)) {
            return null;
        }

        [$controller] = explode('::', $handler, 2);

        return $controller;
    }

    /**
     * Set The flag that limit or not the routes with {locale} placeholder to App::$supportedLocales
     */
    public function useSupportedLocalesOnly(bool $useOnly): self
    {
        $this->useSupportedLocalesOnly = $useOnly;

        return $this;
    }

    /**
     * Get the flag that limit or not the routes with {locale} placeholder to App::$supportedLocales
     */
    public function shouldUseSupportedLocalesOnly(): bool
    {
        return $this->useSupportedLocalesOnly;
    }
}
