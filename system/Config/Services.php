<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
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
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Config;

use CodeIgniter\Cache\CacheFactory;
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
use Config\Format as FormatConfig;
use Config\Filters as FiltersConfig;
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
	 * @param \Config\Cache|null $config
	 * @param boolean            $getShared
	 *
	 * @return \CodeIgniter\Cache\CacheInterface
	 */
	public static function cache(Cache $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('cache', $config);
		}

		$config = $config ?? new Cache();

		return CacheFactory::getHandler($config);
	}

	//--------------------------------------------------------------------

	/**
	 * The CLI Request class provides for ways to interact with
	 * a command line request.
	 *
	 * @param \Config\App|null $config
	 * @param boolean          $getShared
	 *
	 * @return \CodeIgniter\HTTP\CLIRequest
	 */
	public static function clirequest(App $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('clirequest', $config);
		}

		$config = $config ?? config('App');

		return new CLIRequest($config);
	}

	//--------------------------------------------------------------------

	/**
	 * The commands utility for running and working with CLI commands.
	 *
	 * @param boolean $getShared
	 *
	 * @return \CodeIgniter\CLI\Commands
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
	 * @param array                                    $options
	 * @param \CodeIgniter\HTTP\ResponseInterface|null $response
	 * @param \Config\App|null                         $config
	 * @param boolean                                  $getShared
	 *
	 * @return \CodeIgniter\HTTP\CURLRequest
	 */
	public static function curlrequest(array $options = [], ResponseInterface $response = null, App $config = null, bool $getShared = true)
	{
		if ($getShared === true)
		{
			return static::getSharedInstance('curlrequest', $options, $response, $config);
		}

		$config   = $config ?? config('App');
		$response = $response ?? new Response($config);

		return new CURLRequest(
			$config,
			new URI($options['base_uri'] ?? null),
			$response,
			$options
		);
	}

	//--------------------------------------------------------------------

	/**
	 * The Email class allows you to send email via mail, sendmail, SMTP.
	 *
	 * @param \Config\Email|array|null $config
	 * @param boolean                  $getShared
	 *
	 * @return \CodeIgniter\Email\Email|mixed
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
	 * @param \Config\Encryption|null $config
	 * @param boolean                 $getShared
	 *
	 * @return EncrypterInterface Encryption handler
	 */
	public static function encrypter(EncryptionConfig $config = null, $getShared = false)
	{
		if ($getShared === true)
		{
			return static::getSharedInstance('encrypter', $config);
		}

		$config     = $config ?? config('Encryption');
		$encryption = new Encryption($config);

		return $encryption->initialize($config);
	}

	//--------------------------------------------------------------------

	/**
	 * The Exceptions class holds the methods that handle:
	 *
	 *  - set_exception_handler
	 *  - set_error_handler
	 *  - register_shutdown_function
	 *
	 * @param \Config\Exceptions|null                $config
	 * @param \CodeIgniter\HTTP\IncomingRequest|null $request
	 * @param \CodeIgniter\HTTP\Response|null        $response
	 * @param boolean                                $getShared
	 *
	 * @return \CodeIgniter\Debug\Exceptions
	 */
	public static function exceptions(
		ExceptionsConfig $config = null,
		IncomingRequest $request = null,
		Response $response = null,
		bool $getShared = true
	)
	{
		if ($getShared)
		{
			return static::getSharedInstance('exceptions', $config, $request, $response);
		}

		$config   = $config ?? config('Exceptions');
		$request  = $request ?? static::request();
		$response = $response ?? static::response();

		return new Exceptions($config, $request, $response);
	}

	//--------------------------------------------------------------------

	/**
	 * Filters allow you to run tasks before and/or after a controller
	 * is executed. During before filters, the request can be modified,
	 * and actions taken based on the request, while after filters can
	 * act on or modify the response itself before it is sent to the client.
	 *
	 * @param \Config\Filters|null $config
	 * @param boolean              $getShared
	 *
	 * @return \CodeIgniter\Filters\Filters
	 */
	public static function filters(FiltersConfig $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('filters', $config);
		}

		$config = $config ?? config('Filters');

		return new Filters($config, static::request(), static::response());
	}

	//--------------------------------------------------------------------

	/**
	 * The Format class is a convenient place to create Formatters.
	 *
	 * @param \Config\Format|null $config
	 * @param boolean             $getShared
	 *
	 * @return \CodeIgniter\Format\Format
	 */
	public static function format(FormatConfig $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('format', $config);
		}

		$config = $config ?? config('Format');

		return new Format($config);
	}

	//--------------------------------------------------------------------

	/**
	 * The Honeypot provides a secret input on forms that bots should NOT
	 * fill in, providing an additional safeguard when accepting user input.
	 *
	 * @param \Config\Honeypot|null $config
	 * @param boolean               $getShared
	 *
	 * @return \CodeIgniter\Honeypot\Honeypot
	 */
	public static function honeypot(HoneypotConfig $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('honeypot', $config);
		}

		$config = $config ?? config('Honeypot');

		return new Honeypot($config);
	}

	//--------------------------------------------------------------------

	/**
	 * Acts as a factory for ImageHandler classes and returns an instance
	 * of the handler. Used like Services::image()->withFile($path)->rotate(90)->save();
	 *
	 * @param string|null         $handler
	 * @param \Config\Images|null $config
	 * @param boolean             $getShared
	 *
	 * @return \CodeIgniter\Images\Handlers\BaseHandler
	 */
	public static function image(string $handler = null, Images $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('image', $handler, $config);
		}

		$config  = $config ?? config('Images');
		$handler = $handler ?: $config->defaultHandler;
		$class   = $config->handlers[$handler];

		return new $class($config);
	}

	//--------------------------------------------------------------------

	/**
	 * The Iterator class provides a simple way of looping over a function
	 * and timing the results and memory usage. Used when debugging and
	 * optimizing applications.
	 *
	 * @param boolean $getShared
	 *
	 * @return \CodeIgniter\Debug\Iterator
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
	 * @return \CodeIgniter\Language\Language
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
	 * @return \CodeIgniter\Log\Logger
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
	 * @param \Config\Migrations|null                        $config
	 * @param \CodeIgniter\Database\ConnectionInterface|null $db
	 * @param boolean                                        $getShared
	 *
	 * @return \CodeIgniter\Database\MigrationRunner
	 */
	public static function migrations(Migrations $config = null, ConnectionInterface $db = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('migrations', $config, $db);
		}

		$config = $config ?? config('Migrations');

		return new MigrationRunner($config, $db);
	}

	//--------------------------------------------------------------------

	/**
	 * The Negotiate class provides the content negotiation features for
	 * working the request to determine correct language, encoding, charset,
	 * and more.
	 *
	 * @param \CodeIgniter\HTTP\RequestInterface|null $request
	 * @param boolean                                 $getShared
	 *
	 * @return \CodeIgniter\HTTP\Negotiate
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
	 * @param \Config\Pager|null                       $config
	 * @param \CodeIgniter\View\RendererInterface|null $view
	 * @param boolean                                  $getShared
	 *
	 * @return \CodeIgniter\Pager\Pager
	 */
	public static function pager(PagerConfig $config = null, RendererInterface $view = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('pager', $config, $view);
		}

		$config = $config ?? config('Pager');
		$view   = $view ?? static::renderer();

		return new Pager($config, $view);
	}

	//--------------------------------------------------------------------

	/**
	 * The Parser is a simple template parser.
	 *
	 * @param string|null       $viewPath
	 * @param \Config\View|null $config
	 * @param boolean           $getShared
	 *
	 * @return \CodeIgniter\View\Parser
	 */
	public static function parser(string $viewPath = null, ViewConfig $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('parser', $viewPath, $config);
		}

		$viewPath = $viewPath ?: config('Paths')->viewDirectory;
		$config   = $config ?? config('View');

		return new Parser($config, $viewPath, static::locator(), CI_DEBUG, static::logger());
	}

	//--------------------------------------------------------------------

	/**
	 * The Renderer class is the class that actually displays a file to the user.
	 * The default View class within CodeIgniter is intentionally simple, but this
	 * service could easily be replaced by a template engine if the user needed to.
	 *
	 * @param string|null       $viewPath
	 * @param \Config\View|null $config
	 * @param boolean           $getShared
	 *
	 * @return \CodeIgniter\View\View
	 */
	public static function renderer(string $viewPath = null, ViewConfig $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('renderer', $viewPath, $config);
		}

		$viewPath = $viewPath ?: config('Paths')->viewDirectory;
		$config   = $config ?? config('View');

		return new View($config, $viewPath, static::locator(), CI_DEBUG, static::logger());
	}

	//--------------------------------------------------------------------

	/**
	 * The Request class models an HTTP request.
	 *
	 * @param \Config\App|null $config
	 * @param boolean          $getShared
	 *
	 * @return \CodeIgniter\HTTP\IncomingRequest
	 */
	public static function request(App $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('request', $config);
		}

		$config = $config ?? config('App');

		return new IncomingRequest(
			$config,
			static::uri(),
			'php://input',
			new UserAgent()
		);
	}

	//--------------------------------------------------------------------

	/**
	 * The Response class models an HTTP response.
	 *
	 * @param \Config\App|null $config
	 * @param boolean          $getShared
	 *
	 * @return \CodeIgniter\HTTP\Response
	 */
	public static function response(App $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('response', $config);
		}

		$config = $config ?? config('App');

		return new Response($config);
	}

	//--------------------------------------------------------------------

	/**
	 * The Redirect class provides nice way of working with redirects.
	 *
	 * @param \Config\App|null $config
	 * @param boolean          $getShared
	 *
	 * @return RedirectResponse
	 */
	public static function redirectResponse(App $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('redirectResponse', $config);
		}

		$config   = $config ?? config('App');
		$response = new RedirectResponse($config);
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
	 * @return \CodeIgniter\Router\RouteCollection
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
	 * @param \CodeIgniter\Router\RouteCollectionInterface|null $routes
	 * @param \CodeIgniter\HTTP\Request|null                    $request
	 * @param boolean                                           $getShared
	 *
	 * @return \CodeIgniter\Router\Router
	 */
	public static function router(RouteCollectionInterface $routes = null, Request $request = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('router', $routes, $request);
		}

		$routes  = $routes ?? static::routes();
		$request = $request ?? static::request();

		return new Router($routes, $request);
	}

	//--------------------------------------------------------------------

	/**
	 * The Security class provides a few handy tools for keeping the site
	 * secure, most notably the CSRF protection tools.
	 *
	 * @param \Config\App|null $config
	 * @param boolean          $getShared
	 *
	 * @return \CodeIgniter\Security\Security
	 */
	public static function security(App $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('security', $config);
		}

		$config = $config ?? config('App');

		return new Security($config);
	}

	//--------------------------------------------------------------------

	/**
	 * Return the session manager.
	 *
	 * @param \Config\App|null $config
	 * @param boolean          $getShared
	 *
	 * @return \CodeIgniter\Session\Session
	 */
	public static function session(App $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('session', $config);
		}

		$config = $config ?? config('App');
		$logger = static::logger();

		$driverName = $config->sessionDriver;
		$driver     = new $driverName($config, static::request()->getIPAddress());
		$driver->setLogger($logger);

		$session = new Session($driver, $config);
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
	 * @return \CodeIgniter\Throttle\Throttler
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
	 * @return \CodeIgniter\Debug\Timer
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
	 * @param \Config\Toolbar|null $config
	 * @param boolean              $getShared
	 *
	 * @return \CodeIgniter\Debug\Toolbar
	 */
	public static function toolbar(ToolbarConfig $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('toolbar', $config);
		}

		$config = $config ?? config('Toolbar');

		return new Toolbar($config);
	}

	//--------------------------------------------------------------------

	/**
	 * The URI class provides a way to model and manipulate URIs.
	 *
	 * @param string  $uri
	 * @param boolean $getShared
	 *
	 * @return \CodeIgniter\HTTP\URI
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
	 * @param \Config\Validation|null $config
	 * @param boolean                 $getShared
	 *
	 * @return \CodeIgniter\Validation\Validation
	 */
	public static function validation(ValidationConfig $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('validation', $config);
		}

		$config = $config ?? config('Validation');

		return new Validation($config, static::renderer());
	}

	//--------------------------------------------------------------------

	/**
	 * View cells are intended to let you insert HTML into view
	 * that has been generated by any callable in the system.
	 *
	 * @param boolean $getShared
	 *
	 * @return \CodeIgniter\View\Cell
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
	 * @return \CodeIgniter\Typography\Typography
	 */
	public static function typography(bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('typography');
		}

		return new Typography();
	}

	//--------------------------------------------------------------------
}
