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
use CodeIgniter\Exceptions\LogicException;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\Filters;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\Exceptions\FormRequestException;
use CodeIgniter\HTTP\Exceptions\RedirectException;
use CodeIgniter\HTTP\FormRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Method;
use CodeIgniter\HTTP\NonBufferedResponseInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponsableInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Router\CallableParamClassifier;
use CodeIgniter\Router\ParamKind;
use CodeIgniter\Router\RouteCollectionInterface;
use CodeIgniter\Router\Router;
use Config\App;
use Config\Cache;
use Config\Feature;
use Config\Services;
use Locale;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
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
    public const CI_VERSION = '4.8.0-dev';

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
     * @var ResponseInterface|null
     */
    protected $response;

    /**
     * Router to use.
     *
     * @var Router|null
     */
    protected $router;

    /**
     * Controller to use.
     *
     * @var Closure|string|null
     */
    protected $controller;

    /**
     * Controller method to invoke.
     *
     * @var string|null
     */
    protected $method;

    /**
     * Output handler to use.
     *
     * @var string|null
     */
    protected $output;

    /**
     * Context
     *  web:     Invoked by HTTP request
     *  php-cli: Invoked by CLI via `php public/index.php`
     *
     * @var 'php-cli'|'web'|null
     */
    protected ?string $context = null;

    /**
     * Whether to enable Control Filters.
     */
    protected bool $enableFilters = true;

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
        // Set default locale on the server
        Locale::setDefault($this->config->defaultLocale);

        // Set default timezone on the server
        date_default_timezone_set($this->config->appTimezone);
    }

    /**
     * Reset request-specific state for worker mode.
     * Clears all request/response data to prepare for the next request.
     */
    public function resetForWorkerMode(): void
    {
        $this->request    = null;
        $this->response   = null;
        $this->router     = null;
        $this->controller = null;
        $this->method     = null;
        $this->output     = null;

        // Reset timing
        $this->startTime = null;
        $this->totalTime = 0;
    }

    /**
     * Launch the application!
     *
     * This is "the loop" if you will. The main entry point into the script
     * that gets the required class instances, fires off the filters,
     * tries to route the response, loads the controller and generally
     * makes all the pieces work together.
     *
     * @param bool $returnResponse Used for testing purposes only.
     *
     * @return ResponseInterface|null
     */
    public function run(?RouteCollectionInterface $routes = null, bool $returnResponse = false)
    {
        if ($this->context === null) {
            throw new LogicException(
                'Context must be set before run() is called. If you are upgrading from 4.1.x, '
                . 'you need to merge `public/index.php` and `spark` file from `vendor/codeigniter4/framework`.',
            );
        }

        $this->pageCache->setTtl(0);
        $this->bufferLevel = ob_get_level();

        $this->startBenchmark();

        $this->getRequestObject();
        $this->getResponseObject();

        Events::trigger('pre_system');

        $this->benchmark->stop('bootstrap');

        $this->benchmark->start('required_before_filters');
        // Start up the filters
        $filters = Services::filters();
        // Run required before filters
        $possibleResponse = $this->runRequiredBeforeFilters($filters);

        // If a ResponseInterface instance is returned then send it back to the client and stop
        if ($possibleResponse instanceof ResponseInterface) {
            $this->response = $possibleResponse;
        } else {
            try {
                $this->response = $this->handleRequest($routes);
            } catch (ResponsableInterface $e) {
                $this->outputBufferingEnd();

                $this->response = $e->getResponse();
            } catch (PageNotFoundException $e) {
                $this->response = $this->display404errors($e);
            } catch (Throwable $e) {
                $this->outputBufferingEnd();

                throw $e;
            }
        }

        $this->runRequiredAfterFilters($filters);

        // Is there a post-system event?
        Events::trigger('post_system');

        if ($returnResponse) {
            return $this->response;
        }

        $this->sendResponse();

        return null;
    }

    private function runRequiredBeforeFilters(Filters $filters): ?ResponseInterface
    {
        $possibleResponse = $filters->runRequired('before');
        $this->benchmark->stop('required_before_filters');

        // If a ResponseInterface instance is returned then send it back to the client and stop
        if ($possibleResponse instanceof ResponseInterface) {
            return $possibleResponse;
        }

        return null;
    }

    private function runRequiredAfterFilters(Filters $filters): void
    {
        $filters->setResponse($this->response);

        $this->benchmark->start('required_after_filters');
        $response = $filters->runRequired('after');
        $this->benchmark->stop('required_after_filters');

        if ($response instanceof ResponseInterface) {
            $this->response = $response;
        }
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
     */
    protected function handleRequest(?RouteCollectionInterface $routes, ?Cache $cacheConfig = null)
    {
        if (func_num_args() > 1) {
            // @todo v4.8.0: Remove this check and the $cacheConfig parameter from the method signature.
            @trigger_error(sprintf('Since v4.8.0, the $cacheConfig parameter of %s is deprecated and no longer used.', __METHOD__), E_USER_DEPRECATED);
        }

        if ($this->request instanceof IncomingRequest && $this->request->getMethod() === 'CLI') {
            return $this->response->setStatusCode(405)->setBody('Method Not Allowed');
        }

        $routeFilters = $this->tryToRouteIt($routes);

        // $uri is URL-encoded.
        $uri = $this->request->getPath();

        if ($this->enableFilters) {
            /** @var Filters $filters */
            $filters = service('filters');

            // If any filters were specified within the routes file,
            // we need to ensure it's active for the current request
            if ($routeFilters !== null) {
                $filters->enableFilters($routeFilters, 'before');

                $oldFilterOrder = config(Feature::class)->oldFilterOrder ?? false; // @phpstan-ignore nullCoalesce.property
                if (! $oldFilterOrder) {
                    $routeFilters = array_reverse($routeFilters);
                }

                $filters->enableFilters($routeFilters, 'after');
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

        // If startController returned a Response (from an attribute or Closure), use it
        if ($returned instanceof ResponseInterface) {
            $this->gatherOutput($returned);
        }
        // Closure controller has run in startController() - benchmarks were
        // stopped there as well.
        elseif (! is_callable($this->controller)) {
            $controller = $this->createController();

            if (! method_exists($controller, '_remap') && ! is_callable([$controller, $this->method], false)) {
                throw PageNotFoundException::forMethodNotFound($this->method);
            }

            // Is there a "post_controller_constructor" event?
            Events::trigger('post_controller_constructor');

            $returned = $this->runController($controller);
        }

        // If $returned is a string, then the controller output something,
        // probably a view, instead of echoing it directly. Send it along
        // so it can be used with the output.
        $this->gatherOutput($returned);

        if ($this->enableFilters) {
            /** @var Filters $filters */
            $filters = service('filters');
            $filters->setResponse($this->response);

            // Run "after" filters
            $this->benchmark->start('after_filters');
            $response = $filters->run($uri, 'after');
            $this->benchmark->stop('after_filters');

            if ($response instanceof ResponseInterface) {
                $this->response = $response;
            }
        }

        // Execute controller attributes' after() methods AFTER framework filters
        if ((config('Routing')->useControllerAttributes ?? true) === true) { // @phpstan-ignore nullCoalesce.property
            $this->benchmark->start('route_attributes_after');
            $this->response = $this->router->executeAfterAttributes($this->request, $this->response);
            $this->benchmark->stop('route_attributes_after');
        }

        // Skip unnecessary processing for special Responses.
        if (
            ! $this->response instanceof NonBufferedResponseInterface
            && ! $this->response instanceof RedirectResponse
        ) {
            // Save our current URI as the previous URI in the session
            // for safer, more accurate use with `previous_url()` helper function.
            $this->storePreviousURL(current_url(true));
        }

        unset($uri);

        return $this->response;
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
     *
     * @internal Used for testing purposes only.
     * @testTag
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
            $this->spoofRequestMethod();

            return;
        }

        if ($this->isPhpCli()) {
            Services::createRequest($this->config, true);
        } else {
            Services::createRequest($this->config);
        }

        $this->request = service('request');

        $this->spoofRequestMethod();
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
     * Returns an array with our basic performance stats collected.
     */
    public function getPerformanceStats(): array
    {
        // After filter debug toolbar requires 'total_execution'.
        $this->totalTime = $this->benchmark->getElapsedTime('total_execution');

        return [
            'startTime' => $this->startTime,
            'totalTime' => $this->totalTime,
        ];
    }

    /**
     * Try to Route It - As it sounds like, works with the router to
     * match a route against the current URI. If the route is a
     * "redirect route", will also handle the redirect.
     *
     * @param RouteCollectionInterface|null $routes A collection interface to use in place
     *                                              of the config file.
     *
     * @return list<string>|string|null Route filters, that is, the filters specified in the routes file
     *
     * @throws RedirectException
     */
    protected function tryToRouteIt(?RouteCollectionInterface $routes = null)
    {
        $this->benchmark->start('routing');

        if (! $routes instanceof RouteCollectionInterface) {
            $routes = service('routes')->loadRoutes();
        }

        // $routes is defined in Config/Routes.php
        $this->router = Services::router($routes, $this->request);

        // $uri is URL-encoded.
        $uri = $this->request->getPath();

        $this->outputBufferingStart();

        $this->controller = $this->router->handle($uri);
        $this->method     = $this->router->methodName();

        // If a {locale} segment was matched in the final route,
        // then we need to set the correct locale on our Request.
        if ($this->router->hasLocale()) {
            $this->request->setLocale($this->router->getLocale());
        }

        $this->benchmark->stop('routing');

        return $this->router->getFilters();
    }

    /**
     * Now that everything has been setup, this method attempts to run the
     * controller method and make the script go. If it's not able to, will
     * show the appropriate Page Not Found error.
     *
     * @return ResponseInterface|string|null
     */
    protected function startController()
    {
        $this->benchmark->start('controller');
        $this->benchmark->start('controller_constructor');

        // Is it routed to a Closure?
        if (is_object($this->controller) && ($this->controller::class === 'Closure')) {
            $controller = $this->controller;

            try {
                $resolved = $this->resolveCallableParams(new ReflectionFunction($controller), $this->router->params());

                return $controller(...$resolved);
            } finally {
                $this->benchmark->stop('controller_constructor');
                $this->benchmark->stop('controller');
            }
        }

        // No controller specified - we don't know what to do now.
        if (! isset($this->controller)) {
            throw PageNotFoundException::forEmptyController();
        }

        // Try to autoload the class
        if (
            ! class_exists($this->controller, true)
            || ($this->method[0] === '_' && $this->method !== '__invoke')
        ) {
            throw PageNotFoundException::forControllerNotFound($this->controller, $this->method);
        }

        // Execute route attributes' before() methods
        // This runs after routing/validation but BEFORE expensive controller instantiation
        if ((config('Routing')->useControllerAttributes ?? true) === true) { // @phpstan-ignore nullCoalesce.property
            $this->benchmark->start('route_attributes_before');
            $attributeResponse = $this->router->executeBeforeAttributes($this->request);
            $this->benchmark->stop('route_attributes_before');

            // If attribute returns a Response, short-circuit
            if ($attributeResponse instanceof ResponseInterface) {
                $this->benchmark->stop('controller_constructor');
                $this->benchmark->stop('controller');

                return $attributeResponse;
            }

            // If attribute returns a modified Request, use it
            if ($attributeResponse instanceof RequestInterface) {
                $this->request = $attributeResponse;
            }
        }

        return null;
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

        // The controller method param types may not be string.
        // So cannot set `declare(strict_types=1)` in this file.
        try {
            if (method_exists($class, '_remap')) {
                // FormRequest injection is not supported for _remap() because its
                // signature is fixed to ($method, ...$params). Instantiate the
                // FormRequest manually inside _remap() if needed.
                $output = $class->_remap($this->method, ...$params);
            } else {
                $resolved = $this->resolveMethodParams($class, $this->method, $params);
                $output   = $class->{$this->method}(...$resolved);
            }
        } finally {
            $this->benchmark->stop('controller');
        }

        return $output;
    }

    /**
     * Resolves the final parameter list for a controller method call.
     *
     * @param list<string> $routeParams URI segments from the router.
     *
     * @return list<mixed>
     */
    private function resolveMethodParams(object $class, string $method, array $routeParams): array
    {
        return $this->resolveCallableParams(new ReflectionMethod($class, $method), $routeParams);
    }

    /**
     * Shared FormRequest resolver for both controller methods and closures.
     *
     * Builds a sequential positional argument list for the call site.
     * The supported signature shape is: required scalar route params first,
     * then the FormRequest, then optional scalar params.
     *
     * - FormRequest subclasses are instantiated, authorized, and validated
     *   before being injected.
     * - Variadic non-FormRequest parameters consume all remaining URI segments.
     * - Scalar non-FormRequest parameters consume one URI segment each.
     * - When route segments run out, a required non-FormRequest parameter stops
     *   iteration so PHP throws an ArgumentCountError on the call site.
     * - Optional non-FormRequest parameters with no remaining segment are omitted
     *   from the list; PHP then applies their declared default values.
     *
     * @param list<string> $routeParams URI segments from the router.
     *
     * @return list<mixed>
     */
    private function resolveCallableParams(ReflectionFunctionAbstract $reflection, array $routeParams): array
    {
        $resolved   = [];
        $routeIndex = 0;

        foreach ($reflection->getParameters() as $param) {
            [$kind, $formRequestClass] = CallableParamClassifier::classify($param);

            switch ($kind) {
                case ParamKind::FormRequest:
                    // Inject FormRequest subclasses regardless of position.
                    $resolved[] = $this->resolveFormRequest($formRequestClass);

                    continue 2;

                case ParamKind::Variadic:
                    // Consume all remaining route segments.
                    while (array_key_exists($routeIndex, $routeParams)) {
                        $resolved[] = $routeParams[$routeIndex++];
                    }
                    break 2;

                case ParamKind::Scalar:
                    // Consume the next route segment if one is available.
                    if (array_key_exists($routeIndex, $routeParams)) {
                        $resolved[] = $routeParams[$routeIndex++];

                        continue 2;
                    }

                    // No more route segments. Required params stop iteration so
                    // that PHP throws an ArgumentCountError on the call site.
                    // Optional params are omitted - PHP then applies their
                    // declared default value.
                    if (! $param->isOptional()) {
                        break 2;
                    }
            }
        }

        return $resolved;
    }

    /**
     * Instantiates, authorizes, and validates a FormRequest class.
     *
     * If authorization or validation fails, the FormRequest returns a
     * ResponseInterface. The framework wraps it in a FormRequestException
     * (which implements ResponsableInterface) so the response is sent
     * without reaching the controller method.
     *
     * @param class-string<FormRequest> $className
     */
    private function resolveFormRequest(string $className): FormRequest
    {
        $formRequest = new $className($this->request);
        $response    = $formRequest->resolveRequest();

        if ($response !== null) {
            throw new FormRequestException($response);
        }

        return $formRequest;
    }

    /**
     * Displays a 404 Page Not Found error. If set, will try to
     * call the 404Override controller/method that was set in routing config.
     *
     * @return ResponseInterface|void
     */
    protected function display404errors(PageNotFoundException $e)
    {
        $this->response->setStatusCode($e->getCode());

        // Is there a 404 Override available?
        $override = $this->router->get404Override();

        if ($override !== null) {
            $returned = null;

            if ($override instanceof Closure) {
                echo $override($e->getMessage());
            } elseif (is_array($override)) {
                $this->benchmark->start('controller');
                $this->benchmark->start('controller_constructor');

                $this->controller = $override[0];
                $this->method     = $override[1];

                $controller = $this->createController();

                $returned = $controller->{$this->method}($e->getMessage());

                $this->benchmark->stop('controller');
            }

            unset($override);

            $this->gatherOutput($returned);

            return $this->response;
        }

        $this->outputBufferingEnd();

        // Throws new PageNotFoundException and remove exception message on production.
        throw PageNotFoundException::forPageNotFound(
            (ENVIRONMENT !== 'production' || ! $this->isWeb()) ? $e->getMessage() : null,
        );
    }

    /**
     * Gathers the script output from the buffer, replaces some execution
     * time tag in the output and displays the debug toolbar, if required.
     *
     * @param ResponseInterface|string|null $returned
     *
     * @return void
     */
    protected function gatherOutput($returned = null)
    {
        $this->output = $this->outputBufferingEnd();

        if ($returned instanceof NonBufferedResponseInterface) {
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
        if ($this->response instanceof NonBufferedResponseInterface || $this->response instanceof RedirectResponse) {
            return;
        }

        // Ignore non-HTML responses
        if (! str_contains($this->response->getHeaderLine('Content-Type'), 'text/html')) {
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
                $uri->getFragment(),
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
        if ($this->request->getMethod() !== Method::POST) {
            return;
        }

        $method = $this->request->getPost('_method');

        if ($method === null) {
            return;
        }

        // Only allows PUT, PATCH, DELETE
        if (in_array($method, [Method::PUT, Method::PATCH, Method::DELETE], true)) {
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
     * Sets the app context.
     *
     * @param 'php-cli'|'web' $context
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
