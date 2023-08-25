<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\Exceptions\RedirectException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Router\RouteCollection;
use Config\App;
use Config\Services;
use Exception;
use ReflectionException;

/**
 * Class FeatureTestCase
 *
 * Provides a base class with the trait for doing full HTTP testing
 * against your application.
 *
 * @no-final
 *
 * @deprecated Use FeatureTestTrait instead
 *
 * @codeCoverageIgnore
 *
 * @internal
 */
class FeatureTestCase extends CIUnitTestCase
{
    use DatabaseTestTrait;

    /**
     * Sets a RouteCollection that will override
     * the application's route collection.
     *
     * Example routes:
     * [
     *    ['get', 'home', 'Home::index']
     * ]
     *
     * @param array $routes
     *
     * @return $this
     */
    protected function withRoutes(?array $routes = null)
    {
        $collection = Services::routes();

        if ($routes) {
            $collection->resetRoutes();

            foreach ($routes as $route) {
                $collection->{$route[0]}($route[1], $route[2]);
            }
        }

        $this->routes = $collection;

        return $this;
    }

    /**
     * Sets any values that should exist during this session.
     *
     * @param array|null $values Array of values, or null to use the current $_SESSION
     *
     * @return $this
     */
    public function withSession(?array $values = null)
    {
        $this->session = $values ?? $_SESSION;

        return $this;
    }

    /**
     * Set request's headers
     *
     * Example of use
     * withHeaders([
     *  'Authorization' => 'Token'
     * ])
     *
     * @param array $headers Array of headers
     *
     * @return $this
     */
    public function withHeaders(array $headers = [])
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Set the format the request's body should have.
     *
     * @param string $format The desired format. Currently supported formats: xml, json
     *
     * @return $this
     */
    public function withBodyFormat(string $format)
    {
        $this->bodyFormat = $format;

        return $this;
    }

    /**
     * Set the raw body for the request
     *
     * @param mixed $body
     *
     * @return $this
     */
    public function withBody($body)
    {
        $this->requestBody = $body;

        return $this;
    }

    /**
     * Don't run any events while running this test.
     *
     * @return $this
     */
    public function skipEvents()
    {
        Events::simulate(true);

        return $this;
    }

    /**
     * Calls a single URI, executes it, and returns a FeatureResponse
     * instance that can be used to run many assertions against.
     *
     * @return FeatureResponse
     */
    public function call(string $method, string $path, ?array $params = null)
    {
        $buffer = \ob_get_level();

        // Clean up any open output buffers
        // not relevant to unit testing
        if (\ob_get_level() > 0 && (! isset($this->clean) || $this->clean === true)) {
            \ob_end_clean(); // @codeCoverageIgnore
        }

        // Simulate having a blank session
        $_SESSION                  = [];
        $_SERVER['REQUEST_METHOD'] = $method;

        $request = $this->setupRequest($method, $path);
        $request = $this->setupHeaders($request);
        $request = $this->populateGlobals($method, $request, $params);
        $request = $this->setRequestBody($request);

        // Initialize the RouteCollection
        if (! $routes = $this->routes) {
            $routes = Services::routes()->loadRoutes();
        }

        $routes->setHTTPVerb($method);

        // Make sure any other classes that might call the request
        // instance get the right one.
        Services::injectMock('request', $request);

        // Make sure filters are reset between tests
        Services::injectMock('filters', Services::filters(null, false));

        $response = $this->app
            ->setContext('web')
            ->setRequest($request)
            ->run($routes, true);

        $output = \ob_get_contents();
        if (empty($response->getBody()) && ! empty($output)) {
            $response->setBody($output);
        }

        // Reset directory if it has been set
        Services::router()->setDirectory(null);

        // Ensure the output buffer is identical so no tests are risky
        while (\ob_get_level() > $buffer) {
            \ob_end_clean(); // @codeCoverageIgnore
        }

        while (\ob_get_level() < $buffer) {
            \ob_start(); // @codeCoverageIgnore
        }

        return new FeatureResponse($response);
    }

    /**
     * Performs a GET request.
     *
     * @return FeatureResponse
     *
     * @throws Exception
     * @throws RedirectException
     */
    public function get(string $path, ?array $params = null)
    {
        return $this->call('get', $path, $params);
    }

    /**
     * Performs a POST request.
     *
     * @return FeatureResponse
     *
     * @throws Exception
     * @throws RedirectException
     */
    public function post(string $path, ?array $params = null)
    {
        return $this->call('post', $path, $params);
    }

    /**
     * Performs a PUT request
     *
     * @return FeatureResponse
     *
     * @throws Exception
     * @throws RedirectException
     */
    public function put(string $path, ?array $params = null)
    {
        return $this->call('put', $path, $params);
    }

    /**
     * Performss a PATCH request
     *
     * @return FeatureResponse
     *
     * @throws Exception
     * @throws RedirectException
     */
    public function patch(string $path, ?array $params = null)
    {
        return $this->call('patch', $path, $params);
    }

    /**
     * Performs a DELETE request.
     *
     * @return FeatureResponse
     *
     * @throws Exception
     * @throws RedirectException
     */
    public function delete(string $path, ?array $params = null)
    {
        return $this->call('delete', $path, $params);
    }

    /**
     * Performs an OPTIONS request.
     *
     * @return FeatureResponse
     *
     * @throws Exception
     * @throws RedirectException
     */
    public function options(string $path, ?array $params = null)
    {
        return $this->call('options', $path, $params);
    }

    /**
     * Setup a Request object to use so that CodeIgniter
     * won't try to auto-populate some of the items.
     */
    protected function setupRequest(string $method, ?string $path = null): IncomingRequest
    {
        $config = config(App::class);
        $uri    = new URI(rtrim($config->baseURL, '/') . '/' . trim($path, '/ '));

        $request      = new IncomingRequest($config, clone $uri, null, new UserAgent());
        $request->uri = $uri;

        $request->setMethod($method);
        $request->setProtocolVersion('1.1');

        if ($config->forceGlobalSecureRequests) {
            $_SERVER['HTTPS'] = 'test';
        }

        return $request;
    }

    /**
     * Setup the custom request's headers
     *
     * @return IncomingRequest
     */
    protected function setupHeaders(IncomingRequest $request)
    {
        foreach ($this->headers as $name => $value) {
            $request->setHeader($name, $value);
        }

        return $request;
    }

    /**
     * Populates the data of our Request with "global" data
     * relevant to the request, like $_POST data.
     *
     * Always populate the GET vars based on the URI.
     *
     * @param CLIRequest|IncomingRequest $request
     *
     * @return CLIRequest|IncomingRequest
     *
     * @throws ReflectionException
     */
    protected function populateGlobals(string $method, $request, ?array $params = null)
    {
        // $params should set the query vars if present,
        // otherwise set it from the URL.
        $get = ! empty($params) && $method === 'get'
            ? $params
            : $this->getPrivateProperty($request->getUri(), 'query');

        $request->setGlobal('get', $get);
        if ($method !== 'get') {
            $request->setGlobal($method, $params);
        }

        $request->setGlobal('request', $params);

        $_SESSION = $this->session ?? [];

        return $request;
    }

    /**
     * Set the request's body formatted according to the value in $this->bodyFormat.
     * This allows the body to be formatted in a way that the controller is going to
     * expect as in the case of testing a JSON or XML API.
     *
     * @param CLIRequest|IncomingRequest $request
     * @param array|null                 $params  The parameters to be formatted and put in the body. If this is empty, it will get the
     *                                            what has been loaded into the request global of the request class.
     *
     * @return CLIRequest|IncomingRequest
     */
    protected function setRequestBody($request, ?array $params = null)
    {
        if (isset($this->requestBody) && $this->requestBody !== '') {
            $request->setBody($this->requestBody);

            return $request;
        }

        if (isset($this->bodyFormat) && $this->bodyFormat !== '') {
            if (empty($params)) {
                $params = $request->fetchGlobal('request');
            }
            $formatMime = '';
            if ($this->bodyFormat === 'json') {
                $formatMime = 'application/json';
            } elseif ($this->bodyFormat === 'xml') {
                $formatMime = 'application/xml';
            }
            if (! empty($formatMime) && ! empty($params)) {
                $formatted = Services::format()->getFormatter($formatMime)->format($params);
                $request->setBody($formatted);
                $request->setHeader('Content-Type', $formatMime);
            }
        }

        return $request;
    }
}
