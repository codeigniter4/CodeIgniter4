<?php namespace Tests\Support\Helpers;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use Config\App;

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
			$this->request = new IncomingRequest($this->appConfig, $this->uri, $this->body);
		}

		if (empty($this->response))
		{
			$this->response = new Response($this->appConfig);
		}

		$this->controller = new $name($this->request, $this->response);

		return $this;
	}

	/**
	 * Runs the specified method on the controller and returns the results.
	 *
	 * @param string $method
	 * @param array  $params
	 *
	 * @return \Tests\Support\Helpers\ControllerResponse
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

			// If the controller returned a redirect response
			// then we need to use that...
			if (isset($response) && $response instanceof Response)
			{
				$result->setResponse($response);
			}

			$result->response()->setBody($output);
			$result->setBody($output);
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
