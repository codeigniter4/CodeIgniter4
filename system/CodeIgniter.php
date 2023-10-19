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
use CodeIgniter\Cache\ResponseCache;
use CodeIgniter\Debug\Timer;
use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\Exceptions\RedirectException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\ResponsableInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Router\Exceptions\RedirectException as DeprecatedRedirectException;
use CodeIgniter\Router\RouteCollectionInterface;
use CodeIgniter\Router\Router;
use Config\App;
use Config\Cache;
use Config\Feature;
use Config\Kint as KintConfig;
use Config\Services;
use Exception;
use Kint;
use Kint\Renderer\CliRenderer;
use Kint\Renderer\RichRenderer;
use Locale;
use LogicException;
use Throwable;

/**
 * This class is the core of the framework, and will analyse the
 * request, route it to a controller, and send back the response.
 * Of course, there are variations to that flow, but this is the brains.
 *
 * @see \CodeIgniter\CodeIgniterTest
 */
class CodeIgniter
{
    /**
     * The current version of CodeIgniter Framework
     */
    public const CI_VERSION = '4.4.2';

    /**
     * App startup time.
     *
     * @var float|null
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
     * @var CLIRequest|IncomingRequest|null
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
     * @var int seconds
     *
     * @deprecated 4.4.0 Moved to ResponseCache::$ttl. No longer used.
     */
    protected static $cacheTTL = 0;

    /**
     * Request path to use.
     *
     * @var string
     *
     * @deprecated No longer used.
     */
    protected $path;

    /**
     * Should the Response instance "pretend"
     * to keep from setting headers/cookies/etc
     *
     * @var bool
     *
     * @deprecated No longer used.
     */
    protected $useSafeOutput = false;

    /**
     * Context
     *  web:     Invoked by HTTP request
     *  php-cli: Invoked by CLI via `php public/index.php`
     *
     * @phpstan-var 'php-cli'|'web'
     */
    protected ?string $context = null;

    /**
     * Whether to enable Control Filters.
     */
    protected bool $enableFilters = true;

    /**
     * Whether to return Response object or send response.
     *
     * @deprecated No longer used.
     */
    protected bool $returnResponse = false;

    /**
     * Application output buffering level
     */
    protected int $bufferLevel;

    /**
     * Web Page Caching
     */
    protected ResponseCache $pageCache;

    /**
     * Constructor.
     */
    public function __construct(App $config)
    {
        $this->startTime = microtime(true);
        $this->config    = $config;

        $this->pageCache = Services::responsecache();
    }

    /**
     * Handles some basic app and environment setup.
     *
     * @return void
     */
    public function initialize()
    {
        // Define environment variables
        $this->bootstrapEnvironment();

        // Setup Exception Handling
        Services::exceptions()->initialize();

        // Run this check for manual installations
        if (! is_file(COMPOSER_PATH)) {
            $this->resolvePlatformExtensions(); // @codeCoverageIgnore
        }

        // Set default locale on the server
        Locale::setDefault($this->config->defaultLocale ?? 'en');

        // Set default timezone on the server
        date_default_timezone_set($this->config->appTimezone ?? 'UTC');

        $this->initializeKint();
    }

    /**
     * Checks system for missing required PHP extensions.
     *
     * @return void
     *
     * @throws FrameworkException
     *
     * @codeCoverageIgnore
     */
    protected function resolvePlatformExtensions()
    {
        $requiredExtensions = [
            'intl',
            'json',
            'mbstring',
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
     *
     * @return void
     */
    protected function initializeKint()
    {
        if (CI_DEBUG) {
            $this->autoloadKint();
            $this->configureKint();
        } elseif (class_exists(Kint::class)) {
            // In case that Kint is already loaded via Composer.
            Kint::$enabled_mode = false;
            // @codeCoverageIgnore
        }

        helper('kint');
    }

    private function autoloadKint(): void
    {
        // If we have KINT_DIR it means it's already loaded via composer
        if (! defined('KINT_DIR')) {
            spl_autoload_register(function ($class) {
                $class = explode('\\', $class);

                if (array_shift($class) !== 'Kint') {
                    return;
                }

                $file = SYSTEMPATH . 'ThirdParty/Kint/' . implode('/', $class) . '.php';

                if (is_file($file)) {
                    require_once $file;
                }
            });

            require_once SYSTEMPATH . 'ThirdParty/Kint/init.php';
        }
    }

    private function configureKint(): void
    {
        $config = config(KintConfig::class);

        Kint::$depth_limit         = $config->maxDepth;
        Kint::$display_called_from = $config->displayCalledFrom;
        Kint::$expanded            = $config->expanded;

        if (! empty($config->plugins) && is_array($config->plugins)) {
            Kint::$plugins = $config->plugins;
        }

        $csp = Services::csp();
        if ($csp->enabled()) {
            RichRenderer::$js_nonce  = $csp->getScriptNonce();
            RichRenderer::$css_nonce = $csp->getStyleNonce();
        }

        RichRenderer::$theme  = $config->richTheme;
        RichRenderer::$folder = $config->richFolder;
        RichRenderer::$sort   = $config->richSort;
        if (! empty($config->richObjectPlugins) && is_array($config->richObjectPlugins)) {
            RichRenderer::$value_plugins = $config->richObjectPlugins;
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
     * makes all the pieces work together.
     *
     * @return ResponseInterface|void
     */
    public function run(?RouteCollectionInterface $routes = null, bool $returnResponse = false)
    {
        if ($this->context === null) {
            throw new LogicException(
                'Context must be set before run() is called. If you are upgrading from 4.1.x, '
                . 'you need to merge `public/index.php` and `spark` file from `vendor/codeigniter4/framework`.'
            );
        }

        $this->pageCache->setTtl(0);
        $this->bufferLevel = ob_get_level();

        $this->startBenchmark();

        $this->getRequestObject();
        $this->getResponseObject();

        $this->spoofRequestMethod();

        try {
            $this->response = $this->handleRequest($routes, config(Cache::class), $returnResponse);
        } catch (ResponsableInterface|DeprecatedRedirectException $e) {
            $this->outputBufferingEnd();
            if ($e instanceof DeprecatedRedirectException) {
                $e = new RedirectException($e->getMessage(), $e->getCode(), $e);
            }

            $this->response = $e->getResponse();
        } catch (PageNotFoundException $e) {
            $this->response = $this->display404errors($e);
        } catch (Throwable $e) {
            $this->outputBufferingEnd();

            throw $e;
        }

        if ($returnResponse) {
            return $this->response;
        }

        $this->sendResponse();
    }

    /**
     * Set our Response instance to "pretend" mode so that things like
     * cookies and headers are not actually sent, allowing PHP 7.2+ to
     * not complain when ini_set() function is used.
     *
     * @return $this
     *
     * @deprecated No longer used.
     */
    public function useSafeOutput(bool $safe = true)
    {
        $this->useSafeOutput = $safe;

        return $this;
    }

    /**
     * Invoked via php-cli command?
     */
    private function isPhpCli(): bool
    {
        return $this->context === 'php-cli';
    }

    /**
     * Web access?
     */
    private function isWeb(): bool
    {
        return $this->context === 'web';
    }

    /**
     * Disables Controller Filters.
     */
    public function disableFilters(): void
    {
        $this->enableFilters = false;
    }

    /**
     * Handles the main request logic and fires the controller.
     *
     * @return ResponseInterface
     *
     * @throws PageNotFoundException
     * @throws RedirectException
     *
     * @deprecated $returnResponse is deprecated.
     */
    protected function handleRequest(?RouteCollectionInterface $routes, Cache $cacheConfig, bool $returnResponse = false)
    {
        $this->forceSecureAccess();

        if ($this->request instanceof IncomingRequest && strtolower($this->request->getMethod()) === 'cli') {
            return $this->response->setStatusCode(405)->setBody('Method Not Allowed');
        }

        Events::trigger('pre_system');

        // Check for a cached page. Execution will stop
        // if the page has been cached.
        if (($response = $this->displayCache($cacheConfig)) instanceof ResponseInterface) {
            return $response;
        }

        $routeFilter = $this->tryToRouteIt($routes);

        $uri = $this->determinePath();

        if ($this->enableFilters) {
            // Start up the filters
            $filters = Services::filters();

            // If any filters were specified within the routes file,
            // we need to ensure it's active for the current request
            if ($routeFilter !== null) {
                $multipleFiltersEnabled = config(Feature::class)->multipleFilters ?? false;
                if ($multipleFiltersEnabled) {
                    $filters->enableFilters($routeFilter, 'before');
                    $filters->enableFilters($routeFilter, 'after');
                } else {
                    // for backward compatibility
                    $filters->enableFilter($routeFilter, 'before');
                    $filters->enableFilter($routeFilter, 'after');
                }
            }

            // Run "before" filters
            $this->benchmark->start('before_filters');
            $possibleResponse = $filters->run($uri, 'before');
            $this->benchmark->stop('before_filters');

            // If a ResponseInterface instance is returned then send it back to the client and stop
            if ($possibleResponse instanceof ResponseInterface) {
                $this->outputBufferingEnd();

                return $possibleResponse;
            }

            if ($possibleResponse instanceof IncomingRequest || $possibleResponse instanceof CLIRequest) {
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

        if ($this->enableFilters) {
            $filters = Services::filters();
            $filters->setResponse($this->response);

            // After filter debug toolbar requires 'total_execution'.
            $this->totalTime = $this->benchmark->getElapsedTime('total_execution');

            // Run "after" filters
            $this->benchmark->start('after_filters');
            $response = $filters->run($uri, 'after');
            $this->benchmark->stop('after_filters');

            if ($response instanceof ResponseInterface) {
                $this->response = $response;
            }
        }

        // Skip unnecessary processing for special Responses.
        if (
            ! $this->response instanceof DownloadResponse
            && ! $this->response instanceof RedirectResponse
        ) {
            // Cache it without the performance metrics replaced
            // so that we can have live speed updates along the way.
            // Must be run after filters to preserve the Response headers.
            $this->pageCache->make($this->request, $this->response);

            // Update the performance metrics
            $body = $this->response->getBody();
            if ($body !== null) {
                $output = $this->displayPerformanceMetrics($body);
                $this->response->setBody($output);
            }

            // Save our current URI as the previous URI in the session
            // for safer, more accurate use with `previous_url()` helper function.
            $this->storePreviousURL(current_url(true));
        }

        unset($uri);

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
     *
     * @return void
     *
     * @deprecated 4.4.0 No longer used. Moved to index.php and spark.
     */
    protected function detectEnvironment()
    {
        // Make sure ENVIRONMENT isn't already set by other means.
        if (! defined('ENVIRONMENT')) {
            define('ENVIRONMENT', env('CI_ENVIRONMENT', 'production'));
        }
    }

    /**
     * Load any custom boot files based upon the current environment.
     *
     * If no boot file exists, we shouldn't continue because something
     * is wrong. At the very least, they should have error reporting setup.
     *
     * @return void
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
     *
     * @return void
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
     * @param CLIRequest|IncomingRequest $request
     *
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get our Request object, (either IncomingRequest or CLIRequest).
     *
     * @return void
     */
    protected function getRequestObject()
    {
        if ($this->request instanceof Request) {
            return;
        }

        if ($this->isPhpCli()) {
            Services::createRequest($this->config, true);
        } else {
            Services::createRequest($this->config);
        }

        $this->request = Services::request();
    }

    /**
     * Get our Response object, and set some default values, including
     * the HTTP protocol version and a default successful response.
     *
     * @return void
     */
    protected function getResponseObject()
    {
        $this->response = Services::response($this->config);

        if ($this->isWeb()) {
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
     *
     * @return void
     */
    protected function forceSecureAccess($duration = 31_536_000)
    {
        if ($this->config->forceGlobalSecureRequests !== true) {
            return;
        }

        force_https($duration, $this->request, $this->response);
    }

    /**
     * Determines if a response has been cached for the given URI.
     *
     * @return false|ResponseInterface
     *
     * @throws Exception
     *
     * @deprecated 4.4.2 The parameter $config is deprecated. No longer used.
     */
    public function displayCache(Cache $config)
    {
        $cachedResponse = $this->pageCache->get($this->request, $this->response);
        if ($cachedResponse instanceof ResponseInterface) {
            $this->response = $cachedResponse;

            $this->totalTime = $this->benchmark->getElapsedTime('total_execution');
            $output          = $this->displayPerformanceMetrics($cachedResponse->getBody());
            $this->response->setBody($output);

            return $this->response;
        }

        return false;
    }

    /**
     * Tells the app that the final output should be cached.
     *
     * @deprecated 4.4.0 Moved to ResponseCache::setTtl(). No longer used.
     *
     * @return void
     */
    public static function cache(int $time)
    {
        static::$cacheTTL = $time;
    }

    /**
     * Caches the full response from the current request. Used for
     * full-page caching for very high performance.
     *
     * @return bool
     *
     * @deprecated 4.4.0 No longer used.
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
     *
     * @deprecated 4.4.0 No longer used.
     */
    protected function generateCacheName(Cache $config): string
    {
        if ($this->request instanceof CLIRequest) {
            return md5($this->request->getPath());
        }

        $uri = clone $this->request->getUri();

        $query = $config->cacheQueryString
            ? $uri->getQuery(is_array($config->cacheQueryString) ? ['only' => $config->cacheQueryString] : [])
            : '';

        return md5($uri->setFragment('')->setQuery($query));
    }

    /**
     * Replaces the elapsed_time tag.
     */
    public function displayPerformanceMetrics(string $output): string
    {
        return str_replace('{elapsed_time}', (string) $this->totalTime, $output);
    }

    /**
     * Try to Route It - As it sounds like, works with the router to
     * match a route against the current URI. If the route is a
     * "redirect route", will also handle the redirect.
     *
     * @param RouteCollectionInterface|null $routes A collection interface to use in place
     *                                              of the config file.
     *
     * @return string|string[]|null Route filters, that is, the filters specified in the routes file
     *
     * @throws RedirectException
     */
    protected function tryToRouteIt(?RouteCollectionInterface $routes = null)
    {
        if ($routes === null) {
            $routes = Services::routes()->loadRoutes();
        }

        // $routes is defined in Config/Routes.php
        $this->router = Services::router($routes, $this->request);

        $path = $this->determinePath();

        $this->benchmark->stop('bootstrap');
        $this->benchmark->start('routing');

        $this->outputBufferingStart();

        $this->controller = $this->router->handle($path);
        $this->method     = $this->router->methodName();

        // If a {locale} segment was matched in the final route,
        // then we need to set the correct locale on our Request.
        if ($this->router->hasLocale()) {
            $this->request->setLocale($this->router->getLocale());
        }

        $this->benchmark->stop('routing');

        // for backward compatibility
        $multipleFiltersEnabled = config(Feature::class)->multipleFilters ?? false;
        if (! $multipleFiltersEnabled) {
            return $this->router->getFilter();
        }

        return $this->router->getFilters();
    }

    /**
     * Determines the path to use for us to try to route to, based
     * on the CLI/IncomingRequest path.
     *
     * @return string
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
     * This is not used now.
     *
     * @return $this
     *
     * @deprecated No longer used.
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
     *
     * @return ResponseInterface|string|void
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
     * @return Controller
     */
    protected function createController()
    {
        assert(is_string($this->controller));

        $class = new $this->controller();
        $class->initController($this->request, $this->response, Services::logger());

        $this->benchmark->stop('controller_constructor');

        return $class;
    }

    /**
     * Runs the controller, allowing for _remap methods to function.
     *
     * CI4 supports three types of requests:
     *  1. Web: URI segments become parameters, sent to Controllers via Routes,
     *      output controlled by Headers to browser
     *  2. PHP CLI: accessed by CLI via php public/index.php, arguments become URI segments,
     *      sent to Controllers via Routes, output varies
     *
     * @param Controller $class
     *
     * @return false|ResponseInterface|string|void
     */
    protected function runController($class)
    {
        // This is a Web request or PHP CLI request
        $params = $this->router->params();

        $output = method_exists($class, '_remap')
            ? $class->_remap($this->method, ...$params)
            : $class->{$this->method}(...$params);

        $this->benchmark->stop('controller');

        return $output;
    }

    /**
     * Displays a 404 Page Not Found error. If set, will try to
     * call the 404Override controller/method that was set in routing config.
     *
     * @return ResponseInterface|void
     */
    protected function display404errors(PageNotFoundException $e)
    {
        // Is there a 404 Override available?
        if ($override = $this->router->get404Override()) {
            $returned = null;

            if ($override instanceof Closure) {
                echo $override($e->getMessage());
            } elseif (is_array($override)) {
                $this->benchmark->start('controller');
                $this->benchmark->start('controller_constructor');

                $this->controller = $override[0];
                $this->method     = $override[1];

                $controller = $this->createController();
                $returned   = $this->runController($controller);
            }

            unset($override);

            $cacheConfig = config(Cache::class);
            $this->gatherOutput($cacheConfig, $returned);

            return $this->response;
        }

        // Display 404 Errors
        $this->response->setStatusCode($e->getCode());

        $this->outputBufferingEnd();

        // Throws new PageNotFoundException and remove exception message on production.
        throw PageNotFoundException::forPageNotFound(
            (ENVIRONMENT !== 'production' || ! $this->isWeb()) ? $e->getMessage() : null
        );
    }

    /**
     * Gathers the script output from the buffer, replaces some execution
     * time tag in the output and displays the debug toolbar, if required.
     *
     * @param Cache|null                    $cacheConfig Deprecated. No longer used.
     * @param ResponseInterface|string|null $returned
     *
     * @deprecated $cacheConfig is deprecated.
     *
     * @return void
     */
    protected function gatherOutput(?Cache $cacheConfig = null, $returned = null)
    {
        $this->output = $this->outputBufferingEnd();

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
     *
     * @return void
     */
    public function storePreviousURL($uri)
    {
        // Ignore CLI requests
        if (! $this->isWeb()) {
            return;
        }
        // Ignore AJAX requests
        if (method_exists($this->request, 'isAJAX') && $this->request->isAJAX()) {
            return;
        }

        // Ignore unroutable responses
        if ($this->response instanceof DownloadResponse || $this->response instanceof RedirectResponse) {
            return;
        }

        // Ignore non-HTML responses
        if (strpos($this->response->getHeaderLine('Content-Type'), 'text/html') === false) {
            return;
        }

        // This is mainly needed during testing...
        if (is_string($uri)) {
            $uri = new URI($uri);
        }

        if (isset($_SESSION)) {
            session()->set('_ci_previous_url', URI::createURIString(
                $uri->getScheme(),
                $uri->getAuthority(),
                $uri->getPath(),
                $uri->getQuery(),
                $uri->getFragment()
            ));
        }
    }

    /**
     * Modifies the Request Object to use a different method if a POST
     * variable called _method is found.
     *
     * @return void
     */
    public function spoofRequestMethod()
    {
        // Only works with POSTED forms
        if (strtolower($this->request->getMethod()) !== 'post') {
            return;
        }

        $method = $this->request->getPost('_method');

        if (empty($method)) {
            return;
        }

        // Only allows PUT, PATCH, DELETE
        if (in_array(strtoupper($method), ['PUT', 'PATCH', 'DELETE'], true)) {
            $this->request = $this->request->setMethod($method);
        }
    }

    /**
     * Sends the output of this request back to the client.
     * This is what they've been waiting for!
     *
     * @return void
     */
    protected function sendResponse()
    {
        $this->response->send();
    }

    /**
     * Exits the application, setting the exit code for CLI-based applications
     * that might be watching.
     *
     * Made into a separate method so that it can be mocked during testing
     * without actually stopping script execution.
     *
     * @param int $code
     *
     * @deprecated 4.4.0 No longer Used. Moved to index.php.
     *
     * @return void
     */
    protected function callExit($code)
    {
        exit($code); // @codeCoverageIgnore
    }

    /**
     * Sets the app context.
     *
     * @phpstan-param 'php-cli'|'web' $context
     *
     * @return $this
     */
    public function setContext(string $context)
    {
        $this->context = $context;

        return $this;
    }

    protected function outputBufferingStart(): void
    {
        $this->bufferLevel = ob_get_level();
        ob_start();
    }

    protected function outputBufferingEnd(): string
    {
        $buffer = '';

        while (ob_get_level() > $this->bufferLevel) {
            $buffer .= ob_get_contents();
            ob_end_clean();
        }

        return $buffer;
    }
}
