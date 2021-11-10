<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter;

use Closure;
use CodeIgniter\Debug\Timer;
use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Router\Exceptions\RedirectException;
use CodeIgniter\Router\RouteCollectionInterface;
use CodeIgniter\Router\Router;
use Config\App;
use Config\Cache;
use Config\Services;
use Exception;
use Kint;
use Kint\Renderer\CliRenderer;
use Kint\Renderer\RichRenderer;

/**
 * This class is the core of the framework, and will analyse the
 * request, route it to a controller, and send back the response.
 * Of course, there are variations to that flow, but this is the brains.
 */
class CodeIgniter
{
    /**
     * The current version of CodeIgniter Framework
     */
    public const CI_VERSION = '4.1.5';

    private const MIN_PHP_VERSION = '7.3';

    /**
     * App startup time.
     *
     * @var mixed
     */
    protected $startTime;

    /**
     * Total app execution time
     *
     * @var float
     */
    protected $totalTime;

    /**
     * Main application configuration
     *
     * @var App
     */
    protected $config;

    /**
     * Timer instance.
     *
     * @var Timer
     */
    protected $benchmark;

    /**
     * Current request.
     *
     * @var CLIRequest|IncomingRequest|Request
     */
    protected $request;

    /**
     * Current response.
     *
     * @var ResponseInterface
     */
    protected $response;

    /**
     * Router to use.
     *
     * @var Router
     */
    protected $router;

    /**
     * Controller to use.
     *
     * @var Closure|string
     */
    protected $controller;

    /**
     * Controller method to invoke.
     *
     * @var string
     */
    protected $method;

    /**
     * Output handler to use.
     *
     * @var string
     */
    protected $output;

    /**
     * Cache expiration time
     *
     * @var int
     */
    protected static $cacheTTL = 0;

    /**
     * Request path to use.
     *
     * @var string
     */
    protected $path;

    /**
     * Should the Response instance "pretend"
     * to keep from setting headers/cookies/etc
     *
     * @var bool
     */
    protected $useSafeOutput = false;

    /**
     * Constructor.
     */
    public function __construct(App $config)
    {
        if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<')) {
            // @codeCoverageIgnoreStart
            $message = extension_loaded('intl')
                ? lang('Core.invalidPhpVersion', [self::MIN_PHP_VERSION, PHP_VERSION])
                : sprintf('Your PHP version must be %s or higher to run CodeIgniter. Current version: %s', self::MIN_PHP_VERSION, PHP_VERSION);

            exit($message);
            // @codeCoverageIgnoreEnd
        }

        $this->startTime = microtime(true);
        $this->config    = $config;
    }

    /**
     * Handles some basic app and environment setup.
     */
    public function initialize()
    {
        // Define environment variables
        $this->detectEnvironment();
        $this->bootstrapEnvironment();

        // Setup Exception Handling
        Services::exceptions()->initialize();

        // Run this check for manual installations
        if (! is_file(COMPOSER_PATH)) {
            $this->resolvePlatformExtensions(); // @codeCoverageIgnore
        }

        // Set default locale on the server
        locale_set_default($this->config->defaultLocale ?? 'en');

        // Set default timezone on the server
        date_default_timezone_set($this->config->appTimezone ?? 'UTC');

        $this->initializeKint();

        if (! CI_DEBUG) {
            Kint::$enabled_mode = false; // @codeCoverageIgnore
        }
    }

    /**
     * Checks system for missing required PHP extensions.
     *
     * @throws FrameworkException
     *
     * @codeCoverageIgnore
     */
    protected function resolvePlatformExtensions()
    {
        $requiredExtensions = [
            'curl',
            'intl',
            'json',
            'mbstring',
            'xml',
        ];

        $missingExtensions = [];

        foreach ($requiredExtensions as $extension) {
            if (! extension_loaded($extension)) {
                $missingExtensions[] = $extension;
            }
        }

        if ($missingExtensions !== []) {
            throw FrameworkException::forMissingExtension(implode(', ', $missingExtensions));
        }
    }

    /**
     * Initializes Kint
     */
    protected function initializeKint()
    {
        // If we have KINT_DIR it means it's already loaded via composer
        if (! defined('KINT_DIR')) {
            spl_autoload_register(function ($class) {
                $class = explode('\\', $class);

                if (array_shift($class) !== 'Kint') {
                    return;
                }

                $file = SYSTEMPATH . 'ThirdParty/Kint/' . implode('/', $class) . '.php';

                if (file_exists($file)) {
                    require_once $file;
                }
            });

            require_once SYSTEMPATH . 'ThirdParty/Kint/init.php';
        }

        /**
         * Config\Kint
         */
        $config = config('Config\Kint');

        Kint::$max_depth           = $config->maxDepth;
        Kint::$display_called_from = $config->displayCalledFrom;
        Kint::$expanded            = $config->expanded;

        if (! empty($config->plugins) && is_array($config->plugins)) {
            Kint::$plugins = $config->plugins;
        }

        RichRenderer::$theme  = $config->richTheme;
        RichRenderer::$folder = $config->richFolder;
        RichRenderer::$sort   = $config->richSort;
        if (! empty($config->richObjectPlugins) && is_array($config->richObjectPlugins)) {
            RichRenderer::$object_plugins = $config->richObjectPlugins;
        }
        if (! empty($config->richTabPlugins) && is_array($config->richTabPlugins)) {
            RichRenderer::$tab_plugins = $config->richTabPlugins;
        }

        CliRenderer::$cli_colors         = $config->cliColors;
        CliRenderer::$force_utf8         = $config->cliForceUTF8;
        CliRenderer::$detect_width       = $config->cliDetectWidth;
        CliRenderer::$min_terminal_width = $config->cliMinWidth;
    }

    /**
     * Launch the application!
     *
     * This is "the loop" if you will. The main entry point into the script
     * that gets the required class instances, fires off the filters,
     * tries to route the response, loads the controller and generally
     * makes all of the pieces work together.
     *
     * @throws Exception
     * @throws RedirectException
     *
     * @return bool|mixed|RequestInterface|ResponseInterface
     */
    public function run(?RouteCollectionInterface $routes = null, bool $returnResponse = false)
    {
        $this->startBenchmark();

        $this->getRequestObject();
        $this->getResponseObject();

        $this->forceSecureAccess();

        $this->spoofRequestMethod();

        Events::trigger('pre_system');

        // Check for a cached page. Execution will stop
        // if the page has been cached.
        $cacheConfig = new Cache();
        $response    = $this->displayCache($cacheConfig);
        if ($response instanceof ResponseInterface) {
            if ($returnResponse) {
                return $response;
            }

            $this->response->pretend($this->useSafeOutput)->send();
            $this->callExit(EXIT_SUCCESS);

            return;
        }

        try {
            return $this->handleRequest($routes, $cacheConfig, $returnResponse);
        } catch (RedirectException $e) {
            $logger = Services::logger();
            $logger->info('REDIRECTED ROUTE at ' . $e->getMessage());

            // If the route is a 'redirect' route, it throws
            // the exception with the $to as the message
            $this->response->redirect(base_url($e->getMessage()), 'auto', $e->getCode());
            $this->sendResponse();

            $this->callExit(EXIT_SUCCESS);

            return;
        } catch (PageNotFoundException $e) {
            $this->display404errors($e);
        }
    }

    /**
     * Set our Response instance to "pretend" mode so that things like
     * cookies and headers are not actually sent, allowing PHP 7.2+ to
     * not complain when ini_set() function is used.
     *
     * @return $this
     */
    public function useSafeOutput(bool $safe = true)
    {
        $this->useSafeOutput = $safe;

        return $this;
    }

    /**
     * Handles the main request logic and fires the controller.
     *
     * @throws RedirectException
     *
     * @return mixed|RequestInterface|ResponseInterface
     */
    protected function handleRequest(?RouteCollectionInterface $routes, Cache $cacheConfig, bool $returnResponse = false)
    {
        $routeFilter = $this->tryToRouteIt($routes);

        $uri = $this->determinePath();

        // Start up the filters
        $filters = Services::filters();

        // If any filters were specified within the routes file,
        // we need to ensure it's active for the current request
        if ($routeFilter !== null) {
            $multipleFiltersEnabled = config('Feature')->multipleFilters ?? false;
            if ($multipleFiltersEnabled) {
                $filters->enableFilters($routeFilter, 'before');
                $filters->enableFilters($routeFilter, 'after');
            } else {
                // for backward compatibility
                $filters->enableFilter($routeFilter, 'before');
                $filters->enableFilter($routeFilter, 'after');
            }
        }

        // Never run filters when running through Spark cli
        if (! defined('SPARKED')) {
            // Run "before" filters
            $this->benchmark->start('before_filters');
            $possibleResponse = $filters->run($uri, 'before');
            $this->benchmark->stop('before_filters');

            // If a ResponseInterface instance is returned then send it back to the client and stop
            if ($possibleResponse instanceof ResponseInterface) {
                return $returnResponse ? $possibleResponse : $possibleResponse->pretend($this->useSafeOutput)->send();
            }

            if ($possibleResponse instanceof Request) {
                $this->request = $possibleResponse;
            }
        }

        $returned = $this->startController();

        // Closure controller has run in startController().
        if (! is_callable($this->controller)) {
            $controller = $this->createController();

            if (! method_exists($controller, '_remap') && ! is_callable([$controller, $this->method], false)) {
                throw PageNotFoundException::forMethodNotFound($this->method);
            }

            // Is there a "post_controller_constructor" event?
            Events::trigger('post_controller_constructor');

            $returned = $this->runController($controller);
        } else {
            $this->benchmark->stop('controller_constructor');
            $this->benchmark->stop('controller');
        }

        // If $returned is a string, then the controller output something,
        // probably a view, instead of echoing it directly. Send it along
        // so it can be used with the output.
        $this->gatherOutput($cacheConfig, $returned);

        // Never run filters when running through Spark cli
        if (! defined('SPARKED')) {
            $filters->setResponse($this->response);

            // Run "after" filters
            $this->benchmark->start('after_filters');
            $response = $filters->run($uri, 'after');
            $this->benchmark->stop('after_filters');
        } else {
            $response = $this->response;

            // Set response code for CLI command failures
            if (is_numeric($returned) || $returned === false) {
                $response->setStatusCode(400);
            }
        }

        if ($response instanceof ResponseInterface) {
            $this->response = $response;
        }

        // Save our current URI as the previous URI in the session
        // for safer, more accurate use with `previous_url()` helper function.
        $this->storePreviousURL(current_url(true));

        unset($uri);

        if (! $returnResponse) {
            $this->sendResponse();
        }

        // Is there a post-system event?
        Events::trigger('post_system');

        return $this->response;
    }

    /**
     * You can load different configurations depending on your
     * current environment. Setting the environment also influences
     * things like logging and error reporting.
     *
     * This can be set to anything, but default usage is:
     *
     *     development
     *     testing
     *     production
     *
     * @codeCoverageIgnore
     */
    protected function detectEnvironment()
    {
        // Make sure ENVIRONMENT isn't already set by other means.
        if (! defined('ENVIRONMENT')) {
            define('ENVIRONMENT', $_SERVER['CI_ENVIRONMENT'] ?? 'production');
        }
    }

    /**
     * Load any custom boot files based upon the current environment.
     *
     * If no boot file exists, we shouldn't continue because something
     * is wrong. At the very least, they should have error reporting setup.
     */
    protected function bootstrapEnvironment()
    {
        if (is_file(APPPATH . 'Config/Boot/' . ENVIRONMENT . '.php')) {
            require_once APPPATH . 'Config/Boot/' . ENVIRONMENT . '.php';
        } else {
            // @codeCoverageIgnoreStart
            header('HTTP/1.1 503 Service Unavailable.', true, 503);
            echo 'The application environment is not set correctly.';

            exit(EXIT_ERROR); // EXIT_ERROR
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Start the Benchmark
     *
     * The timer is used to display total script execution both in the
     * debug toolbar, and potentially on the displayed page.
     */
    protected function startBenchmark()
    {
        if ($this->startTime === null) {
            $this->startTime = microtime(true);
        }

        $this->benchmark = Services::timer();
        $this->benchmark->start('total_execution', $this->startTime);
        $this->benchmark->start('bootstrap');
    }

    /**
     * Sets a Request object to be used for this request.
     * Used when running certain tests.
     *
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get our Request object, (either IncomingRequest or CLIRequest)
     * and set the server protocol based on the information provided
     * by the server.
     */
    protected function getRequestObject()
    {
        if ($this->request instanceof Request) {
            return;
        }

        // @phpstan-ignore-next-line
        if (is_cli() && ENVIRONMENT !== 'testing') {
            // @codeCoverageIgnoreStart
            $this->request = Services::clirequest($this->config);
        // @codeCoverageIgnoreEnd
        } else {
            $this->request = Services::request($this->config);
            // guess at protocol if needed
            $this->request->setProtocolVersion($_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1');
        }
    }

    /**
     * Get our Response object, and set some default values, including
     * the HTTP protocol version and a default successful response.
     */
    protected function getResponseObject()
    {
        $this->response = Services::response($this->config);

        if (! is_cli() || ENVIRONMENT === 'testing') {
            $this->response->setProtocolVersion($this->request->getProtocolVersion());
        }

        // Assume success until proven otherwise.
        $this->response->setStatusCode(200);
    }

    /**
     * Force Secure Site Access? If the config value 'forceGlobalSecureRequests'
     * is true, will enforce that all requests to this site are made through
     * HTTPS. Will redirect the user to the current page with HTTPS, as well
     * as set the HTTP Strict Transport Security header for those browsers
     * that support it.
     *
     * @param int $duration How long the Strict Transport Security
     *                      should be enforced for this URL.
     */
    protected function forceSecureAccess($duration = 31536000)
    {
        if ($this->config->forceGlobalSecureRequests !== true) {
            return;
        }

        force_https($duration, $this->request, $this->response);
    }

    /**
     * Determines if a response has been cached for the given URI.
     *
     * @throws Exception
     *
     * @return bool|ResponseInterface
     */
    public function displayCache(Cache $config)
    {
        if ($cachedResponse = cache()->get($this->generateCacheName($config))) {
            $cachedResponse = unserialize($cachedResponse);
            if (! is_array($cachedResponse) || ! isset($cachedResponse['output']) || ! isset($cachedResponse['headers'])) {
                throw new Exception('Error unserializing page cache');
            }

            $headers = $cachedResponse['headers'];
            $output  = $cachedResponse['output'];

            // Clear all default headers
            foreach (array_keys($this->response->headers()) as $key) {
                $this->response->removeHeader($key);
            }

            // Set cached headers
            foreach ($headers as $name => $value) {
                $this->response->setHeader($name, $value);
            }

            $output = $this->displayPerformanceMetrics($output);
            $this->response->setBody($output);

            return $this->response;
        }

        return false;
    }

    /**
     * Tells the app that the final output should be cached.
     */
    public static function cache(int $time)
    {
        static::$cacheTTL = $time;
    }

    /**
     * Caches the full response from the current request. Used for
     * full-page caching for very high performance.
     *
     * @return mixed
     */
    public function cachePage(Cache $config)
    {
        $headers = [];

        foreach ($this->response->headers() as $header) {
            $headers[$header->getName()] = $header->getValueLine();
        }

        return cache()->save($this->generateCacheName($config), serialize(['headers' => $headers, 'output' => $this->output]), static::$cacheTTL);
    }

    /**
     * Returns an array with our basic performance stats collected.
     */
    public function getPerformanceStats(): array
    {
        return [
            'startTime' => $this->startTime,
            'totalTime' => $this->totalTime,
        ];
    }

    /**
     * Generates the cache name to use for our full-page caching.
     */
    protected function generateCacheName(Cache $config): string
    {
        if ($this->request instanceof CLIRequest) {
            return md5($this->request->getPath());
        }

        $uri = $this->request->getUri();

        if ($config->cacheQueryString) {
            $name = URI::createURIString($uri->getScheme(), $uri->getAuthority(), $uri->getPath(), $uri->getQuery());
        } else {
            $name = URI::createURIString($uri->getScheme(), $uri->getAuthority(), $uri->getPath());
        }

        return md5($name);
    }

    /**
     * Replaces the elapsed_time tag.
     */
    public function displayPerformanceMetrics(string $output): string
    {
        $this->totalTime = $this->benchmark->getElapsedTime('total_execution');

        return str_replace('{elapsed_time}', (string) $this->totalTime, $output);
    }

    /**
     * Try to Route It - As it sounds like, works with the router to
     * match a route against the current URI. If the route is a
     * "redirect route", will also handle the redirect.
     *
     * @param RouteCollectionInterface|null $routes An collection interface to use in place
     *                                              of the config file.
     *
     * @throws RedirectException
     *
     * @return string|string[]|null
     */
    protected function tryToRouteIt(?RouteCollectionInterface $routes = null)
    {
        if ($routes === null) {
            require APPPATH . 'Config/Routes.php';
        }

        // $routes is defined in Config/Routes.php
        $this->router = Services::router($routes, $this->request);

        $path = $this->determinePath();

        $this->benchmark->stop('bootstrap');
        $this->benchmark->start('routing');

        ob_start();

        $this->controller = $this->router->handle($path);
        $this->method     = $this->router->methodName();

        // If a {locale} segment was matched in the final route,
        // then we need to set the correct locale on our Request.
        if ($this->router->hasLocale()) {
            $this->request->setLocale($this->router->getLocale()); // @phpstan-ignore-line
        }

        $this->benchmark->stop('routing');

        // for backward compatibility
        $multipleFiltersEnabled = config('Feature')->multipleFilters ?? false;
        if (! $multipleFiltersEnabled) {
            return $this->router->getFilter();
        }

        return $this->router->getFilters();
    }

    /**
     * Determines the path to use for us to try to route to, based
     * on user input (setPath), or the CLI/IncomingRequest path.
     */
    protected function determinePath()
    {
        if (! empty($this->path)) {
            return $this->path;
        }

        return method_exists($this->request, 'getPath') ? $this->request->getPath() : $this->request->getUri()->getPath();
    }

    /**
     * Allows the request path to be set from outside the class,
     * instead of relying on CLIRequest or IncomingRequest for the path.
     *
     * This is primarily used by the Console.
     *
     * @return $this
     */
    public function setPath(string $path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Now that everything has been setup, this method attempts to run the
     * controller method and make the script go. If it's not able to, will
     * show the appropriate Page Not Found error.
     */
    protected function startController()
    {
        $this->benchmark->start('controller');
        $this->benchmark->start('controller_constructor');

        // Is it routed to a Closure?
        if (is_object($this->controller) && (get_class($this->controller) === 'Closure')) {
            $controller = $this->controller;

            return $controller(...$this->router->params());
        }

        // No controller specified - we don't know what to do now.
        if (empty($this->controller)) {
            throw PageNotFoundException::forEmptyController();
        }

        // Try to autoload the class
        if (! class_exists($this->controller, true) || $this->method[0] === '_') {
            throw PageNotFoundException::forControllerNotFound($this->controller, $this->method);
        }
    }

    /**
     * Instantiates the controller class.
     *
     * @return mixed
     */
    protected function createController()
    {
        $class = new $this->controller();
        $class->initController($this->request, $this->response, Services::logger());

        $this->benchmark->stop('controller_constructor');

        return $class;
    }

    /**
     * Runs the controller, allowing for _remap methods to function.
     *
     * @param mixed $class
     *
     * @return mixed
     */
    protected function runController($class)
    {
        // If this is a console request then use the input segments as parameters
        $params = defined('SPARKED') ? $this->request->getSegments() : $this->router->params(); // @phpstan-ignore-line

        if (method_exists($class, '_remap')) {
            $output = $class->_remap($this->method, ...$params);
        } else {
            $output = $class->{$this->method}(...$params);
        }

        $this->benchmark->stop('controller');

        return $output;
    }

    /**
     * Displays a 404 Page Not Found error. If set, will try to
     * call the 404Override controller/method that was set in routing config.
     */
    protected function display404errors(PageNotFoundException $e)
    {
        // Is there a 404 Override available?
        if ($override = $this->router->get404Override()) {
            if ($override instanceof Closure) {
                echo $override($e->getMessage());
            } elseif (is_array($override)) {
                $this->benchmark->start('controller');
                $this->benchmark->start('controller_constructor');

                $this->controller = $override[0];
                $this->method     = $override[1];

                $controller = $this->createController();
                $this->runController($controller);
            }

            unset($override);

            $cacheConfig = new Cache();
            $this->gatherOutput($cacheConfig);
            $this->sendResponse();

            return;
        }

        // Display 404 Errors
        $this->response->setStatusCode($e->getCode());

        if (ENVIRONMENT !== 'testing') {
            // @codeCoverageIgnoreStart
            if (ob_get_level() > 0) {
                ob_end_flush();
            }
            // @codeCoverageIgnoreEnd
        }
        // When testing, one is for phpunit, another is for test case.
        elseif (ob_get_level() > 2) {
            ob_end_flush(); // @codeCoverageIgnore
        }

        throw PageNotFoundException::forPageNotFound(ENVIRONMENT !== 'production' || is_cli() ? $e->getMessage() : '');
    }

    /**
     * Gathers the script output from the buffer, replaces some execution
     * time tag in the output and displays the debug toolbar, if required.
     *
     * @param mixed|null $returned
     */
    protected function gatherOutput(?Cache $cacheConfig = null, $returned = null)
    {
        $this->output = ob_get_contents();
        // If buffering is not null.
        // Clean (erase) the output buffer and turn off output buffering
        if (ob_get_length()) {
            ob_end_clean();
        }

        if ($returned instanceof DownloadResponse) {
            $this->response = $returned;

            return;
        }
        // If the controller returned a response object,
        // we need to grab the body from it so it can
        // be added to anything else that might have been
        // echoed already.
        // We also need to save the instance locally
        // so that any status code changes, etc, take place.
        if ($returned instanceof ResponseInterface) {
            $this->response = $returned;
            $returned       = $returned->getBody();
        }

        if (is_string($returned)) {
            $this->output .= $returned;
        }

        // Cache it without the performance metrics replaced
        // so that we can have live speed updates along the way.
        if (static::$cacheTTL > 0) {
            $this->cachePage($cacheConfig);
        }

        $this->output = $this->displayPerformanceMetrics($this->output);

        $this->response->setBody($this->output);
    }

    /**
     * If we have a session object to use, store the current URI
     * as the previous URI. This is called just prior to sending the
     * response to the client, and will make it available next request.
     *
     * This helps provider safer, more reliable previous_url() detection.
     *
     * @param string|URI $uri
     */
    public function storePreviousURL($uri)
    {
        // Ignore CLI requests
        if (is_cli() && ENVIRONMENT !== 'testing') {
            return; // @codeCoverageIgnore
        }
        // Ignore AJAX requests
        if (method_exists($this->request, 'isAJAX') && $this->request->isAJAX()) {
            return;
        }

        // Ignore unroutable responses
        if ($this->response instanceof DownloadResponse || $this->response instanceof RedirectResponse) {
            return;
        }

        // This is mainly needed during testing...
        if (is_string($uri)) {
            $uri = new URI($uri);
        }

        if (isset($_SESSION)) {
            $_SESSION['_ci_previous_url'] = URI::createURIString($uri->getScheme(), $uri->getAuthority(), $uri->getPath(), $uri->getQuery(), $uri->getFragment());
        }
    }

    /**
     * Modifies the Request Object to use a different method if a POST
     * variable called _method is found.
     */
    public function spoofRequestMethod()
    {
        // Only works with POSTED forms
        if ($this->request->getMethod() !== 'post') {
            return;
        }

        $method = $this->request->getPost('_method'); // @phpstan-ignore-line

        if (empty($method)) {
            return;
        }

        $this->request = $this->request->setMethod($method);
    }

    /**
     * Sends the output of this request back to the client.
     * This is what they've been waiting for!
     */
    protected function sendResponse()
    {
        $this->response->pretend($this->useSafeOutput)->send();
    }

    /**
     * Exits the application, setting the exit code for CLI-based applications
     * that might be watching.
     *
     * Made into a separate method so that it can be mocked during testing
     * without actually stopping script execution.
     *
     * @param int $code
     */
    protected function callExit($code)
    {
        exit($code); // @codeCoverageIgnore
    }
}
