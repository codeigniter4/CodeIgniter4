<?php namespace CodeIgniter\Router;

/**
 * Interface RouteCollectionInterface
 *
 * A Route Collection's sole job is to hold a series of routes. The required
 * number of methods is kept very small on purpose, but implementors may
 * add a number of additional methods to customize how the routes are defined.
 *
 * The RouteCollection provides the Router with the routes so that it can determine
 * which controller should be ran.
 *
 * @package CodeIgniter\Router
 */
interface RouteCollectionInterface
{

	/**
	 * Adds a single route to the collection.
	 *
	 * @param       $route
	 * @param       $map
	 * @param       $method
	 *
	 * @return mixed
	 */
	public function add($route, $map, $method = 'get');

	//--------------------------------------------------------------------

	/**
	 * Adds an array of routes to the class all at once. This can be used
	 * by less complex routing systems to add all routes at once for a tiny
	 * performance gain.
	 *
	 * @param array|null $routes
	 * @param array|null $options
	 *
	 * @return mixed
	 */
	public function map(array $routes = null, array $options = []);

	//--------------------------------------------------------------------

	/**
	 * Registers a new constraint with the system. Constraints are used
	 * by the routes as placeholders for regular expressions to make defining
	 * the routes more human-friendly.
	 *
	 * @param $name
	 * @param $pattern
	 *
	 * @return mixed
	 */
	public function addPlaceholder($name, $pattern);

	//--------------------------------------------------------------------

	/**
	 * Sets the default namespace to use for controllers when no other
	 * namespace has been specified.
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function setDefaultNamespace($value);

	//--------------------------------------------------------------------

	/**
	 * Sets the default controller to use when no other controller has been
	 * specified.
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function setDefaultController($value);

	//--------------------------------------------------------------------

	/**
	 * Sets the default method to call on the controller when no other
	 * method has been set in the route.
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function setDefaultMethod($value);

	//--------------------------------------------------------------------

	/**
	 * Tells the system whether to convert dashes in URI strings into
	 * underscores. In some search engines, including Google, dashes
	 * create more meaning and make it easier for the search engine to
	 * find words and meaning in the URI for better SEO. But it
	 * doesn't work well with PHP method names....
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function setTranslateURIDashes($value);

	//--------------------------------------------------------------------


	/**
	 * Returns the name of the default controller. With Namespace.
	 *
	 * @return string
	 */
	public function defaultController();

	//--------------------------------------------------------------------

	/**
	 * Returns the name of the default method to use within the controller.
	 *
	 * @return string
	 */
	public function defaultMethod();

	//--------------------------------------------------------------------

	/**
	 * Returns the current value of the translateURIDashses setting.
	 *
	 * @param bool|false $val
	 *
	 * @return mixed
	 */
	public function translateURIDashes();

	//--------------------------------------------------------------------

	/**
	 * Returns the raw array of available routes.
	 *
	 * @return mixed
	 */
	public function routes();

	//--------------------------------------------------------------------

	/**
	 * Returns the current HTTP Verb being used.
	 *
	 * @return string
	 */
	public function HTTPVerb();

	//--------------------------------------------------------------------
}