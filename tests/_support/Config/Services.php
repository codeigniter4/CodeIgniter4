<?php namespace Config;

/**
 * Services Configuration file for testing
 *
 * We can't extend \Config\Services in APPPATH, becasuse we can't change the
 * classname.
 */
class Services
{
	/**
	 * Cache for instance of any services that
	 * have been requested as a "shared" instance.
	 *
	 * @var array
	 */
	static protected $instances = [];

	/**
	 * Mock objects for testing which are returned if exist.
	 *
	 * @var array
	 */
	static protected $mocks = [];

	//--------------------------------------------------------------------

	/**
	 * Reset shared instances and mocks for testing.
	 */
	public static function reset()
	{
		static::$instances = [];
		static::$mocks = [];
	}

	/**
	 * Inject mock object for testing.
	 *
	 * @param string $name
	 * @param $mock
	 */
	public static function injectMock(string $name, $mock)
	{
		$name = strtolower($name);
		static::$mocks[$name] = $mock;
	}

	/**
	 * The Autoloader class is the central class that handles our
	 * spl_autoload_register method, and helper methods.
	 */
	public static function autoloader($getShared = false)
	{
		if (isset(static::$mocks[__FUNCTION__]))
		{
			return static::$mocks[__FUNCTION__];
		}

		if (! $getShared)
		{
			return new \CodeIgniter\Autoloader\Autoloader();
		}

		return self::getSharedInstance('autoloader');
	}

	//--------------------------------------------------------------------

	/**
	 * The CLI Request class provides for ways to interact with
	 * a command line request.
	 */
	public static function clirequest(App $config=null, $getShared = false)
	{
		if (isset(static::$mocks[__FUNCTION__]))
		{
			return static::$mocks[__FUNCTION__];
		}

		if (! is_object($config))
		{
			$config = new App();
		}

		if (! $getShared)
		{
			return new \CodeIgniter\HTTP\CLIRequest(
					$config,
					new \CodeIgniter\HTTP\URI()
			);
		}

		return self::getSharedInstance('clirequest');
	}

	//--------------------------------------------------------------------

	/**
	 * The CURL Request class acts as a simple HTTP client for interacting
	 * with other servers, typically through APIs.
	 */
	public static function curlrequest(array $options = [], $response = null, App $config = null, $getShared = false)
	{
		if (isset(static::$mocks[__FUNCTION__]))
		{
			return static::$mocks[__FUNCTION__];
		}

		if ($getShared === true)
		{
			return self::getSharedInstance('curlrequest', $options, $response);
		}

		if (! is_object($config))
		{
			$config = new App();
		}

		if ( ! is_object($response))
		{
			$response = new \CodeIgniter\HTTP\Response($config);
		}

		return new \CodeIgniter\HTTP\CURLRequest(
				$config,
				new \CodeIgniter\HTTP\URI(),
				$response,
				$options
		);
	}

	//--------------------------------------------------------------------

	public static function database($connectionConfig = null, $dbConfig = null, $getShared = false): \CodeIgniter\Data\Database
	{
		if ($getShared)
		{
			if (isset(static::$mocks[__FUNCTION__]))
			{
				return static::$mocks[__FUNCTION__];
			}

			return self::getSharedInstance('database');
		}

		return \CodeIgniter\Data\DatabaseFactory::build($connectionConfig, $dbConfig);
	}

	//--------------------------------------------------------------------

	/**
	 * The Exceptions class holds the methods that handle:
	 *
	 *  - set_exception_handler
	 *  - set_error_handler
	 *  - register_shutdown_function
	 */
	public static function exceptions(App $config = null, $getShared = false)
	{
		if (isset(static::$mocks[__FUNCTION__]))
		{
			return static::$mocks[__FUNCTION__];
		}

		if (empty($config))
		{
			$config = new App();
		}

		if (! $getShared)
		{
			return new \CodeIgniter\Debug\Exceptions($config);
		}

		return self::getSharedInstance('exceptions');
	}

	//--------------------------------------------------------------------

	/**
	 * The Iterator class provides a simple way of looping over a function
	 * and timing the results and memory usage. Used when debugging and
	 * optimizing applications.
	 */
	public static function iterator($getShared = false)
	{
		if (isset(static::$mocks[__FUNCTION__]))
		{
			return static::$mocks[__FUNCTION__];
		}

		if (! $getShared)
		{
			return new \CodeIgniter\Debug\Iterator();
		}

		return self::getSharedInstance('iterator');
	}

	//--------------------------------------------------------------------

	/**
	 * The loader provides utility methods for looking for non-classes
	 * within namespaced folders, as well as convenience methods for
	 * loading 'helpers', and 'libraries'.
	 */
	public static function loader($getShared = false)
	{
		if (isset(static::$mocks[__FUNCTION__]))
		{
			return static::$mocks[__FUNCTION__];
		}

	    if (! $getShared)
	    {
		    return new \CodeIgniter\Loader(new \Config\Autoload());
	    }

		return self::getSharedInstance('loader');
	}

	//--------------------------------------------------------------------


	/**
	 * The Logger class is a PSR-3 compatible Logging class that supports
	 * multiple handlers that process the actual logging.
	 */
	public static function logger($getShared = false)
	{
		if (isset(static::$mocks[__FUNCTION__]))
		{
			return static::$mocks[__FUNCTION__];
		}

		if (! $getShared)
		{
			return new \CodeIgniter\Log\Logger(new Logger());
		}

		return self::getSharedInstance('logger');
	}

	//--------------------------------------------------------------------

	/**
	 * The Negotiate class provides the content negotiation features for
	 * working the request to determine correct language, encoding, charset,
	 * and more.
	 */
	public static function negotiator(\CodeIgniter\HTTP\RequestInterface $request=null, $getShared = false)
	{
		if (isset(static::$mocks[__FUNCTION__]))
		{
			return static::$mocks[__FUNCTION__];
		}

		if (! $getShared)
		{
			if (is_null($request))
			{
				$request = self::request();
			}

			return new \CodeIgniter\HTTP\Negotiate($request);
		}

		return self::getSharedInstance('negotiator');
	}

	//--------------------------------------------------------------------

	/**
	 * The Renderer class is the class that actually displays a file to the user.
	 * The default View class within CodeIgniter is intentionally simple, but this
	 * service could easily be replaced by a template engine if the user needed to.
	 */
	public static function renderer($viewPath = APPPATH.'Views/', $getShared = false)
	{
		if (isset(static::$mocks[__FUNCTION__]))
		{
			return static::$mocks[__FUNCTION__];
		}

		if (! $getShared)
		{
			return new \CodeIgniter\View\View($viewPath, self::loader(true), CI_DEBUG, self::logger(true));
		}

		return self::getSharedInstance('renderer');
	}

	//--------------------------------------------------------------------

	/**
	 * The Request class models an HTTP request.
	 */
	public static function request(App $config = null, $getShared = false)
	{
		if (isset(static::$mocks[__FUNCTION__]))
		{
			return static::$mocks[__FUNCTION__];
		}

		if (! is_object($config))
		{
			$config = new App();
		}

		if (! $getShared)
		{
			return new \CodeIgniter\HTTP\IncomingRequest(
					$config,
					new \CodeIgniter\HTTP\URI()
			);
		}

		return self::getSharedInstance('request');
	}

	//--------------------------------------------------------------------

	/**
	 * The Response class models an HTTP response.
	 */
	public static function response(App $config = null, $getShared = false)
	{
		if (isset(static::$mocks[__FUNCTION__]))
		{
			return static::$mocks[__FUNCTION__];
		}

		if (! is_object($config))
		{
			$config = new App();
		}

		if (! $getShared)
		{
			return new \CodeIgniter\HTTP\Response($config);
		}

		return self::getSharedInstance('response');
	}

	//--------------------------------------------------------------------

	/**
	 * The Routes service is a class that allows for easily building
	 * a collection of routes.
	 */
	public static function routes($getShared = false)
	{
		if (isset(static::$mocks[__FUNCTION__]))
		{
			return static::$mocks[__FUNCTION__];
		}

		if (! $getShared)
		{
			return new \CodeIgniter\Router\RouteCollection();
		}

		return self::getSharedInstance('routes');
	}

	//--------------------------------------------------------------------

	/**
	 * The Router class uses a RouteCollection's array of routes, and determines
	 * the correct Controller and Method to execute.
	 */
	public static function router(\CodeIgniter\Router\RouteCollectionInterface $routes = null, $getShared = false)
	{
		if (isset(static::$mocks[__FUNCTION__]))
		{
			return static::$mocks[__FUNCTION__];
		}

		if ($getShared === true)
		{
			return self::getSharedInstance('router', $routes);
		}

		if (empty($routes))
		{
			$routes = self::routes(true);
		}

		return new \CodeIgniter\Router\Router($routes);
	}

	//--------------------------------------------------------------------

	/**
	 * The Security class provides a few handy tools for keeping the site
	 * secure, most notably the CSRF protection tools.
	 */
	public static function security(App $config = null, $getShared = false)
	{
		if (isset(static::$mocks[__FUNCTION__]))
		{
			return static::$mocks[__FUNCTION__];
		}

		if (! is_object($config))
		{
			$config = new App();
		}

		if (! $getShared)
		{
			return new \CodeIgniter\Security\Security($config);
		}

		return self::getSharedInstance('security');
	}

	//--------------------------------------------------------------------

	/**
	 * @param App|null $config
	 * @param bool     $getShared
	 *
	 * @return \CodeIgniter\Session\Session
	 */
	public static function session(App $config = null, $getShared = false)
	{
		if (isset(static::$mocks[__FUNCTION__]))
		{
			return static::$mocks[__FUNCTION__];
		}

		if (! is_object($config))
		{
			$config = new App();
		}

		if (! $getShared)
		{
			$logger = self::logger(true);

			$driverName = $config->sessionDriver;
			$driver = new $driverName($config);
			$driver->setLogger($logger);

			$session = new \CodeIgniter\Session\Session($driver, $config);
			$session->setLogger($logger);
			$session->initialize();

			return $session;
		}

		return self::getSharedInstance('session');
	}

	//--------------------------------------------------------------------

	/**
	 * The Timer class provides a simple way to Benchmark portions of your
	 * application.
	 */
	public static function timer($getShared = false)
	{
		if (isset(static::$mocks[__FUNCTION__]))
		{
			return static::$mocks[__FUNCTION__];
		}

		if (! $getShared)
		{
			return new \CodeIgniter\Debug\Timer();
		}

		return self::getSharedInstance('timer');
	}

	//--------------------------------------------------------------------

	public static function toolbar(App $config = null, $getShared = false)
	{
		if (isset(static::$mocks[__FUNCTION__]))
		{
			return static::$mocks[__FUNCTION__];
		}

		if (! is_object($config))
		{
			$config = new App();
		}

		if (! $getShared)
		{
			return new \CodeIgniter\Debug\Toolbar($config);
		}

		return self::getSharedInstance('security');
	}

	//--------------------------------------------------------------------

	/**
	 * The URI class provides a way to model and manipulate URIs.
	 */
	public static function uri($uri = null, $getShared = false)
	{
		if (isset(static::$mocks[__FUNCTION__]))
		{
			return static::$mocks[__FUNCTION__];
		}

		if (! $getShared)
		{
			return new \CodeIgniter\HTTP\URI($uri);
		}

		return self::getSharedInstance('uri', $uri);
	}

	//--------------------------------------------------------------------


	//--------------------------------------------------------------------
	// Utility Methods - DO NOT EDIT
	//--------------------------------------------------------------------

	/**
	 * Returns a shared instance of any of the class' services.
	 *
	 * $key must be a name matching a service.
	 *
	 * @param string $key
	 */
	protected static function getSharedInstance(string $key, ...$params)
	{
		if (! isset(static::$instances[$key]))
		{
			static::$instances[$key] = self::$key(...$params);
		}

		return static::$instances[$key];
	}

	//--------------------------------------------------------------------

	/**
	 * Provides the ability to perform case-insensitive calling of service
	 * names.
	 *
	 * @param string $name
	 * @param array  $arguments
	 */
	public static function __callStatic(string $name, array $arguments)
	{
		$name = strtolower($name);

		if (method_exists('Config\Services', $name))
		{
			return Services::$name(...$arguments);
		}
	}

	//--------------------------------------------------------------------


}
