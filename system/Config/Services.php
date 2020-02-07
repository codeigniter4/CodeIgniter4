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
use CodeIgniter\Debug\Exceptions;
use CodeIgniter\Debug\Iterator;
use CodeIgniter\Debug\Timer;
use CodeIgniter\Debug\Toolbar;
use CodeIgniter\Encryption\EncrypterInterface;
use CodeIgniter\Encryption\Encryption;
use CodeIgniter\Filters\Filters;
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
use Config\App;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Database\MigrationRunner;
use CodeIgniter\View\RendererInterface;
use Config\Cache;
use Config\Images;
use Config\Logger;
use Config\Migrations;

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
	 * @param \Config\Cache $config
	 * @param boolean       $getShared
	 *
	 * @return \CodeIgniter\Cache\CacheInterface
	 */
	public static function cache(Cache $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('cache', $config);
		}

		if (! is_object($config))
		{
			$config = new Cache();
		}

		return CacheFactory::getHandler($config);
	}

	//--------------------------------------------------------------------

	/**
	 * The CLI Request class provides for ways to interact with
	 * a command line request.
	 *
	 * @param \Config\App $config
	 * @param boolean     $getShared
	 *
	 * @return \CodeIgniter\HTTP\CLIRequest
	 */
	public static function clirequest(App $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('clirequest', $config);
		}

		if (! is_object($config))
		{
			$config = config(App::class);
		}

		return new CLIRequest($config);
	}

	//--------------------------------------------------------------------

	/**
	 * The CURL Request class acts as a simple HTTP client for interacting
	 * with other servers, typically through APIs.
	 *
	 * @param array                               $options
	 * @param \CodeIgniter\HTTP\ResponseInterface $response
	 * @param \Config\App                         $config
	 * @param boolean                             $getShared
	 *
	 * @return \CodeIgniter\HTTP\CURLRequest
	 */
	public static function curlrequest(array $options = [], ResponseInterface $response = null, App $config = null, bool $getShared = true)
	{
		if ($getShared === true)
		{
			return static::getSharedInstance('curlrequest', $options, $response, $config);
		}

		if (! is_object($config))
		{
			$config = config(App::class);
		}

		if (! is_object($response))
		{
			$response = new Response($config);
		}

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
	 * @param null    $config
	 * @param boolean $getShared
	 *
	 * @return \CodeIgniter\Email\Email|mixed
	 */
	public static function email($config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('email', $config);
		}
		if (empty($config))
		{
			$config = new \Config\Email();
		}
		$email = new \CodeIgniter\Email\Email($config);
		return $email;
	}

	/**
	 * The Encryption class provides two-way encryption.
	 *
	 * @param mixed   $config
	 * @param boolean $getShared
	 *
	 * @return EncrypterInterface Encryption handler
	 */
	public static function encrypter($config = null, $getShared = false)
	{
		if ($getShared === true)
		{
			return static::getSharedInstance('encrypter', $config);
		}

		if (empty($config))
		{
			$config = new \Config\Encryption();
		}

		$encryption = new Encryption($config);
		$encrypter  = $encryption->initialize($config);
		return $encrypter;
	}

	//--------------------------------------------------------------------

	/**
	 * The Exceptions class holds the methods that handle:
	 *
	 *  - set_exception_handler
	 *  - set_error_handler
	 *  - register_shutdown_function
	 *
	 * @param \Config\Exceptions                $config
	 * @param \CodeIgniter\HTTP\IncomingRequest $request
	 * @param \CodeIgniter\HTTP\Response        $response
	 * @param boolean                           $getShared
	 *
	 * @return \CodeIgniter\Debug\Exceptions
	 */
	public static function exceptions(
		\Config\Exceptions $config = null,
		IncomingRequest $request = null,
		Response $response = null,
		bool $getShared = true
	)
	{
		if ($getShared)
		{
			return static::getSharedInstance('exceptions', $config, $request, $response);
		}

		if (empty($config))
		{
			$config = new \Config\Exceptions();
		}

		if (empty($request))
		{
			$request = static::request();
		}

		if (empty($response))
		{
			$response = static::response();
		}

		return (new Exceptions($config, $request, $response));
	}

	//--------------------------------------------------------------------

	/**
	 * Filters allow you to run tasks before and/or after a controller
	 * is executed. During before filters, the request can be modified,
	 * and actions taken based on the request, while after filters can
	 * act on or modify the response itself before it is sent to the client.
	 *
	 * @param mixed   $config
	 * @param boolean $getShared
	 *
	 * @return \CodeIgniter\Filters\Filters
	 */
	public static function filters($config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('filters', $config);
		}

		if (empty($config))
		{
			$config = new \Config\Filters();
		}

		return new Filters($config, static::request(), static::response());
	}

	//--------------------------------------------------------------------

	/**
	 * The Honeypot provides a secret input on forms that bots should NOT
	 * fill in, providing an additional safeguard when accepting user input.
	 *
	 * @param \CodeIgniter\Config\BaseConfig|null $config
	 * @param boolean                             $getShared
	 *
	 * @return \CodeIgniter\Honeypot\Honeypot|mixed
	 */
	public static function honeypot(BaseConfig $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('honeypot', $config);
		}

		if (is_null($config))
		{
			$config = new \Config\Honeypot();
		}

		return new Honeypot($config);
	}

	//--------------------------------------------------------------------

	/**
	 * Acts as a factory for ImageHandler classes and returns an instance
	 * of the handler. Used like Services::image()->withFile($path)->rotate(90)->save();
	 *
	 * @param string  $handler
	 * @param mixed   $config
	 * @param boolean $getShared
	 *
	 * @return \CodeIgniter\Images\Handlers\BaseHandler
	 */
	public static function image(string $handler = null, $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('image', $handler, $config);
		}

		if (empty($config))
		{
			$config = new Images();
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
	 * @param string  $locale
	 * @param boolean $getShared
	 *
	 * @return \CodeIgniter\Language\Language
	 */
	public static function language(string $locale = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('language', $locale)
							->setLocale($locale);
		}

		$locale = ! empty($locale) ? $locale : static::request()
						->getLocale();

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

		return new \CodeIgniter\Log\Logger(new Logger());
	}

	//--------------------------------------------------------------------

	/**
	 * Return the appropriate Migration runner.
	 *
	 * @param \CodeIgniter\Config\BaseConfig            $config
	 * @param \CodeIgniter\Database\ConnectionInterface $db
	 * @param boolean                                   $getShared
	 *
	 * @return \CodeIgniter\Database\MigrationRunner
	 */
	public static function migrations(BaseConfig $config = null, ConnectionInterface $db = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('migrations', $config, $db);
		}

		$config = empty($config) ? new Migrations() : $config;

		return new MigrationRunner($config, $db);
	}

	//--------------------------------------------------------------------

	/**
	 * The Negotiate class provides the content negotiation features for
	 * working the request to determine correct language, encoding, charset,
	 * and more.
	 *
	 * @param \CodeIgniter\HTTP\RequestInterface $request
	 * @param boolean                            $getShared
	 *
	 * @return \CodeIgniter\HTTP\Negotiate
	 */
	public static function negotiator(RequestInterface $request = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('negotiator', $request);
		}

		if (is_null($request))
		{
			$request = static::request();
		}

		return new Negotiate($request);
	}

	//--------------------------------------------------------------------

	/**
	 * Return the appropriate pagination handler.
	 *
	 * @param mixed                               $config
	 * @param \CodeIgniter\View\RendererInterface $view
	 * @param boolean                             $getShared
	 *
	 * @return \CodeIgniter\Pager\Pager
	 */
	public static function pager($config = null, RendererInterface $view = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('pager', $config, $view);
		}

		if (empty($config))
		{
			$config = new \Config\Pager();
		}

		if (! $view instanceof RendererInterface)
		{
			$view = static::renderer();
		}

		return new Pager($config, $view);
	}

	//--------------------------------------------------------------------

	/**
	 * The Parser is a simple template parser.
	 *
	 * @param string  $viewPath
	 * @param mixed   $config
	 * @param boolean $getShared
	 *
	 * @return \CodeIgniter\View\Parser
	 */
	public static function parser(string $viewPath = null, $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('parser', $viewPath, $config);
		}

		if (is_null($config))
		{
			$config = new \Config\View();
		}

		if (is_null($viewPath))
		{
			$paths    = config('Paths');
			$viewPath = $paths->viewDirectory;
		}

		return new Parser($config, $viewPath, static::locator(true), CI_DEBUG, static::logger(true));
	}

	//--------------------------------------------------------------------

	/**
	 * The Renderer class is the class that actually displays a file to the user.
	 * The default View class within CodeIgniter is intentionally simple, but this
	 * service could easily be replaced by a template engine if the user needed to.
	 *
	 * @param string  $viewPath
	 * @param mixed   $config
	 * @param boolean $getShared
	 *
	 * @return \CodeIgniter\View\View
	 */
	public static function renderer(string $viewPath = null, $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('renderer', $viewPath, $config);
		}

		if (is_null($config))
		{
			$config = new \Config\View();
		}

		if (is_null($viewPath))
		{
			$paths = config('Paths');

			$viewPath = $paths->viewDirectory;
		}

		return new \CodeIgniter\View\View($config, $viewPath, static::locator(true), CI_DEBUG, static::logger(true));
	}

	//--------------------------------------------------------------------

	/**
	 * The Request class models an HTTP request.
	 *
	 * @param \Config\App $config
	 * @param boolean     $getShared
	 *
	 * @return \CodeIgniter\HTTP\IncomingRequest
	 */
	public static function request(App $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('request', $config);
		}

		if (! is_object($config))
		{
			$config = config(App::class);
		}

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
	 * @param \Config\App $config
	 * @param boolean     $getShared
	 *
	 * @return \CodeIgniter\HTTP\Response
	 */
	public static function response(App $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('response', $config);
		}

		if (! is_object($config))
		{
			$config = config(App::class);
		}

		return new Response($config);
	}

	//--------------------------------------------------------------------

	/**
	 * The Redirect class provides nice way of working with redirects.
	 *
	 * @param \Config\App $config
	 * @param boolean     $getShared
	 *
	 * @return \CodeIgniter\HTTP\Response
	 */
	public static function redirectResponse(App $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('redirectResponse', $config);
		}

		if (! is_object($config))
		{
			$config = config(App::class);
		}

		$response = new RedirectResponse($config);
		$response->setProtocolVersion(static::request()
						->getProtocolVersion());

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
	 * @param \CodeIgniter\Router\RouteCollectionInterface $routes
	 * @param \CodeIgniter\HTTP\Request                    $request
	 * @param boolean                                      $getShared
	 *
	 * @return \CodeIgniter\Router\Router
	 */
	public static function router(RouteCollectionInterface $routes = null, Request $request = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('router', $routes, $request);
		}

		if (empty($routes))
		{
			$routes = static::routes(true);
		}

		return new Router($routes, $request);
	}

	//--------------------------------------------------------------------

	/**
	 * The Security class provides a few handy tools for keeping the site
	 * secure, most notably the CSRF protection tools.
	 *
	 * @param \Config\App $config
	 * @param boolean     $getShared
	 *
	 * @return \CodeIgniter\Security\Security
	 */
	public static function security(App $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('security', $config);
		}

		if (! is_object($config))
		{
			$config = config(App::class);
		}

		return new Security($config);
	}

	//--------------------------------------------------------------------

	/**
	 * Return the session manager.
	 *
	 * @param \Config\App $config
	 * @param boolean     $getShared
	 *
	 * @return \CodeIgniter\Session\Session
	 */
	public static function session(App $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('session', $config);
		}

		if (! is_object($config))
		{
			$config = config(App::class);
		}

		$logger = static::logger(true);

		$driverName = $config->sessionDriver;
		$driver     = new $driverName($config, static::request()->getIpAddress());
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
	 * @param \Config\Toolbar $config
	 * @param boolean         $getShared
	 *
	 * @return \CodeIgniter\Debug\Toolbar
	 */
	public static function toolbar(\Config\Toolbar $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('toolbar', $config);
		}

		if (! is_object($config))
		{
			$config = config('Toolbar');
		}

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
	 * @param \Config\Validation $config
	 * @param boolean            $getShared
	 *
	 * @return \CodeIgniter\Validation\Validation
	 */
	public static function validation(\Config\Validation $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('validation', $config);
		}

		if (is_null($config))
		{
			$config = config('Validation');
		}

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
