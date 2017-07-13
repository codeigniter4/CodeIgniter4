<?php namespace CodeIgniter\Config;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2017 British Columbia Institute of Technology
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
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	2014-2017 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Database\MigrationRunner;
use CodeIgniter\View\RendererInterface;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This is used in place of a Dependency Injection container primarily
 * due to its simplicity, which allows a better long-term maintenance
 * of the applications built on top of CodeIgniter. A bonus side-effect
 * is that IDEs are able to determine what class you are calling
 * whereas with DI Containers there usually isn't a way for them to do this.
 *
 * @see http://blog.ircmaxell.com/2015/11/simple-easy-risk-and-change.html
 * @see http://www.infoq.com/presentations/Simple-Made-Easy
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

	//--------------------------------------------------------------------

	/**
	 * The Autoloader class is the central class that handles our
	 * spl_autoload_register method, and helper methods.
	 */
	public static function autoloader($getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('autoloader');
		}

		return new \CodeIgniter\Autoloader\Autoloader();
	}

	//--------------------------------------------------------------------

	/**
	 * The cache class provides a simple way to store and retrieve
	 * complex data for later.
	 */
	public static function cache(\Config\Cache $config = null, $getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('cache', $config);
		}

		if ( ! is_object($config))
		{
			$config = new \Config\Cache();
		}

		return \CodeIgniter\Cache\CacheFactory::getHandler($config);
	}

	//--------------------------------------------------------------------

	/**
	 * The CLI Request class provides for ways to interact with
	 * a command line request.
	 */
	public static function clirequest(\Config\App $config = null, $getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('clirequest', $config);
		}

		if ( ! is_object($config))
		{
			$config = new \Config\App();
		}

		return new \CodeIgniter\HTTP\CLIRequest(
				$config, new \CodeIgniter\HTTP\URI()
		);
	}

	//--------------------------------------------------------------------

	/**
	 * The CURL Request class acts as a simple HTTP client for interacting
	 * with other servers, typically through APIs.
	 */
	public static function curlrequest(array $options = [], $response = null, \Config\App $config = null, $getShared = true)
	{
		if ($getShared === true)
		{
			return self::getSharedInstance('curlrequest', $options, $response, $config);
		}

		if ( ! is_object($config))
		{
			$config = new \Config\App();
		}

		if ( ! is_object($response))
		{
			$response = new \CodeIgniter\HTTP\Response($config);
		}

		return new \CodeIgniter\HTTP\CURLRequest(
				$config, new \CodeIgniter\HTTP\URI(), $response, $options
		);
	}

	//--------------------------------------------------------------------

	/**
	 * The Encryption class provides two-way encryption.
	 * 
	 * @return \CodeIgniter\Encryption\EncrypterInterfrace Encryption handler
	 */
	public static function encrypter($config = null, $getShared = false)
	{
		if ($getShared === true)
			return self::getSharedInstance('encrypter', $config);

		if ($config != null && is_object($config))
			$config = (array) $config;

		$encryption = new \CodeIgniter\Encryption\Encryption($config);
		$encrypter = $encryption->initialize($config);
		return $encrypter;
	}

	//--------------------------------------------------------------------

	/**
	 * The Exceptions class holds the methods that handle:
	 *
	 *  - set_exception_handler
	 *  - set_error_handler
	 *  - register_shutdown_function
	 */
	public static function exceptions(\Config\App $config = null, $getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('exceptions', $config);
		}

		if (empty($config))
		{
			$config = new \Config\App();
		}

		return new \CodeIgniter\Debug\Exceptions($config);
	}

	//--------------------------------------------------------------------

	/**
	 * Filters allow you to run tasks before and/or after a controller
	 * is executed. During before filters, the request can be modified,
	 * and actions taken based on the request, while after filters can
	 * act on or modify the response itself before it is sent to the client.
	 */
	public static function filters($config = null, $getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('filters', $config);
		}

		if (empty($config))
		{
			$config = new \Config\Filters();
		}

		return new \CodeIgniter\Filters\Filters($config, self::request(), self::response());
	}

	//--------------------------------------------------------------------

	/**
	 * Acts as a factory for ImageHandler classes and returns an instance
	 * of the handler. Used like Services::image()->withFile($path)->rotate(90)->save();
	 */
	public static function image(string $handler = null, $config = null, $getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('image', $handler, $config);
		}

		if (empty($config))
		{
			$config = new \Config\Images();
		}

		$handler = is_null($handler) ? $config->defaultHandler : $handler;

		$class = $config->handlers[$handler];

		return new $class($config);
	}

	//--------------------------------------------------------------------

	/**
	 * The Iterator class provides a simple way of looping over a function
	 * and timing the results and memory usage. Used when debugging and
	 * optimizing applications.
	 */
	public static function iterator($getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('iterator');
		}

		return new \CodeIgniter\Debug\Iterator();
	}

	//--------------------------------------------------------------------

	/**
	 * Responsible for loading the language string translations.
	 */
	public static function language(string $locale = null, $getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('language', $locale);
		}

		$locale = ! empty($locale) ? $locale : self::request()->getLocale();

		return new \CodeIgniter\Language\Language($locale);
	}

	//--------------------------------------------------------------------

	/**
	 * The file locator provides utility methods for looking for non-classes
	 * within namespaced folders, as well as convenience methods for
	 * loading 'helpers', and 'libraries'.
	 */
	public static function locator($getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('locator');
		}

		return new \CodeIgniter\Autoloader\FileLocator(new \Config\Autoload());
	}

	//--------------------------------------------------------------------

	/**
	 * The Logger class is a PSR-3 compatible Logging class that supports
	 * multiple handlers that process the actual logging.
	 */
	public static function logger($getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('logger');
		}

		return new \CodeIgniter\Log\Logger(new \Config\Logger());
	}

	//--------------------------------------------------------------------

	public static function migrations(BaseConfig $config = null, ConnectionInterface $db = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('migrations', $config, $db);
		}

		$config = empty($config) ? new \Config\Migrations() : $config;

		return new MigrationRunner($config, $db);
	}

	//--------------------------------------------------------------------

	/**
	 * The Negotiate class provides the content negotiation features for
	 * working the request to determine correct language, encoding, charset,
	 * and more.
	 */
	public static function negotiator(\CodeIgniter\HTTP\RequestInterface $request = null, $getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('negotiator', $request);
		}

		if (is_null($request))
		{
			$request = self::request();
		}

		return new \CodeIgniter\HTTP\Negotiate($request);
	}

	//--------------------------------------------------------------------

	public static function pager($config = null, RendererInterface $view = null, $getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('pager', $config, $view);
		}

		if (empty($config))
		{
			$config = new \Config\Pager();
		}

		if ( ! $view instanceof RendererInterface)
		{
			$view = self::renderer();
		}

		return new \CodeIgniter\Pager\Pager($config, $view);
	}

	//--------------------------------------------------------------------

	/**
	 * The Parser is a simple template parser.
	 */
	public static function parser($viewPath = APPPATH . 'Views/', $config = null, $getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('parser', $viewPath, $config);
		}

		if (is_null($config))
		{
			$config = new \Config\View();
		}

		return new \CodeIgniter\View\Parser($config, $viewPath, self::locator(true), CI_DEBUG, self::logger(true));
	}

	//--------------------------------------------------------------------

	/**
	 * The Renderer class is the class that actually displays a file to the user.
	 * The default View class within CodeIgniter is intentionally simple, but this
	 * service could easily be replaced by a template engine if the user needed to.
	 */
	public static function renderer($viewPath = APPPATH . 'Views/', $config = null, $getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('renderer', $viewPath, $config);
		}

		if (is_null($config))
		{
			$config = new \Config\View();
		}

		return new \CodeIgniter\View\View($config, $viewPath, self::locator(true), CI_DEBUG, self::logger(true));
	}

	//--------------------------------------------------------------------

	/**
	 * The Request class models an HTTP request.
	 */
	public static function request(\Config\App $config = null, $getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('request', $config);
		}

		if ( ! is_object($config))
		{
			$config = new \Config\App();
		}

		return new \CodeIgniter\HTTP\IncomingRequest(
				$config, new \CodeIgniter\HTTP\URI()
		);
	}

	//--------------------------------------------------------------------

	/**
	 * The Response class models an HTTP response.
	 */
	public static function response(\Config\App $config = null, $getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('response', $config);
		}

		if ( ! is_object($config))
		{
			$config = new \Config\App();
		}

		return new \CodeIgniter\HTTP\Response($config);
	}

	//--------------------------------------------------------------------

	/**
	 * The Routes service is a class that allows for easily building
	 * a collection of routes.
	 */
	public static function routes($getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('routes');
		}

		return new \CodeIgniter\Router\RouteCollection(self::locator());
	}

	//--------------------------------------------------------------------

	/**
	 * The Router class uses a RouteCollection's array of routes, and determines
	 * the correct Controller and Method to execute.
	 */
	public static function router(\CodeIgniter\Router\RouteCollectionInterface $routes = null, $getShared = true)
	{
		if ($getShared)
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
	public static function security(\Config\App $config = null, $getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('security', $config);
		}

		if ( ! is_object($config))
		{
			$config = new \Config\App();
		}

		return new \CodeIgniter\Security\Security($config);
	}

	//--------------------------------------------------------------------

	/**
	 * @param App|null $config
	 * @param bool     $getShared
	 *
	 * @return \CodeIgniter\Session\Session
	 */
	public static function session(\Config\App $config = null, $getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('session', $config);
		}

		if ( ! is_object($config))
		{
			$config = new \Config\App();
		}

		$logger = self::logger(true);

		$driverName = $config->sessionDriver;
		$driver = new $driverName($config);
		$driver->setLogger($logger);

		$session = new \CodeIgniter\Session\Session($driver, $config);
		$session->setLogger($logger);

		return $session;
	}

	//--------------------------------------------------------------------

	/**
	 * The Throttler class provides a simple method for implementing
	 * rate limiting in your applications.
	 */
	public static function throttler($getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('throttler');
		}

		return new \CodeIgniter\Throttle\Throttler(self::cache());
	}

	//--------------------------------------------------------------------

	/**
	 * The Timer class provides a simple way to Benchmark portions of your
	 * application.
	 */
	public static function timer($getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('timer');
		}

		return new \CodeIgniter\Debug\Timer();
	}

	//--------------------------------------------------------------------

	public static function toolbar(\Config\App $config = null, $getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('toolbar', $config);
		}

		if ( ! is_object($config))
		{
			$config = new \Config\App();
		}

		return new \CodeIgniter\Debug\Toolbar($config);
	}

	//--------------------------------------------------------------------

	/**
	 * The URI class provides a way to model and manipulate URIs.
	 */
	public static function uri($uri = null, $getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('uri', $uri);
		}

		return new \CodeIgniter\HTTP\URI($uri);
	}

	//--------------------------------------------------------------------

	/**
	 * The Validation class provides tools for validating input data.
	 */
	public static function validation(\Config\Validation $config = null, $getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('validation', $config);
		}

		if (is_null($config))
		{
			$config = new \Config\Validation();
		}

		return new \CodeIgniter\Validation\Validation($config, self::renderer());
	}

	//--------------------------------------------------------------------

	/**
	 * View cells are intended to let you insert HTML into view
	 * that has been generated by any callable in the system.
	 */
	public static function viewcell($getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('viewcell');
		}

		return new \CodeIgniter\View\Cell(self::cache());
	}

	//--------------------------------------------------------------------

	/**
	 * The Typography class provides a way to format text in semantically relevant ways.
	 */
	public static function typography($getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('typography');
		}

		return new \CodeIgniter\Typography\Typography();
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
		if ( ! isset(static::$instances[$key]))
		{
			// Make sure $getShared is false
			array_push($params, false);

			static::$instances[$key] = static::$key(...$params);
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

		if (method_exists(__CLASS__, $name))
		{
			return Services::$name(...$arguments);
		}
	}

	//--------------------------------------------------------------------
}
