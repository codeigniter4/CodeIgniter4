<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Config;

use CodeIgniter\Cache\CacheFactory;
use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\CLI\Commands;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Database\MigrationRunner;
use CodeIgniter\Debug\Exceptions;
use CodeIgniter\Debug\Iterator;
use CodeIgniter\Debug\Timer;
use CodeIgniter\Debug\Toolbar;
use CodeIgniter\Email\Email;
use CodeIgniter\Encryption\EncrypterInterface;
use CodeIgniter\Encryption\Encryption;
use CodeIgniter\Filters\Filters;
use CodeIgniter\Format\Format;
use CodeIgniter\Honeypot\Honeypot;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Negotiate;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Images\Handlers\BaseHandler;
use CodeIgniter\Language\Language;
use CodeIgniter\Log\Logger;
use CodeIgniter\Pager\Pager;
use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Router\RouteCollectionInterface;
use CodeIgniter\Router\Router;
use CodeIgniter\Security\Security;
use CodeIgniter\Session\Session;
use CodeIgniter\Throttle\Throttler;
use CodeIgniter\Typography\Typography;
use CodeIgniter\Validation\Validation;
use CodeIgniter\View\Cell;
use CodeIgniter\View\Parser;
use CodeIgniter\View\RendererInterface;
use CodeIgniter\View\View;
use Config\App;
use Config\Cache;
use Config\Email as EmailConfig;
use Config\Encryption as EncryptionConfig;
use Config\Exceptions as ExceptionsConfig;
use Config\Filters as FiltersConfig;
use Config\Format as FormatConfig;
use Config\Honeypot as HoneypotConfig;
use Config\Images;
use Config\Migrations;
use Config\Pager as PagerConfig;
use Config\Toolbar as ToolbarConfig;
use Config\Validation as ValidationConfig;
use Config\View as ViewConfig;

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
class Services extends BaseService
{
	/**
	 * The cache class provides a simple way to store and retrieve
	 * complex data for later.
	 *
	 * @param Cache|null $cache
	 * @param boolean    $getShared
	 *
	 * @return CacheInterface
	 */
	public static function cache(Cache $cache = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('cache', $cache);
		}

		$cache = $cache ?? new Cache();

		return CacheFactory::getHandler($cache);
	}

	//--------------------------------------------------------------------
	/**
	 * The CLI Request class provides for ways to interact with
	 * a command line request.
	 *
	 * @param App|null $app
	 * @param boolean  $getShared
	 *
	 * @return CLIRequest
	 */
	public static function clirequest(App $app = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('clirequest', $app);
		}

		$app = $app ?? config('App');

		return new CLIRequest($app);
	}

	//--------------------------------------------------------------------

	/**
	 * The commands utility for running and working with CLI commands.
	 *
	 * @param boolean $getShared
	 *
	 * @return Commands
	 */
	public static function commands(bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('commands');
		}

		return new Commands();
	}

	/**
	 * The CURL Request class acts as a simple HTTP client for interacting
	 * with other servers, typically through APIs.
	 *
	 * @param array                  $options
	 * @param ResponseInterface|null $response
	 * @param App|null               $app
	 * @param boolean                $getShared
	 *
	 * @return CURLRequest
	 */
	public static function curlrequest(array $options = [], ResponseInterface $response = null, App $app = null, bool $getShared = true)
	{
		if ($getShared === true)
		{
			return static::getSharedInstance('curlrequest', $options, $response, $app);
		}

		$app      = $app ?? config('App');
		$response = $response ?? new Response($app);

		return new CURLRequest(
			$app,
			new URI($options['base_uri'] ?? null),
			$response,
			$options
		);
	}

	//--------------------------------------------------------------------

	/**
	 * The Email class allows you to send email via mail, sendmail, SMTP.
	 *
	 * @param EmailConfig|array|null $config
	 * @param boolean                $getShared
	 *
	 * @return Email
	 */
	public static function email($config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('email', $config);
		}

		if (empty($config) || ! (is_array($config) || $config instanceof EmailConfig))
		{
			$config = config('Email');
		}

		return new Email($config);
	}

	/**
	 * The Encryption class provides two-way encryption.
	 *
	 * @param EncryptionConfig|null $encryptionConfig
	 * @param boolean               $getShared
	 *
	 * @return EncrypterInterface Encryption handler
	 */
	public static function encrypter(EncryptionConfig $encryptionConfig = null, $getShared = false)
	{
		if ($getShared === true)
		{
			return static::getSharedInstance('encrypter', $encryptionConfig);
		}

		$encryptionConfig = $encryptionConfig ?? config('Encryption');
		$encryption       = new Encryption($encryptionConfig);

		return $encryption->initialize($encryptionConfig);
	}

	//--------------------------------------------------------------------
	/**
	 * The Exceptions class holds the methods that handle:
	 *
	 *  - set_exception_handler
	 *  - set_error_handler
	 *  - register_shutdown_function
	 *
	 * @param ExceptionsConfig|null $exceptionsConfig
	 * @param IncomingRequest|null  $incomingRequest
	 * @param Response|null         $response
	 * @param boolean               $getShared
	 *
	 * @return Exceptions
	 */
	public static function exceptions(
		ExceptionsConfig $exceptionsConfig = null,
		IncomingRequest $incomingRequest = null,
		Response $response = null,
		bool $getShared = true
	)
	{
		if ($getShared)
		{
			return static::getSharedInstance('exceptions', $exceptionsConfig, $incomingRequest, $response);
		}

		$exceptionsConfig = $exceptionsConfig ?? config('Exceptions');
		$incomingRequest  = $incomingRequest ?? static::request();
		$response         = $response ?? static::response();

		return new Exceptions($exceptionsConfig, $incomingRequest, $response);
	}

	//--------------------------------------------------------------------
	/**
	 * Filters allow you to run tasks before and/or after a controller
	 * is executed. During before filters, the request can be modified,
	 * and actions taken based on the request, while after filters can
	 * act on or modify the response itself before it is sent to the client.
	 *
	 * @param FiltersConfig|null $filtersConfig
	 * @param boolean            $getShared
	 *
	 * @return Filters
	 */
	public static function filters(FiltersConfig $filtersConfig = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('filters', $filtersConfig);
		}

		$filtersConfig = $filtersConfig ?? config('Filters');

		return new Filters($filtersConfig, static::request(), static::response());
	}

	//--------------------------------------------------------------------
	/**
	 * The Format class is a convenient place to create Formatters.
	 *
	 * @param FormatConfig|null $formatConfig
	 * @param boolean           $getShared
	 *
	 * @return Format
	 */
	public static function format(FormatConfig $formatConfig = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('format', $formatConfig);
		}

		$formatConfig = $formatConfig ?? config('Format');

		return new Format($formatConfig);
	}

	//--------------------------------------------------------------------
	/**
	 * The Honeypot provides a secret input on forms that bots should NOT
	 * fill in, providing an additional safeguard when accepting user input.
	 *
	 * @param HoneypotConfig|null $honeypotConfig
	 * @param boolean             $getShared
	 *
	 * @return Honeypot
	 */
	public static function honeypot(HoneypotConfig $honeypotConfig = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('honeypot', $honeypotConfig);
		}

		$honeypotConfig = $honeypotConfig ?? config('Honeypot');

		return new Honeypot($honeypotConfig);
	}

	//--------------------------------------------------------------------
	/**
	 * Acts as a factory for ImageHandler classes and returns an instance
	 * of the handler. Used like Services::image()->withFile($path)->rotate(90)->save();
	 *
	 * @param string|null $handler
	 * @param Images|null $images
	 * @param boolean     $getShared
	 *
	 * @return BaseHandler
	 */
	public static function image(string $handler = null, Images $images = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('image', $handler, $images);
		}

		$images  = $images ?? config('Images');
		$handler = $handler ?: $images->defaultHandler;
		$class   = $images->handlers[$handler];

		return new $class($images);
	}

	//--------------------------------------------------------------------

	/**
	 * The Iterator class provides a simple way of looping over a function
	 * and timing the results and memory usage. Used when debugging and
	 * optimizing applications.
	 *
	 * @param boolean $getShared
	 *
	 * @return Iterator
	 */
	public static function iterator(bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('iterator');
		}

		return new Iterator();
	}

	//--------------------------------------------------------------------

	/**
	 * Responsible for loading the language string translations.
	 *
	 * @param string|null $locale
	 * @param boolean     $getShared
	 *
	 * @return Language
	 */
	public static function language(string $locale = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('language', $locale)->setLocale($locale);
		}

		// Use '?:' for empty string check
		$locale = $locale ?: static::request()->getLocale();

		return new Language($locale);
	}

	//--------------------------------------------------------------------

	/**
	 * The Logger class is a PSR-3 compatible Logging class that supports
	 * multiple handlers that process the actual logging.
	 *
	 * @param boolean $getShared
	 *
	 * @return Logger
	 */
	public static function logger(bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('logger');
		}

		return new Logger(config('Logger'));
	}

	//--------------------------------------------------------------------
	/**
	 * Return the appropriate Migration runner.
	 *
	 * @param Migrations|null          $migrations
	 * @param ConnectionInterface|null $connection
	 * @param boolean                  $getShared
	 *
	 * @return MigrationRunner
	 */
	public static function migrations(Migrations $migrations = null, ConnectionInterface $connection = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('migrations', $migrations, $connection);
		}

		$migrations = $migrations ?? config('Migrations');

		return new MigrationRunner($migrations, $connection);
	}

	//--------------------------------------------------------------------

	/**
	 * The Negotiate class provides the content negotiation features for
	 * working the request to determine correct language, encoding, charset,
	 * and more.
	 *
	 * @param RequestInterface|null $request
	 * @param boolean               $getShared
	 *
	 * @return Negotiate
	 */
	public static function negotiator(RequestInterface $request = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('negotiator', $request);
		}

		$request = $request ?? static::request();

		return new Negotiate($request);
	}

	//--------------------------------------------------------------------
	/**
	 * Return the appropriate pagination handler.
	 *
	 * @param PagerConfig|null       $pagerConfig
	 * @param RendererInterface|null $renderer
	 * @param boolean                $getShared
	 *
	 * @return Pager
	 */
	public static function pager(PagerConfig $pagerConfig = null, RendererInterface $renderer = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('pager', $pagerConfig, $renderer);
		}

		$pagerConfig = $pagerConfig ?? config('Pager');
		$renderer    = $renderer ?? static::renderer();

		return new Pager($pagerConfig, $renderer);
	}

	//--------------------------------------------------------------------
	/**
	 * The Parser is a simple template parser.
	 *
	 * @param string|null     $viewPath
	 * @param ViewConfig|null $viewConfig
	 * @param boolean         $getShared
	 *
	 * @return Parser
	 */
	public static function parser(string $viewPath = null, ViewConfig $viewConfig = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('parser', $viewPath, $viewConfig);
		}

		$viewPath   = $viewPath ?: config('Paths')->viewDirectory;
		$viewConfig = $viewConfig ?? config('View');

		return new Parser($viewConfig, $viewPath, static::locator(), CI_DEBUG, static::logger());
	}

	//--------------------------------------------------------------------
	/**
	 * The Renderer class is the class that actually displays a file to the user.
	 * The default View class within CodeIgniter is intentionally simple, but this
	 * service could easily be replaced by a template engine if the user needed to.
	 *
	 * @param string|null     $viewPath
	 * @param ViewConfig|null $viewConfig
	 * @param boolean         $getShared
	 *
	 * @return View
	 */
	public static function renderer(string $viewPath = null, ViewConfig $viewConfig = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('renderer', $viewPath, $viewConfig);
		}

		$viewPath   = $viewPath ?: config('Paths')->viewDirectory;
		$viewConfig = $viewConfig ?? config('View');

		return new View($viewConfig, $viewPath, static::locator(), CI_DEBUG, static::logger());
	}

	//--------------------------------------------------------------------
	/**
	 * The Request class models an HTTP request.
	 *
	 * @param App|null $app
	 * @param boolean  $getShared
	 *
	 * @return IncomingRequest
	 */
	public static function request(App $app = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('request', $app);
		}

		$app = $app ?? config('App');

		return new IncomingRequest(
			$app,
			static::uri(),
			'php://input',
			new UserAgent()
		);
	}

	//--------------------------------------------------------------------
	/**
	 * The Response class models an HTTP response.
	 *
	 * @param App|null $app
	 * @param boolean  $getShared
	 *
	 * @return Response
	 */
	public static function response(App $app = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('response', $app);
		}

		$app = $app ?? config('App');

		return new Response($app);
	}

	//--------------------------------------------------------------------
	/**
	 * The Redirect class provides nice way of working with redirects.
	 *
	 * @param App|null $app
	 * @param boolean  $getShared
	 *
	 * @return RedirectResponse
	 */
	public static function redirectresponse(App $app = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('redirectresponse', $app);
		}

		$app      = $app ?? config('App');
		$response = new RedirectResponse($app);
		$response->setProtocolVersion(static::request()->getProtocolVersion());

		return $response;
	}

	//--------------------------------------------------------------------

	/**
	 * The Routes service is a class that allows for easily building
	 * a collection of routes.
	 *
	 * @param boolean $getShared
	 *
	 * @return RouteCollection
	 */
	public static function routes(bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('routes');
		}

		return new RouteCollection(static::locator(), config('Modules'));
	}

	//--------------------------------------------------------------------
	/**
	 * The Router class uses a RouteCollection's array of routes, and determines
	 * the correct Controller and Method to execute.
	 *
	 * @param RouteCollectionInterface|null $routeCollection
	 * @param Request|null                  $request
	 * @param boolean                       $getShared
	 *
	 * @return Router
	 */
	public static function router(RouteCollectionInterface $routeCollection = null, Request $request = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('router', $routeCollection, $request);
		}

		$routeCollection = $routeCollection ?? static::routes();
		$request         = $request ?? static::request();

		return new Router($routeCollection, $request);
	}

	//--------------------------------------------------------------------
	/**
	 * The Security class provides a few handy tools for keeping the site
	 * secure, most notably the CSRF protection tools.
	 *
	 * @param App|null $app
	 * @param boolean  $getShared
	 *
	 * @return Security
	 */
	public static function security(App $app = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('security', $app);
		}

		$app = $app ?? config('App');

		return new Security($app);
	}

	//--------------------------------------------------------------------
	/**
	 * Return the session manager.
	 *
	 * @param App|null $app
	 * @param boolean  $getShared
	 *
	 * @return Session
	 */
	public static function session(App $app = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('session', $app);
		}

		$app    = $app ?? config('App');
		$logger = static::logger();

		$driverName = $app->sessionDriver;
		$driver     = new $driverName($app, static::request()->getIPAddress());
		$driver->setLogger($logger);

		$session = new Session($driver, $app);
		$session->setLogger($logger);

		if (session_status() === PHP_SESSION_NONE)
		{
			$session->start();
		}

		return $session;
	}

	//--------------------------------------------------------------------

	/**
	 * The Throttler class provides a simple method for implementing
	 * rate limiting in your applications.
	 *
	 * @param boolean $getShared
	 *
	 * @return Throttler
	 */
	public static function throttler(bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('throttler');
		}

		return new Throttler(static::cache());
	}

	//--------------------------------------------------------------------

	/**
	 * The Timer class provides a simple way to Benchmark portions of your
	 * application.
	 *
	 * @param boolean $getShared
	 *
	 * @return Timer
	 */
	public static function timer(bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('timer');
		}

		return new Timer();
	}

	//--------------------------------------------------------------------
	/**
	 * Return the debug toolbar.
	 *
	 * @param ToolbarConfig|null $toolbarConfig
	 * @param boolean            $getShared
	 *
	 * @return Toolbar
	 */
	public static function toolbar(ToolbarConfig $toolbarConfig = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('toolbar', $toolbarConfig);
		}

		$toolbarConfig = $toolbarConfig ?? config('Toolbar');

		return new Toolbar($toolbarConfig);
	}

	//--------------------------------------------------------------------

	/**
	 * The URI class provides a way to model and manipulate URIs.
	 *
	 * @param string  $uri
	 * @param boolean $getShared
	 *
	 * @return URI
	 */
	public static function uri(string $uri = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('uri', $uri);
		}

		return new URI($uri);
	}

	//--------------------------------------------------------------------
	/**
	 * The Validation class provides tools for validating input data.
	 *
	 * @param ValidationConfig|null $validationConfig
	 * @param boolean               $getShared
	 *
	 * @return Validation
	 */
	public static function validation(ValidationConfig $validationConfig = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('validation', $validationConfig);
		}

		$validationConfig = $validationConfig ?? config('Validation');

		return new Validation($validationConfig, static::renderer());
	}

	//--------------------------------------------------------------------

	/**
	 * View cells are intended to let you insert HTML into view
	 * that has been generated by any callable in the system.
	 *
	 * @param boolean $getShared
	 *
	 * @return Cell
	 */
	public static function viewcell(bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('viewcell');
		}

		return new Cell(static::cache());
	}

	//--------------------------------------------------------------------

	/**
	 * The Typography class provides a way to format text in semantically relevant ways.
	 *
	 * @param boolean $getShared
	 *
	 * @return Typography
	 */
	public static function typography(bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('typography');
		}

		return new Typography();
	}
}
