<?php namespace App\Config;

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
class Services {

	public static function autoloader()
	{
	    return new \CodeIgniter\Autoloader\Autoloader();
	}

	//--------------------------------------------------------------------

	public static function timer()
	{
	    return new \CodeIgniter\Benchmark\Timer();
	}

	//--------------------------------------------------------------------

	public static function iterator()
	{
	    return new \CodeIgniter\Benchmark\Iterator();
	}

	//--------------------------------------------------------------------

	public static function exceptions()
	{
	    return new \CodeIgniter\Debug\Exceptions();
	}

	//--------------------------------------------------------------------

	public static function logger()
	{
	    return new \PSR\Log\Logger(new \App\Config\LoggerConfig());
	}

	//--------------------------------------------------------------------

	public static function routes()
	{
	    return new \CodeIgniter\Router\RouteCollection();
	}

	//--------------------------------------------------------------------

	public static function router()
	{
	    return new \CodeIgniter\Router\Router(self::routes());
	}

	//--------------------------------------------------------------------

	public static function renderer($viewPath=APPPATH.'views/')
	{
	    return new \CodeIgniter\View\View($viewPath);
	}

	//--------------------------------------------------------------------

	public static function clirequest()
	{
		return new \CodeIgniter\HTTP\CLIRequest(
				new AppConfig(), new \CodeIgniter\HTTP\URI()
		);
	}

	//--------------------------------------------------------------------

	public static function request()
	{
		return new \CodeIgniter\HTTP\IncomingRequest(
				new AppConfig(), new \CodeIgniter\HTTP\URI()
		);
	}

	//--------------------------------------------------------------------

	public static function uri($uri=null)
	{
	    return new \CodeIgniter\HTTP\URI($uri);
	}

	//--------------------------------------------------------------------

	public static function response()
	{
	    return new \CodeIgniter\HTTP\Response();
	}

	//--------------------------------------------------------------------

}
