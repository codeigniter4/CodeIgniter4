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
use CodeIgniter\HTTP\Exceptions\RedirectException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\SiteURI;
use CodeIgniter\HTTP\URI;
use Config\App;
use Config\Services;
use Exception;
use ReflectionException;

/**
 * Trait FeatureTestTrait
 *
 * Provides additional utilities for doing full HTTP testing
 * against your application in trait format.
 */
trait FeatureTestTrait
{
    /**
     * Sets a RouteCollection that will override
     * the application's route collection.
     *
     * Example routes:
     * [
     *    ['get', 'home', 'Home::index']
     * ]
     *
     * @param array|null $routes Array to set routes
     *
     * @return $this
     */
    protected function withRoutes(?array $routes = null)
    {
        $collection = Services::routes();

        if ($routes) {
            $collection->resetRoutes();

            foreach ($routes as $route) {
                if (isset($route[3])) {
                    $collection->{$route[0]}($route[1], $route[2], $route[3]);
                } else {
                    $collection->{$route[0]}($route[1], $route[2]);
                }
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
     * @param string $body
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
     * Calls a single URI, executes it, and returns a TestResponse
     * instance that can be used to run many assertions against.
     *
     * @param string $method HTTP verb
     *
     * @return TestResponse
     */
    public function call(string $method, string $path, ?array $params = null)
    {
        // Simulate having a blank session
        $_SESSION                  = [];
        $_SERVER['REQUEST_METHOD'] = $method;

        $request = $this->setupRequest($method, $path);
        $request = $this->setupHeaders($request);
        $request = $this->populateGlobals($method, $request, $params);
        $request = $this->setRequestBody($request, $params);

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

        // Make sure validation is reset between tests
        Services::injectMock('validation', Services::validation(null, false));

        $response = $this->app
            ->setContext('web')
            ->setRequest($request)
            ->run($routes, true);

        // Reset directory if it has been set
        Services::router()->setDirectory(null);

        return new TestResponse($response);
    }

    /**
     * Performs a GET request.
     *
     * @param string $path URI path relative to baseURL. May include query.
     *
     * @return TestResponse
     *
     * @throws RedirectException
     * @throws Exception
     */
    public function get(string $path, ?array $params = null)
    {
        return $this->call('get', $path, $params);
    }

    /**
     * Performs a POST request.
     *
     * @return TestResponse
     *
     * @throws RedirectException
     * @throws Exception
     */
    public function post(string $path, ?array $params = null)
    {
        return $this->call('post', $path, $params);
    }

    /**
     * Performs a PUT request
     *
     * @return TestResponse
     *
     * @throws RedirectException
     * @throws Exception
     */
    public function put(string $path, ?array $params = null)
    {
        return $this->call('put', $path, $params);
    }

    /**
     * Performss a PATCH request
     *
     * @return TestResponse
     *
     * @throws RedirectException
     * @throws Exception
     */
    public function patch(string $path, ?array $params = null)
    {
        return $this->call('patch', $path, $params);
    }

    /**
     * Performs a DELETE request.
     *
     * @return TestResponse
     *
     * @throws RedirectException
     * @throws Exception
     */
    public function delete(string $path, ?array $params = null)
    {
        return $this->call('delete', $path, $params);
    }

    /**
     * Performs an OPTIONS request.
     *
     * @return TestResponse
     *
     * @throws RedirectException
     * @throws Exception
     */
    public function options(string $path, ?array $params = null)
    {
        return $this->call('options', $path, $params);
    }

    /**
     * Setup a Request object to use so that CodeIgniter
     * won't try to auto-populate some of the items.
     *
     * @param string $method HTTP verb
     */
    protected function setupRequest(string $method, ?string $path = null): IncomingRequest
    {
        $config = config(App::class);
        $uri    = new SiteURI($config);

        // $path may have a query in it
        $path  = URI::removeDotSegments($path);
        $parts = explode('?', $path);
        $path  = $parts[0];
        $query = $parts[1] ?? '';

        $superglobals = Services::superglobals();
        $superglobals->setServer('QUERY_STRING', $query);

        $uri->setPath($path);
        $uri->setQuery($query);

        Services::injectMock('uri', $uri);

        $request = Services::request($config, false);

        $request->setMethod($method);
        $request->setProtocolVersion('1.1');

        if ($config->forceGlobalSecureRequests) {
            $_SERVER['HTTPS'] = 'test';
            $server           = $request->getServer();
            $server['HTTPS']  = 'test';
            $request->setGlobal('server', $server);
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
        if (! empty($this->headers)) {
            foreach ($this->headers as $name => $value) {
                $request->setHeader($name, $value);
            }
        }

        return $request;
    }

    /**
     * Populates the data of our Request with "global" data
     * relevant to the request, like $_POST data.
     *
     * Always populate the GET vars based on the URI.
     *
     * @param string $method HTTP verb
     *
     * @return Request
     *
     * @throws ReflectionException
     */
    protected function populateGlobals(string $method, Request $request, ?array $params = null)
    {
        // $params should set the query vars if present,
        // otherwise set it from the URL.
        $get = (! empty($params) && $method === 'get')
            ? $params
            : $this->getPrivateProperty($request->getUri(), 'query');

        $request->setGlobal('get', $get);

        if ($method === 'get') {
            $request->setGlobal('request', $request->fetchGlobal('get'));
        }

        if ($method === 'post') {
            $request->setGlobal($method, $params);
            $request->setGlobal(
                'request',
                $request->fetchGlobal('post') + $request->fetchGlobal('get')
            );
        }

        $_SESSION = $this->session ?? [];

        return $request;
    }

    /**
     * Set the request's body formatted according to the value in $this->bodyFormat.
     * This allows the body to be formatted in a way that the controller is going to
     * expect as in the case of testing a JSON or XML API.
     *
     * @param array|null $params The parameters to be formatted and put in the body.
     */
    protected function setRequestBody(Request $request, ?array $params = null): Request
    {
        if ($this->requestBody !== '') {
            $request->setBody($this->requestBody);
        }

        if ($this->bodyFormat !== '') {
            $formatMime = '';
            if ($this->bodyFormat === 'json') {
                $formatMime = 'application/json';
            } elseif ($this->bodyFormat === 'xml') {
                $formatMime = 'application/xml';
            }

            if ($formatMime !== '') {
                $request->setHeader('Content-Type', $formatMime);
            }

            if ($params !== null && $formatMime !== '') {
                $formatted = Services::format()->getFormatter($formatMime)->format($params);
                // "withBodyFormat() and $params of call()" has higher priority than withBody().
                $request->setBody($formatted);
            }
        }

        return $request;
    }
}
