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
	 * @param string $route
	 * @param array|string $map
	 * @param string|array $method
	 *
	 * @return mixed
	 */
	public function add(string $route, $map, $method = 'get');

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
	public function addPlaceholder(string $name, string $pattern);

	//--------------------------------------------------------------------

	/**
	 * Sets the default namespace to use for controllers when no other
	 * namespace has been specified.
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function setDefaultNamespace(string $value);

	//--------------------------------------------------------------------

	/**
	 * Sets the default controller to use when no other controller has been
	 * specified.
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function setDefaultController(string $value);

	//--------------------------------------------------------------------

	/**
	 * Sets the default method to call on the controller when no other
	 * method has been set in the route.
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function setDefaultMethod(string $value);

	//--------------------------------------------------------------------

	/**
	 * Tells the system whether to convert dashes in URI strings into
	 * underscores. In some search engines, including Google, dashes
	 * create more meaning and make it easier for the search engine to
	 * find words and meaning in the URI for better SEO. But it
	 * doesn't work well with PHP method names....
	 *
	 * @param bool $value
	 *
	 * @return mixed
	 */
	public function setTranslateURIDashes(bool $value);

	//--------------------------------------------------------------------

	/**
	 * If TRUE, the system will attempt to match the URI against
	 * controllers by matching each segment against folders/files
	 * in APPPATH/controllers, when a match wasn't found against
	 * defined routes.
	 *
	 * If FALSE, will stop searching and do NO automatic routing.
	 *
	 * @param bool $value
	 *
	 * @return RouteCollection
	 */
	public function setAutoRoute(bool $value): self;

	//--------------------------------------------------------------------

	/**
	 * Returns the name of the default controller. With Namespace.
	 *
	 * @return string
	 */
	public function getDefaultController();

	//--------------------------------------------------------------------

	/**
	 * Returns the name of the default method to use within the controller.
	 *
	 * @return string
	 */
	public function getDefaultMethod();

	//--------------------------------------------------------------------

	/**
	 * Returns the current value of the translateURIDashses setting.
	 *
	 * @param bool|false $val
	 *
	 * @return mixed
	 */
	public function shouldTranslateURIDashes();

	//--------------------------------------------------------------------

	/**
	 * Returns the flag that tells whether to autoRoute URI against controllers.
	 *
	 * @return bool
	 */
	public function shouldAutoRoute();

	//--------------------------------------------------------------------

	/**
	 * Returns the raw array of available routes.
	 *
	 * @return mixed
	 */
	public function getRoutes();

	//--------------------------------------------------------------------

	/**
	 * Returns the current HTTP Verb being used.
	 *
	 * @return string
	 */
	public function getHTTPVerb();

	//--------------------------------------------------------------------

	/**
	 * Attempts to look up a route based on it's destination.
	 *
	 * If a route exists:
	 *
	 *      'path/(:any)/(:any)' => 'Controller::method/$1/$2'
	 *
	 * This method allows you to know the Controller and method
	 * and get the route that leads to it.
	 *
	 *      // Equals 'path/$param1/$param2'
	 *      reverseRoute('Controller::method', $param1, $param2);
	 *
	 * @param string $route
	 * @param        ...$params
	 */
	public function reverseRoute(string $search, ...$params): string;

	//--------------------------------------------------------------------

}