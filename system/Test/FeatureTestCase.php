<?php namespace CodeIgniter\Test;

use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\Request;
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\HTTP\IncomingRequest;
use Config\App;

/**
 * Class FeatureTestCase
 *
 * Provides additional utilities for doing full HTTP testing
 * against your application.
 *
 * @package CodeIgniter\Test
 */
class FeatureTestCase extends CIDatabaseTestCase
{
	/**
	 * If present, will override application
	 * routes when using call().
	 * @var \CodeIgniter\Router\RouteCollection
	 */
	protected $routes;

	/**
	 * Values to be set in the SESSION global
	 * before running the test.
	 * @var array
	 */
	protected $session = [];

	/**
	 * Sets a RouteCollection that will override
	 * the application's route collection.
	 *
	 * @param array $routes
	 *
	 * @return $this
	 */
	protected function withRoutes(array $routes = null)
	{
		$collection = \Config\Services::routes();

		if ($routes)
		{
			foreach ($routes as $route)
			{
				$collection->{$route[0]}($route[1], $route[2]);
			}
		}

		$this->routes = $collection;

		return $this;
	}

	/**
	 * Sets any values that should exist during this session.
	 *
	 * @param array $values
	 *
	 * @return $this
	 */
	public function withSession(array $values)
	{
		$this->session = $values;

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
	 * @param string     $method
	 * @param string     $path
	 * @param array|null $params
	 *
	 * @return \CodeIgniter\Test\FeatureResponse
	 * @throws \CodeIgniter\HTTP\RedirectException
	 * @throws \Exception
	 */
	public function call(string $method, string $path, array $params = null)
	{
		// Simulate having a blank session
		$_SESSION = [];

		$request = $this->setupRequest($method, $path, $params);

		$request = $this->populateGlobals($method, $request, $params);

		$response = $this->app
			->setRequest($request)
			->run($this->routes, true);

		$featureResponse = new FeatureResponse($response);

		return $featureResponse;
	}

	/**
	 * Performs a GET request.
	 *
	 * @param string     $path
	 * @param array|null $params
	 *
	 * @return \CodeIgniter\Test\FeatureResponse
	 * @throws \CodeIgniter\HTTP\RedirectException
	 * @throws \Exception
	 */
	public function get(string $path, array $params = null)
	{
		return $this->call('get', $path, $params);
	}

	/**
	 * Performs a POST request.
	 *
	 * @param string     $path
	 * @param array|null $params
	 *
	 * @return \CodeIgniter\Test\FeatureResponse
	 * @throws \CodeIgniter\HTTP\RedirectException
	 * @throws \Exception
	 */
	public function post(string $path, array $params = null)
	{
		return $this->call('post', $path, $params);
	}

	/**
	 * Performs a PUT request
	 *
	 * @param string     $path
	 * @param array|null $params
	 *
	 * @return \CodeIgniter\Test\FeatureResponse
	 * @throws \CodeIgniter\HTTP\RedirectException
	 * @throws \Exception
	 */
	public function put(string $path, array $params = null)
	{
		return $this->call('put', $path, $params);
	}

	/**
	 * Performss a PATCH request
	 *
	 * @param string     $path
	 * @param array|null $params
	 *
	 * @return \CodeIgniter\Test\FeatureResponse
	 * @throws \CodeIgniter\HTTP\RedirectException
	 * @throws \Exception
	 */
	public function patch(string $path, array $params = null)
	{
		return $this->call('patch', $path, $params);
	}

	/**
	 * Performs a DELETE request.
	 *
	 * @param string     $path
	 * @param array|null $params
	 *
	 * @return \CodeIgniter\Test\FeatureResponse
	 * @throws \CodeIgniter\HTTP\RedirectException
	 * @throws \Exception
	 */
	public function delete(string $path, array $params = null)
	{
		return $this->call('delete', $path, $params);
	}

	/**
	 * Performs an OPTIONS request.
	 *
	 * @param string     $path
	 * @param array|null $params
	 *
	 * @return \CodeIgniter\Test\FeatureResponse
	 * @throws \CodeIgniter\HTTP\RedirectException
	 * @throws \Exception
	 */
	public function options(string $path, array $params = null)
	{
		return $this->call('delete', $path, $params);
	}

	/**
	 * Setup a Request object to use so that CodeIgniter
	 * won't try to auto-populate some of the items.
	 *
	 * @param string      $method
	 * @param string|null $path
	 * @param string|null $params
	 *
	 * @return \CodeIgniter\HTTP\IncomingRequest
	 */
	protected function setupRequest(string $method, string $path = null, string $params = null): IncomingRequest
	{
		$config = config(App::class);
		$uri    = new URI($config->baseURL .'/'. trim($path, '/ '));

		$request = new IncomingRequest($config, clone($uri), $params, new UserAgent());
		$request->uri = $uri;

		$request->setMethod($method);
		$request->setProtocolVersion('1.1');

		return $request;
	}

	/**
	 * Populates the data of our Request with "global" data
	 * relevant to the request, like $_POST data.
	 *
	 * Always populate the GET vars based on the URI.
	 *
	 * @param string                    $method
	 * @param \CodeIgniter\HTTP\Request $request
	 * @param array|null                $params
	 *
	 * @return \CodeIgniter\HTTP\Request
	 */
	protected function populateGlobals(string $method, Request $request, array $params = null)
	{
		$request->setGlobal('get', $this->getPrivateProperty($request->uri, 'query'));
		if ($method !== 'get')
		{
			$request->setGlobal($method, $params);
		}

		$_SESSION = $this->session;

		return $request;
	}
}
