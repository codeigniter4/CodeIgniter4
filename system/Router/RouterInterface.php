<?php namespace CodeIgniter\Router;

interface RouterInterface
{

	/**
	 * Stores a reference to the RouteCollection object.
	 *
	 * @param RouteCollectionInterface $routes
	 */
	public function __construct(RouteCollectionInterface $routes);

	//--------------------------------------------------------------------

	/**
	 * Scans the URI and attempts to match the current URI to the
	 * one of the defined routes in the RouteCollection.
	 *
	 * @param null $uri
	 *
	 * @return mixed
	 */
	public function handle(string $uri = null);

	//--------------------------------------------------------------------

	/**
	 * Returns the name of the matched controller.
	 *
	 * @return mixed
	 */
	public function controllerName();

	//--------------------------------------------------------------------

	/**
	 * Returns the name of the method to run in the
	 * chosen container.
	 *
	 * @return mixed
	 */
	public function methodName();

	//--------------------------------------------------------------------

	/**
	 * Returns the parameters that have been matched and collected
	 * during the parsing process as an array, ready to send to
	 * call_user_func_array().
	 *
	 * @return mixed
	 */
	public function params();

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
	public function setIndexPage($page);

	//--------------------------------------------------------------------

}