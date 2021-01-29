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

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use Config\App;
use Config\Services;
use InvalidArgumentException;
use Throwable;

/**
 * ControllerTester Trait
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
 *       ->run('methodName');
 */
trait ControllerTester
{
	/**
	 * Controller configuration.
	 *
	 * @var BaseConfig
	 */
	protected $appConfig;

	/**
	 * Request.
	 *
	 * @var Request
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
	 * Request or response body.
	 *
	 * @var string
	 */
	protected $body;

	/**
	 * Loads the specified controller, and generates any needed dependencies.
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function controller(string $name)
	{
		if (! class_exists($name))
		{
			throw new InvalidArgumentException('Invalid Controller: ' . $name);
		}

		if (empty($this->appConfig))
		{
			$this->appConfig = new App();
		}

		if (! $this->uri instanceof URI)
		{
			$this->uri = new URI($this->appConfig->baseURL ?? 'http://example.com/');
		}

		if (empty($this->request))
		{
			$this->request = new IncomingRequest($this->appConfig, $this->uri, $this->body, new UserAgent());
		}

		if (empty($this->response))
		{
			$this->response = new Response($this->appConfig);
		}

		if (empty($this->logger))
		{
			$this->logger = Services::logger();
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
	 * @return ControllerResponse
	 */
	public function execute(string $method, ...$params)
	{
		if (! method_exists($this->controller, $method) || ! is_callable([$this->controller, $method]))
		{
			throw new InvalidArgumentException('Method does not exist or is not callable in controller: ' . $method);
		}

		// The URL helper is always loaded by the system
		// so ensure it's available.
		helper('url');

		$result = (new ControllerResponse())
				->setRequest($this->request)
				->setResponse($this->response);

		$response = null;
		try
		{
			ob_start();

			$response = $this->controller->{$method}(...$params);
		}
		catch (Throwable $e)
		{
			$result->response()->setStatusCode($e->getCode());
		}
		finally
		{
			$output = ob_get_clean();

			// If the controller returned a response, use it
			if (isset($response) && $response instanceof Response)
			{
				$result->setResponse($response);
			}

			// check if controller returned a view rather than echoing it
			if (is_string($response))
			{
				$output = $response;
				$result->response()->setBody($output);
				$result->setBody($output);
			}
			elseif (! empty($response) && ! empty($response->getBody()))
			{
				$result->setBody($response->getBody());
			}
			else
			{
				$result->setBody('');
			}
		}

		// If not response code has been sent, assume a success
		if (empty($result->response()->getStatusCode()))
		{
			$result->response()->setStatusCode(200);
		}

		return $result;
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
	 * @param mixed $body
	 *
	 * @return mixed
	 */
	public function withBody($body)
	{
		$this->body = $body;

		return $this;
	}
}
