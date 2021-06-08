<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use Config\App;
use Config\Services;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Controller Test Trait
 *
 * Provides features that make testing controllers simple and fluent.
 *
 * Example:
 *
 *  $this->withRequest($request)
 *       ->withResponse($response)
 *       ->withURI($uri)
 *       ->withBody($body)
 *       ->controller('App\Controllers\Home')
 *       ->execute('methodName');
 */
trait ControllerTestTrait
{
    /**
     * Controller configuration.
     *
     * @var App
     */
    protected $appConfig;

    /**
     * Request.
     *
     * @var IncomingRequest
     */
    protected $request;

    /**
     * Response.
     *
     * @var Response
     */
    protected $response;

    /**
     * Message logger.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Initialized controller.
     *
     * @var Controller
     */
    protected $controller;

    /**
     * URI of this request.
     *
     * @var string
     */
    protected $uri = 'http://example.com';

    /**
     * Request body.
     *
     * @var string|null
     */
    protected $body;

    /**
     * Initializes required components.
     */
    protected function setUpControllerTestTrait(): void
    {
        // The URL helper is always loaded by the system so ensure it is available.
        helper('url');

        if (empty($this->appConfig)) {
            $this->appConfig = config('App');
        }

        if (! $this->uri instanceof URI) {
            $this->uri = Services::uri($this->appConfig->baseURL ?? 'http://example.com/', false);
        }

        if (empty($this->request)) {
            // Do some acrobatics so we can use the Request service with our own URI
            $tempUri = Services::uri();
            Services::injectMock('uri', $this->uri);

            $this->withRequest(Services::request($this->appConfig, false)->setBody($this->body));

            // Restore the URI service
            Services::injectMock('uri', $tempUri);
        }

        if (empty($this->response)) {
            $this->response = Services::response($this->appConfig, false);
        }

        if (empty($this->logger)) {
            $this->logger = Services::logger();
        }
    }

    /**
     * Loads the specified controller, and generates any needed dependencies.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function controller(string $name)
    {
        if (! class_exists($name)) {
            throw new InvalidArgumentException('Invalid Controller: ' . $name);
        }

        $this->controller = new $name();
        $this->controller->initController($this->request, $this->response, $this->logger);

        return $this;
    }

    /**
     * Runs the specified method on the controller and returns the results.
     *
     * @param string $method
     * @param array  $params
     *
     * @throws InvalidArgumentException
     *
     * @return TestResponse
     */
    public function execute(string $method, ...$params)
    {
        if (! method_exists($this->controller, $method) || ! is_callable([$this->controller, $method])) {
            throw new InvalidArgumentException('Method does not exist or is not callable in controller: ' . $method);
        }

        $response = null;

        try {
            ob_start();
            $response = $this->controller->{$method}(...$params);
        } catch (Throwable $e) {
            $code = $e->getCode();

            // If code is not a valid HTTP status then assume there is an error
            if ($code < 100 || $code >= 600) {
                throw $e;
            }
        } finally {
            $output = ob_get_clean();
        }

        // If the controller returned a view then add it to the output
        if (is_string($response)) {
            $output = is_string($output) ? $output . $response : $response;
        }

        // If the controller did not return a response then start one
        if (! $response instanceof Response) {
            $response = $this->response;
        }

        // Check for output to set or prepend
        // @see \CodeIgniter\CodeIgniter::gatherOutput()
        if (is_string($output)) {
            if (is_string($response->getBody())) {
                $response->setBody($output . $response->getBody());
            } else {
                $response->setBody($output);
            }
        }

        // Check for an overriding code from exceptions
        if (isset($code)) {
            $response->setStatusCode($code);
        }
        // Otherwise ensure there is a status code
        else {
            // getStatusCode() throws for empty codes
            try {
                $response->getStatusCode();
            } catch (HTTPException $e) {
                // If no code has been set then assume success
                $response->setStatusCode(200);
            }
        }

        // Create the result and add the Request for reference
        return (new TestResponse($response))->setRequest($this->request);
    }

    /**
     * Set controller's config, with method chaining.
     *
     * @param mixed $appConfig
     *
     * @return mixed
     */
    public function withConfig($appConfig)
    {
        $this->appConfig = $appConfig;

        return $this;
    }

    /**
     * Set controller's request, with method chaining.
     *
     * @param mixed $request
     *
     * @return mixed
     */
    public function withRequest($request)
    {
        $this->request = $request;

        // Make sure it's available for other classes
        Services::injectMock('request', $request);

        return $this;
    }

    /**
     * Set controller's response, with method chaining.
     *
     * @param mixed $response
     *
     * @return mixed
     */
    public function withResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Set controller's logger, with method chaining.
     *
     * @param mixed $logger
     *
     * @return mixed
     */
    public function withLogger($logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Set the controller's URI, with method chaining.
     *
     * @param string $uri
     *
     * @return mixed
     */
    public function withUri(string $uri)
    {
        $this->uri = new URI($uri);

        return $this;
    }

    /**
     * Set the method's body, with method chaining.
     *
     * @param string|null $body
     *
     * @return mixed
     */
    public function withBody($body)
    {
        $this->body = $body;

        return $this;
    }
}
