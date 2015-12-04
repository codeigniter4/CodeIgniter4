<?php namespace App\Config;

use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Router\RouteCollectionInterface;

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

	public static function autoloader($getShared = false)
	{
		if (! $getShared)
		{
			return new \CodeIgniter\Autoloader\Autoloader();
		}

		return self::getSharedInstance('autoloader');
	}

	//--------------------------------------------------------------------

	public static function timer($getShared = false)
	{
		if (! $getShared)
		{
			return new \CodeIgniter\Debug\Timer();
		}

		return self::getSharedInstance('timer');
	}

	//--------------------------------------------------------------------

	public static function iterator($getShared = false)
	{
		if (! $getShared)
		{
			return new \CodeIgniter\Debug\Iterator();
		}

		return self::getSharedInstance('iterator');
	}

	//--------------------------------------------------------------------

	public static function exceptions($getShared = false)
	{
		if (! $getShared)
		{
			return new \CodeIgniter\Debug\Exceptions();
		}

		return self::getSharedInstance('exceptions');
	}

	//--------------------------------------------------------------------

	public static function logger($getShared = true)
	{
		if (! $getShared)
		{
			return new \CodeIgniter\Log\Logger(new \App\Config\LoggerConfig());
		}

		return self::getSharedInstance('logger');
	}

	//--------------------------------------------------------------------

	public static function routes($getShared = false)
	{
		if (! $getShared)
		{
			return new \CodeIgniter\Router\RouteCollection();
		}

		return self::getSharedInstance('routes');
	}

	//--------------------------------------------------------------------

	public static function router(RouteCollectionInterface $routes = null, $getShared = false)
	{
		if ($getShared === true)
		{
			return self::getSharedInstance('router', $routes);
		}

		if (empty($routes))
		{
			$routes = self::routes();
		}

		return new \CodeIgniter\Router\Router($routes);
	}

	//--------------------------------------------------------------------

	public static function renderer($viewPath = APPPATH.'views/', $getShared = false)
	{
		if (! $getShared)
		{
			return new \CodeIgniter\View\View($viewPath);
		}

		return self::getSharedInstance('renderer');
	}

	//--------------------------------------------------------------------

	public static function curlrequest(array $options = [], $response = null, $getShared = false)
	{
		if ($getShared === true)
		{
			return self::getSharedInstance('curlrequest', $options, $response);
		}

		if ( ! is_object($response))
		{
			$response = new Response();
		}

		return new \CodeIgniter\HTTP\CURLRequest(
			new AppConfig(),
			new URI(),
			$response,
			$options
		);
	}

	//--------------------------------------------------------------------

	public static function clirequest($getShared = false)
	{
		if (! $getShared)
		{
			return new \CodeIgniter\HTTP\CLIRequest(
					new AppConfig(),
					new \CodeIgniter\HTTP\URI()
			);
		}

		return self::getSharedInstance('clirequest');
	}

	//--------------------------------------------------------------------

	public static function request($getShared = false)
	{
		if (! $getShared)
		{
			return new \CodeIgniter\HTTP\IncomingRequest(
					new AppConfig(),
					new \CodeIgniter\HTTP\URI()
			);
		}

		return self::getSharedInstance('request');
	}

	//--------------------------------------------------------------------

	public static function uri($uri = null, $getShared = false)
	{
		if (! $getShared)
		{
			return new \CodeIgniter\HTTP\URI($uri);
		}

		return self::getSharedInstance('uri', $uri);
	}

	//--------------------------------------------------------------------

	public static function response($getShared = false)
	{
		if (! $getShared)
		{
			return new \CodeIgniter\HTTP\Response();
		}

		return self::getSharedInstance('response');
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

}
