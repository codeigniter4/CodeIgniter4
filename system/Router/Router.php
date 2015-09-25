<?php namespace CodeIgniter\Router;

class Router implements RouterInterface
{
	/**
	 * A RouteCollection instance.
	 *
	 * @var RouteCollectionInterface
	 */
	protected $collection;

	protected $controller;

	protected $method;

	protected $params = [];

	protected $indexPage = 'index.php';

	protected $uriProtocol = 'REQUEST_URI';

	//--------------------------------------------------------------------

	/**
	 * Stores a reference to the RouteCollection object.
	 *
	 * @param RouteCollectionInterface $routes
	 */
	public function __construct(RouteCollectionInterface $routes)
	{
		$this->collection = $routes;

		$this->controller = $this->collection->defaultController();
		$this->method     = $this->collection->defaultMethod();
	}

	//--------------------------------------------------------------------

	/**
	 * Scans the URI and attempts to match the current URI to the
	 * one of the defined routes in the RouteCollection.
	 *
	 * @param null $uri
	 *
	 * @return mixed
	 */
	public function handle($uri = null)
	{
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the name of the matched controller.
	 *
	 * @return mixed
	 */
	public function controllerName()
	{
		return $this->controller;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the name of the method to run in the
	 * chosen container.
	 *
	 * @return mixed
	 */
	public function methodName()
	{
		return $this->method;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the parameters that have been matched and collected
	 * during the parsing process as an array, ready to send to
	 * call_user_func_array().
	 *
	 * @return mixed
	 */
	public function params()
	{
		return $this->params;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the value that should be used to match the index.php file. Defaults
	 * to index.php but this allows you to modify it in case your are using
	 * something like mod_rewrite to remove the page. This allows you to set
	 * it a blank.
	 *
	 * @param $page
	 *
	 * @return mixed
	 */
	public function setIndexPage($page)
	{
		$this->indexPage = $page;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the method used to determine the current page. Valid options are:
	 *
	 *  - REQUEST_URI
	 *  - QUERY_STRING  // Expects a var of $_GET['_url'] to contain the URL
	 *  - PATH_INFO
	 *
	 * @param $protocol
	 *
	 * @return mixed
	 */
	public function setURIProtocol($protocol)
	{
		if ( ! in_array($protocol, ['REQUEST_URI', 'QUERY_STRING', 'PATH_INFO']))
		{
			throw new \InvalidArgumentException('The URI Protocol is not one of the valid options.');
		}

		$this->uriProtocol = $protocol;

		return $this;
	}

	//--------------------------------------------------------------------

}