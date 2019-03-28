<?php


/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2014-2019 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 3.0.0
 * @filesource
 */

namespace CodeIgniter\Test;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use Config\App;
use Config\Services;

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

	protected $appConfig;
	protected $request;
	protected $response;
	protected $logger;
	protected $controller;
	protected $uri = 'http://example.com';
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
			throw new \InvalidArgumentException('Invalid Controller: ' . $name);
		}

		if (empty($this->appConfig))
		{
			$this->appConfig = new App();
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
	 * @return \CodeIgniter\Test\ControllerResponse|\InvalidArgumentException
	 */
	public function execute(string $method, ...$params)
	{
		if (! method_exists($this->controller, $method) || ! is_callable([$this->controller, $method]))
		{
			throw new \InvalidArgumentException('Method does not exist or is not callable in controller: ' . $method);
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
		catch (\Throwable $e)
		{
			$result->response()
					->setStatusCode($e->getCode());
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
	 * @param mixed $request
	 *
	 * @return mixed
	 */
	public function withRequest($request)
	{
		$this->request = $request;

		return $this;
	}

	/**
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
