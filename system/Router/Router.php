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
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Exceptions\BadRequestException;
use CodeIgniter\HTTP\Exceptions\RedirectException;
use CodeIgniter\HTTP\Method;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Router\Exceptions\RouterException;
use Config\App;
use Config\Feature;
use Config\Routing;

/**
 * Request router.
 *
 * @see \CodeIgniter\Router\RouterTest
 */
class Router implements RouterInterface
{
    /**
     * List of allowed HTTP methods (and CLI for command line use).
     */
    public const HTTP_METHODS = [
        Method::GET,
        Method::HEAD,
        Method::POST,
        Method::PATCH,
        Method::PUT,
        Method::DELETE,
        Method::OPTIONS,
        Method::TRACE,
        Method::CONNECT,
        'CLI',
    ];

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
     * @var (Closure(mixed...): (ResponseInterface|string|void))|string
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
     * @var bool
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
     * @var list<string>
     */
    protected $filtersInfo = [];

    protected ?AutoRouterInterface $autoRouter = null;

    /**
     * Permitted URI chars
     *
     * The default value is `''` (do not check) for backward compatibility.
     */
    protected string $permittedURIChars = '';

    /**
     * Stores a reference to the RouteCollection object.
     */
    public function __construct(RouteCollectionInterface $routes, ?Request $request = null)
    {
        $config = config(App::class);

        if (isset($config->permittedURIChars)) {
            $this->permittedURIChars = $config->permittedURIChars;
        }

        $this->collection = $routes;

        // These are only for auto-routing
        $this->controller = $this->collection->getDefaultController();
        $this->method     = $this->collection->getDefaultMethod();

        $this->collection->setHTTPVerb($request->getMethod() === '' ? $_SERVER['REQUEST_METHOD'] : $request->getMethod());

        $this->translateURIDashes = $this->collection->shouldTranslateURIDashes();

        if ($this->collection->shouldAutoRoute()) {
            $autoRoutesImproved = config(Feature::class)->autoRoutesImproved ?? false;
            if ($autoRoutesImproved) {
                assert($this->collection instanceof RouteCollection);

                $this->autoRouter = new AutoRouterImproved(
                    $this->collection->getRegisteredControllers('*'),
                    $this->collection->getDefaultNamespace(),
                    $this->collection->getDefaultController(),
                    $this->collection->getDefaultMethod(),
                    $this->translateURIDashes,
                );
            } else {
                $this->autoRouter = new AutoRouter(
                    $this->collection->getRoutes('CLI', false),
                    $this->collection->getDefaultNamespace(),
                    $this->collection->getDefaultController(),
                    $this->collection->getDefaultMethod(),
                    $this->translateURIDashes,
                );
            }
        }
    }

    /**
     * Finds the controller corresponding to the URI.
     *
     * @param string|null $uri URI path relative to baseURL
     *
     * @return (Closure(mixed...): (ResponseInterface|string|void))|string Controller classname or Closure
     *
     * @throws BadRequestException
     * @throws PageNotFoundException
     * @throws RedirectException
     */
    public function handle(?string $uri = null)
    {
        // If we cannot find a URI to match against, then set it to root (`/`).
        if ($uri === null || $uri === '') {
            $uri = '/';
        }

        // Decode URL-encoded string
        $uri = urldecode($uri);

        $this->checkDisallowedChars($uri);

        // Restart filterInfo
        $this->filtersInfo = [];

        // Checks defined routes
        if ($this->checkRoutes($uri)) {
            if ($this->collection->isFiltered($this->matchedRoute[0])) {
                $this->filtersInfo = $this->collection->getFiltersForRoute($this->matchedRoute[0]);
            }

            return $this->controller;
        }

        // Still here? Then we can try to match the URI against
        // Controllers/directories, but the application may not
        // want this, like in the case of API's.
        if (! $this->collection->shouldAutoRoute()) {
            throw new PageNotFoundException(
                "Can't find a route for '{$this->collection->getHTTPVerb()}: {$uri}'.",
            );
        }

        // Checks auto routes
        $this->autoRoute($uri);

        return $this->controllerName();
    }

    /**
     * Returns the filter info for the matched route, if any.
     *
     * @return list<string>
     */
    public function getFilters(): array
    {
        return $this->filtersInfo;
    }

    /**
     * Returns the name of the matched controller or closure.
     *
     * @return (Closure(mixed...): (ResponseInterface|string|void))|string Controller classname or Closure
     */
    public function controllerName()
    {
        return $this->translateURIDashes && ! $this->controller instanceof Closure
            ? str_replace('-', '_', $this->controller)
            : $this->controller;
    }

    /**
     * Returns the name of the method to run in the
     * chosen controller.
     */
    public function methodName(): string
    {
        return $this->translateURIDashes
            ? str_replace('-', '_', $this->method)
            : $this->method;
    }

    /**
     * Returns the 404 Override settings from the Collection.
     * If the override is a string, will split to controller/index array.
     *
     * @return array{string, string}|(Closure(string): (ResponseInterface|string|void))|null
     */
    public function get404Override()
    {
        $route = $this->collection->get404Override();

        if (is_string($route)) {
            $routeArray = explode('::', $route);

            return [
                $routeArray[0], // Controller
                $routeArray[1] ?? 'index',   // Method
            ];
        }

        if (is_callable($route)) {
            return $route;
        }

        return null;
    }

    /**
     * Returns the binds that have been matched and collected
     * during the parsing process as an array, ready to send to
     * instance->method(...$params).
     */
    public function params(): array
    {
        return $this->params;
    }

    /**
     * Returns the name of the sub-directory the controller is in,
     * if any. Relative to APPPATH.'Controllers'.
     *
     * Only used when auto-routing is turned on.
     */
    public function directory(): string
    {
        if ($this->autoRouter instanceof AutoRouter) {
            return $this->autoRouter->directory();
        }

        return '';
    }

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

    /**
     * Returns all options set for the matched route
     *
     * @return array|null
     */
    public function getMatchedRouteOptions()
    {
        return $this->matchedRouteOptions;
    }

    /**
     * Sets the value that should be used to match the index.php file. Defaults
     * to index.php but this allows you to modify it in case you are using
     * something like mod_rewrite to remove the page. This allows you to set
     * it a blank.
     *
     * @param string $page
     */
    public function setIndexPage($page): self
    {
        $this->indexPage = $page;

        return $this;
    }

    /**
     * Tells the system whether we should translate URI dashes or not
     * in the URI from a dash to an underscore.
     *
     * @deprecated This method should be removed.
     */
    public function setTranslateURIDashes(bool $val = false): self
    {
        if ($this->autoRouter instanceof AutoRouter) {
            $this->autoRouter->setTranslateURIDashes($val);

            return $this;
        }

        return $this;
    }

    /**
     * Returns true/false based on whether the current route contained
     * a {locale} placeholder.
     *
     * @return bool
     */
    public function hasLocale()
    {
        return (bool) $this->detectedLocale;
    }

    /**
     * Returns the detected locale, if any, or null.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->detectedLocale;
    }

    /**
     * Checks Defined Routes.
     *
     * Compares the uri string against the routes that the
     * RouteCollection class defined for us, attempting to find a match.
     * This method will modify $this->controller, etal as needed.
     *
     * @param string $uri The URI path to compare against the routes
     *
     * @return bool Whether the route was matched or not.
     *
     * @throws RedirectException
     */
    protected function checkRoutes(string $uri): bool
    {
        $routes = $this->collection->getRoutes($this->collection->getHTTPVerb());

        // Don't waste any time
        if ($routes === []) {
            return false;
        }

        $uri = $uri === '/'
            ? $uri
            : trim($uri, '/ ');

        // Loop through the route array looking for wildcards
        foreach ($routes as $routeKey => $handler) {
            $routeKey = $routeKey === '/'
                ? $routeKey
                // $routeKey may be int, because it is an array key,
                // and the URI `/1` is valid. The leading `/` is removed.
                : ltrim((string) $routeKey, '/ ');

            $matchedKey = $routeKey;

            // Are we dealing with a locale?
            if (str_contains($routeKey, '{locale}')) {
                $routeKey = str_replace('{locale}', '[^/]+', $routeKey);
            }

            // Does the RegEx match?
            if (preg_match('#^' . $routeKey . '$#u', $uri, $matches)) {
                // Is this route supposed to redirect to another?
                if ($this->collection->isRedirect($routeKey)) {
                    // replacing matched route groups with references: post/([0-9]+) -> post/$1
                    $redirectTo = preg_replace_callback('/(\([^\(]+\))/', static function (): string {
                        static $i = 1;

                        return '$' . $i++;
                    }, is_array($handler) ? key($handler) : $handler);

                    throw new RedirectException(
                        preg_replace('#\A' . $routeKey . '\z#u', $redirectTo, $uri),
                        $this->collection->getRedirectCode($routeKey),
                    );
                }
                // Store our locale so CodeIgniter object can
                // assign it to the Request.
                if (str_contains($matchedKey, '{locale}')) {
                    preg_match(
                        '#^' . str_replace('{locale}', '(?<locale>[^/]+)', $matchedKey) . '$#u',
                        $uri,
                        $matched,
                    );

                    if ($this->collection->shouldUseSupportedLocalesOnly()
                        && ! in_array($matched['locale'], config(App::class)->supportedLocales, true)) {
                        // Throw exception to prevent the autorouter, if enabled,
                        // from trying to find a route
                        throw PageNotFoundException::forLocaleNotSupported($matched['locale']);
                    }

                    $this->detectedLocale = $matched['locale'];
                    unset($matched);
                }

                // Are we using Closures? If so, then we need
                // to collect the params into an array
                // so it can be passed to the controller method later.
                if (! is_string($handler) && is_callable($handler)) {
                    $this->controller = $handler;

                    // Remove the original string from the matches array
                    array_shift($matches);

                    $this->params = $matches;

                    $this->setMatchedRoute($matchedKey, $handler);

                    return true;
                }

                if (str_contains($handler, '::')) {
                    [$controller, $methodAndParams] = explode('::', $handler);
                } else {
                    $controller      = $handler;
                    $methodAndParams = '';
                }

                // Checks `/` in controller name
                if (str_contains($controller, '/')) {
                    throw RouterException::forInvalidControllerName($handler);
                }

                if (str_contains($handler, '$') && str_contains($routeKey, '(')) {
                    // Checks dynamic controller
                    if (str_contains($controller, '$')) {
                        throw RouterException::forDynamicController($handler);
                    }

                    if (config(Routing::class)->multipleSegmentsOneParam === false) {
                        // Using back-references
                        $segments = explode('/', preg_replace('#\A' . $routeKey . '\z#u', $handler, $uri));
                    } else {
                        if (str_contains($methodAndParams, '/')) {
                            [$method, $handlerParams] = explode('/', $methodAndParams, 2);
                            $params                   = explode('/', $handlerParams);
                            $handlerSegments          = array_merge([$controller . '::' . $method], $params);
                        } else {
                            $handlerSegments = [$handler];
                        }

                        $segments = [];

                        foreach ($handlerSegments as $segment) {
                            $segments[] = $this->replaceBackReferences($segment, $matches);
                        }
                    }
                } else {
                    $segments = explode('/', $handler);
                }

                $this->setRequest($segments);

                $this->setMatchedRoute($matchedKey, $handler);

                return true;
            }
        }

        return false;
    }

    /**
     * Replace string `$n` with `$matches[n]` value.
     */
    private function replaceBackReferences(string $input, array $matches): string
    {
        $pattern = '/\$([1-' . count($matches) . '])/u';

        return preg_replace_callback(
            $pattern,
            static function ($match) use ($matches) {
                $index = (int) $match[1];

                return $matches[$index] ?? '';
            },
            $input,
        );
    }

    /**
     * Checks Auto Routes.
     *
     * Attempts to match a URI path against Controllers and directories
     * found in APPPATH/Controllers, to find a matching route.
     *
     * @return void
     */
    public function autoRoute(string $uri)
    {
        [$this->directory, $this->controller, $this->method, $this->params]
            = $this->autoRouter->getRoute($uri, $this->collection->getHTTPVerb());
    }

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

    /**
     * Scans the controller directory, attempting to locate a controller matching the supplied uri $segments
     *
     * @param array $segments URI segments
     *
     * @return array returns an array of remaining uri segments that don't map onto a directory
     *
     * @deprecated Not used. Moved to AutoRouter class.
     */
    protected function scanControllers(array $segments): array
    {
        $segments = array_filter($segments, static fn ($segment): bool => $segment !== '');
        // numerically reindex the array, removing gaps
        $segments = array_values($segments);

        // if a prior directory value has been set, just return segments and get out of here
        if (isset($this->directory)) {
            return $segments;
        }

        // Loop through our segments and return as soon as a controller
        // is found or when such a directory doesn't exist
        $c = count($segments);

        while ($c-- > 0) {
            $segmentConvert = ucfirst($this->translateURIDashes === true ? str_replace('-', '_', $segments[0]) : $segments[0]);
            // as soon as we encounter any segment that is not PSR-4 compliant, stop searching
            if (! $this->isValidSegment($segmentConvert)) {
                return $segments;
            }

            $test = APPPATH . 'Controllers/' . $this->directory . $segmentConvert;

            // as long as each segment is *not* a controller file but does match a directory, add it to $this->directory
            if (! is_file($test . '.php') && is_dir($test)) {
                $this->setDirectory($segmentConvert, true, false);
                array_shift($segments);

                continue;
            }

            return $segments;
        }

        // This means that all segments were actually directories
        return $segments;
    }

    /**
     * Sets the sub-directory that the controller is in.
     *
     * @param bool $validate if true, checks to make sure $dir consists of only PSR4 compliant segments
     *
     * @return void
     *
     * @deprecated This method should be removed.
     */
    public function setDirectory(?string $dir = null, bool $append = false, bool $validate = true)
    {
        if ($dir === null || $dir === '') {
            $this->directory = null;
        }

        if ($this->autoRouter instanceof AutoRouter) {
            $this->autoRouter->setDirectory($dir, $append, $validate);
        }
    }

    /**
     * Returns true if the supplied $segment string represents a valid PSR-4 compliant namespace/directory segment
     *
     * regex comes from https://www.php.net/manual/en/language.variables.basics.php
     *
     * @deprecated Moved to AutoRouter class.
     */
    private function isValidSegment(string $segment): bool
    {
        return (bool) preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/', $segment);
    }

    /**
     * Set request route
     *
     * Takes an array of URI segments as input and sets the class/method
     * to be called.
     *
     * @param array $segments URI segments
     *
     * @return void
     */
    protected function setRequest(array $segments = [])
    {
        // If we don't have any segments - use the default controller;
        if ($segments === []) {
            return;
        }

        [$controller, $method] = array_pad(explode('::', $segments[0]), 2, null);

        $this->controller = $controller;

        // $this->method already contains the default method name,
        // so don't overwrite it with emptiness.
        if (! empty($method)) {
            $this->method = $method;
        }

        array_shift($segments);

        $this->params = $segments;
    }

    /**
     * Sets the default controller based on the info set in the RouteCollection.
     *
     * @deprecated This was an unnecessary method, so it is no longer used.
     *
     * @return void
     */
    protected function setDefaultController()
    {
        if (empty($this->controller)) {
            throw RouterException::forMissingDefaultRoute();
        }

        sscanf($this->controller, '%[^/]/%s', $class, $this->method);

        if (! is_file(APPPATH . 'Controllers/' . $this->directory . ucfirst($class) . '.php')) {
            return;
        }

        $this->controller = ucfirst($class);

        log_message('info', 'Used the default controller.');
    }

    /**
     * @param callable|string $handler
     */
    protected function setMatchedRoute(string $route, $handler): void
    {
        $this->matchedRoute = [$route, $handler];

        $this->matchedRouteOptions = $this->collection->getRoutesOptions($route);
    }

    /**
     * Checks disallowed characters
     */
    private function checkDisallowedChars(string $uri): void
    {
        foreach (explode('/', $uri) as $segment) {
            if ($segment !== '' && $this->permittedURIChars !== ''
                && preg_match('/\A[' . $this->permittedURIChars . ']+\z/iu', $segment) !== 1
            ) {
                throw new BadRequestException(
                    'The URI you submitted has disallowed characters: "' . $segment . '"',
                );
            }
        }
    }
}
