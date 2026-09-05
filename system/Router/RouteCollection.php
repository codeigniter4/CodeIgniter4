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

namespace CodeIgniter\Router;

use Closure;
use CodeIgniter\Autoloader\FileLocatorInterface;
use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\HTTP\Method;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Router\Exceptions\RouterException;
use Config\App;
use Config\Modules;
use Config\Routing;

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
     * @var (Closure(string): (ResponseInterface|string|void))|string
     */
    protected $override404;

    /**
     * An array of files that would contain route definitions.
     *
     * @var list<string>
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
     *```
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
     * ```
     *
     * @var array<string, array<string, array<string, mixed>>>
     */
    protected $routes = [
        '*'             => [],
        Method::OPTIONS => [],
        Method::GET     => [],
        Method::HEAD    => [],
        Method::QUERY   => [],
        Method::POST    => [],
        Method::PATCH   => [],
        Method::PUT     => [],
        Method::DELETE  => [],
        Method::TRACE   => [],
        Method::CONNECT => [],
        'CLI'           => [],
    ];

    /**
     * Array of routes names
     * ```
     * [verb => [routeName => routeKey(regex)]]
     * ```
     *
     * @var array<string, array<string, string>>
     */
    protected $routesNames = [
        '*'             => [],
        Method::OPTIONS => [],
        Method::GET     => [],
        Method::HEAD    => [],
        Method::QUERY   => [],
        Method::POST    => [],
        Method::PATCH   => [],
        Method::PUT     => [],
        Method::DELETE  => [],
        Method::TRACE   => [],
        Method::CONNECT => [],
        'CLI'           => [],
    ];

    /**
     * Array of routes options
     * ```
     * [
     *     verb => [
     *         routeKey(regex) => [
     *             key => value,
     *         ]
     *     ],
     * ]
     * ```
     *
     * @var array<string, array<string, array<string, mixed>>>
     */
    protected $routesOptions = [];

    /**
     * The current method that the script is being called by.
     *
     * @var string HTTP verb like `GET`,`POST` or `*` or `CLI`
     */
    protected $HTTPVerb = '*';

    /**
     * The default list of HTTP methods (and CLI for command line usage)
     * that is allowed if no other method is provided.
     *
     * @var list<string>
     */
    public $defaultHTTPMethods = Router::HTTP_METHODS;

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
     * @var array<string, mixed>|null
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
     * @var FileLocatorInterface
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
    public function __construct(FileLocatorInterface $locator, Modules $moduleConfig, Routing $routing)
    {
        $this->fileLocator  = $locator;
        $this->moduleConfig = $moduleConfig;

        $this->httpHost = service('request')->getServer('HTTP_HOST');

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

    public function setDefaultNamespace(string $value): RouteCollectionInterface
    {
        $this->defaultNamespace = esc(strip_tags($value));
        $this->defaultNamespace = rtrim($this->defaultNamespace, '\\') . '\\';

        return $this;
    }

    public function setDefaultController(string $value): RouteCollectionInterface
    {
        $this->defaultController = esc(strip_tags($value));

        return $this;
    }

    public function setDefaultMethod(string $value): RouteCollectionInterface
    {
        $this->defaultMethod = esc(strip_tags($value));

        return $this;
    }

    public function setTranslateURIDashes(bool $value): RouteCollectionInterface
    {
        $this->translateURIDashes = $value;

        return $this;
    }

    public function setAutoRoute(bool $value): RouteCollectionInterface
    {
        $this->autoRoute = $value;

        return $this;
    }

    public function set404Override($callable = null): RouteCollectionInterface
    {
        $this->override404 = $callable;

        return $this;
    }

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

    public function getDefaultController(): string
    {
        return $this->defaultController;
    }

    public function getDefaultMethod(): string
    {
        return $this->defaultMethod;
    }

    public function getDefaultNamespace(): string
    {
        return $this->defaultNamespace;
    }

    public function shouldTranslateURIDashes(): bool
    {
        return $this->translateURIDashes;
    }

    public function shouldAutoRoute(): bool
    {
        return $this->autoRoute;
    }

    public function getRoutes(?string $verb = null, bool $includeWildcard = true): array
    {
        if ((string) $verb === '') {
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

    public function getRoutesOptions(?string $from = null, ?string $verb = null): array
    {
        $options = $this->loadRoutesOptions($verb);

        return ((string) $from !== '') ? $options[$from] ?? [] : $options;
    }

    public function getHTTPVerb(): string
    {
        return $this->HTTPVerb;
    }

    public function setHTTPVerb(string $verb)
    {
        if ($verb !== '*' && $verb === strtolower($verb)) {
            @trigger_error(
                'Passing lowercase HTTP method "' . $verb . '" is deprecated.'
                . ' Use uppercase HTTP method like "' . strtoupper($verb) . '".',
                E_USER_DEPRECATED,
            );
        }

        /**
         * @deprecated 4.5.0
         * @TODO Remove strtoupper() in the future.
         */
        $this->HTTPVerb = strtoupper($verb);

        return $this;
    }

    /**
     * A shortcut method to add a number of routes at a single time.
     * It does not allow any options to be set on the route, or to
     * define the method used.
     *
     * @param array<string, mixed>      $routes
     * @param array<string, mixed>|null $options
     */
    public function map(array $routes = [], ?array $options = null): RouteCollectionInterface
    {
        foreach ($routes as $from => $to) {
            $this->add($from, $to, $options);
        }

        return $this;
    }

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
        } elseif (array_key_exists($to, $this->routesNames[Method::GET])) {
            $routeName  = $to;
            $routeKey   = $this->routesNames[Method::GET][$routeName];
            $redirectTo = [$routeKey => $this->routes[Method::GET][$routeKey]['handler']];
        } else {
            // The named route is not found.
            $redirectTo = $to;
        }

        $this->create('*', $from, $redirectTo, ['redirect' => $status]);

        return $this;
    }

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
     * @param string                                      $name      The name to group/prefix the routes with.
     * @param array<string, mixed>|(callable(self): void) ...$params
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
        $this->group = $name !== '' ? trim($oldGroup . '/' . $name, '/') : $oldGroup;

        $callback = array_pop($params);

        if ($params !== [] && is_array($params[0])) {
            $options = array_shift($params);

            if (isset($options['filter'])) {
                // Merge filters.
                $currentFilter     = (array) ($this->currentOptions['filter'] ?? []);
                $options['filter'] = array_merge($currentFilter, (array) $options['filter']);
            }

            // Merge options other than filters.
            $this->currentOptions = array_merge(
                $this->currentOptions ?? [],
                $options,
            );
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
     * @param string                    $name    The name of the resource/controller to route to.
     * @param array<string, mixed>|null $options A list of possible ways to customize the routing.
     */
    public function resource(string $name, ?array $options = null): RouteCollectionInterface
    {
        // In order to allow customization of the route the
        // resources are sent to, we need to have a new name
        // to store the values in.
        $newName = implode('\\', array_map(ucfirst(...), explode('/', $name)));

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
     * @param string                    $name    The name of the controller to route to.
     * @param array<string, mixed>|null $options A list of possible ways to customize the routing.
     */
    public function presenter(string $name, ?array $options = null): RouteCollectionInterface
    {
        // In order to allow customization of the route the
        // resources are sent to, we need to have a new name
        // to store the values in.
        $newName = implode('\\', array_map(ucfirst(...), explode('/', $name)));

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
     *  $route->match( ['GET', 'POST'], 'users/(:num)', 'users/$1);
     *
     * @param list<string>                           $verbs
     * @param array<array-key, mixed>|Closure|string $to
     * @param array<string, mixed>|null              $options
     */
    public function match(array $verbs = [], string $from = '', $to = '', ?array $options = null): RouteCollectionInterface
    {
        if ($from === '' || in_array($to, ['', []], true)) {
            throw new InvalidArgumentException('You must supply the parameters: from, to.');
        }

        foreach ($verbs as $verb) {
            if ($verb === strtolower($verb)) {
                @trigger_error(
                    'Passing lowercase HTTP method "' . $verb . '" is deprecated.'
                    . ' Use uppercase HTTP method like "' . strtoupper($verb) . '".',
                    E_USER_DEPRECATED,
                );
            }

            /**
             * @TODO We should use correct uppercase verb.
             * @deprecated 4.5.0
             */
            $verb = strtolower($verb);

            $this->{$verb}($from, $to, $options);
        }

        return $this;
    }

    /**
     * Specifies a route that is only available to GET requests.
     *
     * @param array<array-key, mixed>|Closure|string $to
     * @param array<string, mixed>|null              $options
     */
    public function get(string $from, $to, ?array $options = null): RouteCollectionInterface
    {
        $this->create(Method::GET, $from, $to, $options);

        return $this;
    }

    /**
     * Specifies a route that is only available to POST requests.
     *
     * @param array<array-key, mixed>|Closure|string $to
     * @param array<string, mixed>|null              $options
     */
    public function post(string $from, $to, ?array $options = null): RouteCollectionInterface
    {
        $this->create(Method::POST, $from, $to, $options);

        return $this;
    }

    /**
     * Specifies a route that is only available to PUT requests.
     *
     * @param array<array-key, mixed>|Closure|string $to
     * @param array<string, mixed>|null              $options
     */
    public function put(string $from, $to, ?array $options = null): RouteCollectionInterface
    {
        $this->create(Method::PUT, $from, $to, $options);

        return $this;
    }

    /**
     * Specifies a route that is only available to DELETE requests.
     *
     * @param array<array-key, mixed>|Closure|string $to
     * @param array<string, mixed>|null              $options
     */
    public function delete(string $from, $to, ?array $options = null): RouteCollectionInterface
    {
        $this->create(Method::DELETE, $from, $to, $options);

        return $this;
    }

    /**
     * Specifies a route that is only available to HEAD requests.
     *
     * @param array<array-key, mixed>|Closure|string $to
     * @param array<string, mixed>|null              $options
     */
    public function head(string $from, $to, ?array $options = null): RouteCollectionInterface
    {
        $this->create(Method::HEAD, $from, $to, $options);

        return $this;
    }

    /**
     * Specifies a route that is only available to QUERY requests.
     *
     * @param array<string, mixed>|(Closure(mixed...): (ResponseInterface|string|void))|string $to
     * @param array<string, mixed>|null                                                        $options
     */
    public function query(string $from, $to, ?array $options = null): RouteCollectionInterface
    {
        $this->create(Method::QUERY, $from, $to, $options);

        return $this;
    }

    /**
     * Specifies a route that is only available to PATCH requests.
     *
     * @param array<array-key, mixed>|Closure|string $to
     * @param array<string, mixed>|null              $options
     */
    public function patch(string $from, $to, ?array $options = null): RouteCollectionInterface
    {
        $this->create(Method::PATCH, $from, $to, $options);

        return $this;
    }

    /**
     * Specifies a route that is only available to OPTIONS requests.
     *
     * @param array<array-key, mixed>|Closure|string $to
     * @param array<string, mixed>|null              $options
     */
    public function options(string $from, $to, ?array $options = null): RouteCollectionInterface
    {
        $this->create(Method::OPTIONS, $from, $to, $options);

        return $this;
    }

    /**
     * Specifies a route that is only available to command-line requests.
     *
     * @param array<array-key, mixed>|Closure|string $to
     * @param array<string, mixed>|null              $options
     */
    public function cli(string $from, $to, ?array $options = null): RouteCollectionInterface
    {
        $this->create('CLI', $from, $to, $options);

        return $this;
    }

    /**
     * Specifies a route that will only display a view.
     * Only works for GET requests.
     *
     * @param array<string, mixed>|null $options
     */
    public function view(string $from, string $view, ?array $options = null): RouteCollectionInterface
    {
        $to = static fn (...$data) => service('renderer')
            ->setData(['segments' => $data], 'raw')
            ->render($view, $options);

        $routeOptions = $options ?? [];
        $routeOptions = array_merge($routeOptions, ['view' => $view]);

        $this->create(Method::GET, $from, $to, $routeOptions);

        return $this;
    }

    /**
     * Limits the routes to a specified ENVIRONMENT or they won't run.
     *
     * @param Closure(RouteCollection): void $callback
     */
    public function environment(string $env, Closure $callback): RouteCollectionInterface
    {
        if (service('environment')->is($env)) {
            $callback($this);
        }

        return $this;
    }

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
            ! str_starts_with($search, '\\')
            && ! str_starts_with($search, $namespace)
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
                if (! str_starts_with($to, $search)) {
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
     * @deprecated 4.3.0 Unused.
     */
    protected function localizeRoute(string $route): string
    {
        return strtr($route, ['{locale}' => service('request')->getLocale()]);
    }

    public function isFiltered(string $search, ?string $verb = null): bool
    {
        $options = $this->loadRoutesOptions($verb);

        return isset($options[$search]['filter']);
    }

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
     * @param list<int|string>|null $params
     *
     * @throws RouterException
     *
     * @deprecated 4.3.0 Unused. Now uses buildReverseRoute().
     */
    protected function fillRouteParams(string $from, ?array $params = null): string
    {
        // Find all of our back-references in the original route
        preg_match_all('/\(([^)]+)\)/', $from, $matches);

        if ($matches[0] === []) {
            return '/' . ltrim($from, '/');
        }

        /**
         * Build our resulting string, inserting the $params in
         * the appropriate places.
         *
         * @var list<string> $patterns
         */
        $patterns = $matches[0];

        foreach ($patterns as $index => $pattern) {
            if (preg_match('#^' . $pattern . '$#u', $params[$index]) !== 1) {
                throw RouterException::forInvalidParameterType();
            }

            // Ensure that the param we're inserting matches
            // the expected param type.
            $pos  = strpos($from, (string) $pattern);
            $from = substr_replace($from, $params[$index], $pos, strlen($pattern));
        }

        return '/' . ltrim($from, '/');
    }

    /**
     * Builds reverse route
     *
     * @param list<int|string> $params One or more parameters to be passed to the route.
     *                                 The last parameter allows you to set the locale.
     */
    protected function buildReverseRoute(string $from, array $params): string
    {
        $locale = null;

        // Find all of our back-references in the original route
        preg_match_all('/\(([^)]+)\)/', $from, $matches);

        if ($matches[0] === []) {
            if (str_contains($from, '{locale}')) {
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
         * @var list<string> $placeholders
         */
        $placeholders = $matches[0];

        foreach ($placeholders as $index => $placeholder) {
            if (! isset($params[$index])) {
                throw new InvalidArgumentException(
                    'Missing argument for "' . $placeholder . '" in route "' . $from . '".',
                );
            }

            // Remove `(:` and `)` when $placeholder is a placeholder.
            $placeholderName = substr($placeholder, 2, -1);
            // or maybe $placeholder is not a placeholder, but a regex.
            $pattern = $this->placeholders[$placeholderName] ?? $placeholder;

            if (preg_match('#^' . $pattern . '$#u', (string) $params[$index]) !== 1) {
                throw RouterException::forInvalidParameterType();
            }

            // Ensure that the param we're inserting matches
            // the expected param type.
            $pos  = strpos($from, (string) $placeholder);
            $from = substr_replace($from, (string) $params[$index], $pos, strlen($placeholder));
        }

        $from = $this->replaceLocale($from, $locale);

        return '/' . ltrim($from, '/');
    }

    /**
     * Replaces the {locale} tag with the locale
     */
    private function replaceLocale(string $route, ?string $locale = null): string
    {
        if (! str_contains($route, '{locale}')) {
            return $route;
        }

        // Check invalid locale
        if ((string) $locale !== '') {
            $config = config(App::class);
            if (! in_array($locale, $config->supportedLocales, true)) {
                $locale = null;
            }
        }

        if ((string) $locale === '') {
            $locale = service('request')->getLocale();
        }

        return strtr($route, ['{locale}' => $locale]);
    }

    /**
     * Does the heavy lifting of creating an actual route. You must specify
     * the request method(s) that this route will work for. They can be separated
     * by a pipe character "|" if there is more than one.
     *
     * @param array<array-key, mixed>|Closure|string $to
     * @param array<string, mixed>|null              $options
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

        // Merge group filters.
        if (isset($options['filter'])) {
            $currentFilter     = (array) ($this->currentOptions['filter'] ?? []);
            $options['filter'] = array_merge($currentFilter, (array) $options['filter']);
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
        if (! in_array($options['hostname'] ?? '', ['', '0', []], true)) {
            // @todo determine if there's a way to whitelist hosts?
            if (! $this->checkHostname($options['hostname'])) {
                return;
            }

            $overwrite = true;
        }
        // Limiting to subdomains?
        elseif (! in_array($options['subdomain'] ?? '', ['', '0', []], true)) {
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
                    static fn ($m): string => '$' . $i,
                    $to,
                    1,
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
            if (! str_contains($to, '\\') || strpos($to, '\\') > 0) {
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
     * @param list<string>|string $hostname Hostname in route options
     */
    private function checkHostname($hostname): bool
    {
        // CLI calls can't be on hostname.
        if (! isset($this->httpHost)) {
            return false;
        }

        // Has multiple hostnames
        if (is_array($hostname)) {
            $hostnameLower = array_map(strtolower(...), $hostname);

            return in_array(strtolower($this->httpHost), $hostnameLower, true);
        }

        return strtolower($this->httpHost) === strtolower($hostname);
    }

    /**
     * @param array<array-key, mixed> $to
     *
     * @return array<array-key, mixed>|string
     */
    private function processArrayCallableSyntax(string $from, array $to): array|string
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
            return '\\' . $callableName . '/' . $to[1];
        }

        return $to;
    }

    /**
     * Returns the method param string like `/$1/$2` for placeholders
     */
    private function getMethodParams(string $from): string
    {
        preg_match_all('/\(.+?\)/', $from, $matches);
        $count = count($matches[0]);

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
     * @param list<string>|string $subdomains
     */
    private function checkSubdomains($subdomains): bool
    {
        // CLI calls can't be on subdomain.
        if (! isset($this->httpHost)) {
            return false;
        }

        $this->currentSubdomain ??= parse_subdomain($this->httpHost);

        if (! is_array($subdomains)) {
            $subdomains = [$subdomains];
        }

        // Routes can be limited to any sub-domain. In that case, though,
        // it does require a sub-domain to be present.
        if ($this->currentSubdomain !== '' && in_array('*', $subdomains, true)) {
            return true;
        }

        return in_array($this->currentSubdomain, $subdomains, true);
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
     * @return array<
     *     string,
     *     array{
     *         filter?: list<string>|string, namespace?: string, hostname?: string,
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
     * @param string|null $verb HTTP verb like `GET`,`POST` or `*` or `CLI`.
     *                          `'*'` returns all controllers in any verb.
     *
     * @return list<string> controller name list
     *
     * @interal
     */
    public function getRegisteredControllers(?string $verb = '*'): array
    {
        if ($verb !== '*' && $verb === strtolower($verb)) {
            @trigger_error(
                'Passing lowercase HTTP method "' . $verb . '" is deprecated.'
                . ' Use uppercase HTTP method like "' . strtoupper($verb) . '".',
                E_USER_DEPRECATED,
            );
        }

        /**
         * @deprecated 4.5.0
         * @TODO Remove this in the future.
         */
        $verb = strtoupper($verb);

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

    public function shouldUseSupportedLocalesOnly(): bool
    {
        return $this->useSupportedLocalesOnly;
    }
}
