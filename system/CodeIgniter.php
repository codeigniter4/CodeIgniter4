<?php namespace CodeIgniter;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
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
 * @package      CodeIgniter
 * @author       CodeIgniter Dev Team
 * @copyright    Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license      http://opensource.org/licenses/MIT	MIT License
 * @link         http://codeigniter.com
 * @since        Version 3.0.0
 * @filesource
 */


use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Router\RouteCollectionInterface;
use Config\App;
use CodeIgniter\Services;
use CodeIgniter\Hooks\Hooks;
use Config\Cache;

/**
 * System Initialization Class
 *
 * Loads the base classes and executes the request.
 */
class CodeIgniter
{
	/**
	 * The current version of CodeIgniter Framework
	 */
	const CI_VERSION = '4.0-dev';

	/**
	 * UNIX timestamp for the start of script execution
	 * in seconds with microseconds.
	 *
	 * @var float
	 */
	protected $startMemory;

	/**
	 * App start time
	 *
	 * @var float
	 */
	protected $startTime;

	/**
	 * Total app execution time
	 *
	 * @var float
	 */
	protected $totalTime;

	/**
	 * The application configuration object.
	 *
	 * @var \Config\App
	 */
	protected $config;

	/**
	 * Current request.
	 *
	 * @var \CodeIgniter\HTTP\Request
	 */
	protected $request;

	/**
	 * Current response.
	 *
	 * @var \CodeIgniter\HTTP\Response
	 */
	protected $response;

	/**
	 * Router to use.
	 *
	 * @var \CodeIgniter\Router\Router
	 */
	protected $router;

	/**
	 * Controller to use.
	 * @var string|\Closure
	 */
	protected $controller;

	/**
	 * Controller method to invoke.
	 *
	 * @var string
	 */
	protected $method;

	/**
	 * Output handler to use.
	 * @var string
	 */
	protected $output;

	/**
	 * Cache expiration time
	 * @var int
	 */
	protected static $cacheTTL = 0;

	//--------------------------------------------------------------------

	/**
	 * CodeIgniter constructor.
	 *
	 * @param int $startMemory
	 * @param float $startTime
	 * @param App $config
	 */
	public function __construct(int $startMemory, float $startTime, App $config)
	{
		$this->startMemory = $startMemory;
		$this->startTime   = $startTime;
		$this->config = $config;
	}

	//--------------------------------------------------------------------

	/**
	 * The class entry point. This is where the magic happens and all
	 * of the framework pieces are pulled together and shown how to
	 * make beautiful music together. Or something like that. :)
	 *
	 * @param RouteCollectionInterface $routes
	 */
	public function run(RouteCollectionInterface $routes = null)
	{
		$this->startBenchmark();

		//--------------------------------------------------------------------
		// Is there a "pre-system" hook?
		//--------------------------------------------------------------------
		Hooks::trigger('pre_system');

		$this->getRequestObject();
		$this->getResponseObject();

		// Check for a cached page. Execution will stop
		// if the page has been cached.
		$cacheConfig = new Cache();
		$this->displayCache($cacheConfig);

		$this->forceSecureAccess();

		try
		{
			$this->tryToRouteIt($routes);

			//--------------------------------------------------------------------
			// Run "before" filters
			//--------------------------------------------------------------------
			$filters = Services::filters();
			$uri = $this->request instanceof CLIRequest
				? $this->request->getPath()
				: $this->request->uri->getPath();

			$filters->run($uri, 'before');

			$returned = $this->startController();

			// Closure controller has run in startController().
			if ( ! is_callable($this->controller))
			{
				$controller = $this->createController();

				//--------------------------------------------------------------------
				// Is there a "post_controller_constructor" hook?
				//--------------------------------------------------------------------
				Hooks::trigger('post_controller_constructor');

				$returned = $this->runController($controller);
			}

			// If $returned is a string, then the controller output something,
			// probably a view, instead of echoing it directly. Send it along
			// so it can be used with the output.
			$this->gatherOutput($cacheConfig, $returned);

			//--------------------------------------------------------------------
			// Run "after" filters
			//--------------------------------------------------------------------
			$response = $filters->run($uri, 'after');

			if ($response instanceof Response)
			{
				$this->response = $response;
			}

			// Save our current URI as the previous URI in the session
            // for safer, more accurate use with `previous_url()` helper function.
            $this->storePreviousURL($uri);

			unset($uri);

			$this->sendResponse();

			//--------------------------------------------------------------------
			// Is there a post-system hook?
			//--------------------------------------------------------------------
			Hooks::trigger('post_system');
		}
		catch (Router\RedirectException $e)
		{
			$logger = Services::logger();
			$logger->info('REDIRECTED ROUTE at '.$e->getMessage());

			// If the route is a 'redirect' route, it throws
			// the exception with the $to as the message
			$this->response->redirect($e->getMessage(), 'auto', $e->getCode());
			$this->callExit(EXIT_SUCCESS);
		}
		// Catch Response::redirect()
		catch (HTTP\RedirectException $e)
		{
			$this->callExit(EXIT_SUCCESS);
		}
		catch (PageNotFoundException $e)
		{
			$this->display404errors($e);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Determines if a response has been cached for the given URI.
	 *
	 * @param \Config\Cache $config
	 *
	 * @return bool
	 */
	 public function displayCache($config)
	 {
		 if ($cachedResponse = cache()->get($this->generateCacheName($config)))
		 {
			 $cachedResponse = unserialize($cachedResponse);
			 if (!is_array($cachedResponse) || !isset($cachedResponse['output']) || !isset($cachedResponse['headers']))
			 {
				 throw new \Exception("Error unserializing page cache");
			 }

			 $headers = $cachedResponse['headers'];
			 $output  = $cachedResponse['output'];

			 // Clear all default headers
			 foreach($this->response->getHeaders() as $key => $val) {
				 $this->response->removeHeader($key);
			 }

			 // Set cached headers
			 foreach($headers as $name => $value) {
				 $this->response->setHeader($name, $value);
			 }

			 $output = $this->displayPerformanceMetrics($output);
			 $this->response->setBody($output)->send();
			 $this->callExit(EXIT_SUCCESS);
		 };
	 }


	//--------------------------------------------------------------------

	/**
	 * Tells the app that the final output should be cached.
	 *
	 * @param int $time
	 *
	 * @return $this
	 */
	public static function cache(int $time)
	{
	    self::$cacheTTL = (int)$time;
	}

	//--------------------------------------------------------------------

	/**
	 * Caches the full response from the current request. Used for
	 * full-page caching for very high performance.
	 *
	 * @param \Config\Cache $config
	 */
	public function cachePage(Cache $config)
	{
		$headers = [];
		foreach($this->response->getHeaders() as $header) {
			$headers[$header->getName()] = $header->getValueLine();
		}

		return cache()->save(
			$this->generateCacheName($config),
			serialize(['headers' => $headers, 'output' => $this->output]),
			self::$cacheTTL
		);

	}

	//--------------------------------------------------------------------

	public function getPerfomanceStats()
	{
	    return [
	    	'startTime'	=> $this->startTime,
			'totalTime' => $this->totalTime,
			'startMemory' => $this->startMemory
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Generates the cache name to use for our full-page caching.
	 *
	 * @param \CodeIgniter\HTTP\URI $URI
	 *
	 * @return string
	 */
	protected function generateCacheName($config): string
	{
		if (is_cli())
		{
			return md5($this->request->getPath());
		}

		$uri = $this->request->uri;

		if ($config->cacheQueryString)
		{
			$name = URI::createURIString(
				$uri->getScheme(),
				$uri->getAuthority(),
				$uri->getPath(),
				$uri->getQuery()
			);
		}
		else
		{
			$name = URI::createURIString(
				$uri->getScheme(),
				$uri->getAuthority(),
				$uri->getPath()
			);
		}

		return md5($name);
	}

	//--------------------------------------------------------------------

	/**
	 * Replaces the memory_usage and elapsed_time tags.
	 *
	 * @param string $output
	 *
	 * @return string
	 */
	public function displayPerformanceMetrics(string $output): string
	{
		$this->totalTime = $this->benchmark->getElapsedTime('total_execution');

		$output = str_replace('{elapsed_time}', $this->totalTime, $output);

	    return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Start the Benchmark
	 *
	 * The timer is used to display total script execution both in the
	 * debug toolbar, and potentially on the displayed page.
	 */
	protected function startBenchmark()
	{
		$this->startTime = microtime(true);

		$this->benchmark = Services::timer();
		$this->benchmark->start('total_execution', $this->startTime);
		$this->benchmark->start('bootstrap');
	}

	//--------------------------------------------------------------------

	/**
	 * Get our Request object, (either IncomingRequest or CLIRequest)
	 * and set the server protocol based on the information provided
	 * by the server.
	 */
	protected function getRequestObject()
	{
		if (is_cli())
		{
			$this->request = Services::clirequest($this->config);
		}
		else
		{
			$this->request = Services::request($this->config);
			$this->request->setProtocolVersion($_SERVER['SERVER_PROTOCOL']);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Get our Response object, and set some default values, including
	 * the HTTP protocol version and a default successful response.
	 */
	protected function getResponseObject()
	{
		$this->response = Services::response($this->config);

		if ( ! is_cli())
		{
			$this->response->setProtocolVersion($this->request->getProtocolVersion());
		}

		// Assume success until proven otherwise.
		$this->response->setStatusCode(200);
	}

	//--------------------------------------------------------------------

	/**
	 * Force Secure Site Access? If the config value 'forceGlobalSecureRequests'
	 * is true, will enforce that all requests to this site are made through
	 * HTTPS. Will redirect the user to the current page with HTTPS, as well
	 * as set the HTTP Strict Transport Security header for those browsers
	 * that support it.
	 *
	 * @param int $duration  How long the Strict Transport Security
	 *                       should be enforced for this URL.
	 */
	protected function forceSecureAccess($duration = 31536000)
	{
		if ($this->config->forceGlobalSecureRequests !== true)
		{
			return;
		}

		force_https($duration, $this->request, $this->response);
	}

	//--------------------------------------------------------------------

	/**
	 * Try to Route It - As it sounds like, works with the router to
	 * match a route against the current URI. If the route is a
	 * "redirect route", will also handle the redirect.
	 *
	 * @param RouteCollectionInterface $routes  An collection interface to use in place
	 *                                          of the config file.
	 */
	protected function tryToRouteIt(RouteCollectionInterface $routes = null)
	{
		if (empty($routes) || ! $routes instanceof RouteCollectionInterface)
		{
			require APPPATH.'Config/Routes.php';
		}

		// $routes is defined in Config/Routes.php
		$this->router = Services::router($routes);

		$path = is_cli() ? $this->request->getPath() : $this->request->uri->getPath();

		$this->benchmark->stop('bootstrap');
		$this->benchmark->start('routing');

		ob_start();

		$this->controller = $this->router->handle($path);
		$this->method     = $this->router->methodName();

		// If a {locale} segment was matched in the final route,
		// then we need to set the correct locale on our Request.
		if ($this->router->hasLocale())
		{
			$this->request->setLocale($this->router->getLocale());
		}

		$this->benchmark->stop('routing');
	}

	//--------------------------------------------------------------------

	/**
	 * Now that everything has been setup, this method attempts to run the
	 * controller method and make the script go. If it's not able to, will
	 * show the appropriate Page Not Found error.
	 */
	protected function startController()
	{
		$this->benchmark->start('controller');
		$this->benchmark->start('controller_constructor');

		// Is it routed to a Closure?
		if (is_object($this->controller) && (get_class($this->controller) == 'Closure'))
		{
			$controller = $this->controller;
			return $controller(...$this->router->params());
		}
		else
		{
			// No controller specified - we don't know what to do now.
			if (empty($this->controller))
			{
				throw new PageNotFoundException('Controller is empty.');
			}
			else
			{
				// Try to autoload the class
				if ( ! class_exists($this->controller, true) || $this->method[0] === '_')
				{
					throw new PageNotFoundException('Controller or its method is not found.');
				}
				else if ( ! method_exists($this->controller, '_remap') &&
					! is_callable([$this->controller, $this->method], false)
				)
				{
					throw new PageNotFoundException('Controller method is not found.');
				}
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Instantiates the controller class.
	 *
	 * @return mixed
	 */
	protected function createController()
	{
		$class = new $this->controller($this->request, $this->response);

		$this->benchmark->stop('controller_constructor');

		return $class;
	}

	//--------------------------------------------------------------------

	/**
	 * Runs the controller, allowing for _remap methods to function.
	 *
	 * @param mixed $class
	 *
	 * @return mixed
	 */
	protected function runController($class)
	{
		if (method_exists($class, '_remap'))
		{
			$output = $class->_remap($this->method, ...$this->router->params());
		}
		else
		{
			$output = $class->{$this->method}(...$this->router->params());
		}

		$this->benchmark->stop('controller');

		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Displays a 404 Page Not Found error. If set, will try to
	 * call the 404Override controller/method that was set in routing config.
	 *
	 * @param PageNotFoundException $e
	 */
	protected function display404errors(PageNotFoundException $e)
	{
		// Is there a 404 Override available?
		if ($override = $this->router->get404Override())
		{
			if ($override instanceof \Closure)
			{
				echo $override();
			}
			else if (is_array($override))
			{
				$this->benchmark->start('controller');
				$this->benchmark->start('controller_constructor');

				$this->controller = $override[0];
				$this->method     = $override[1];

				unset($override);

				$controller = $this->createController();
				$this->runController($controller);
			}

			$this->gatherOutput();
			$this->sendResponse();

			return;
		}

		// Display 404 Errors
		$this->response->setStatusCode(404);

		if (ENVIRONMENT !== 'testing') {
			if (ob_get_level() > 0) {
				ob_end_flush();
			}
		}
		else
		{
			// When testing, one is for phpunit, another is for test case.
			if (ob_get_level() > 2) {
				ob_end_flush();
			}
		}

		ob_start();

		// These might show as unused here - but don't delete!
		// They are used within the view files.
		$heading = 'Page Not Found';
		$message = $e->getMessage();

		// Show the 404 error page
		if (is_cli())
		{
			require APPPATH.'Views/errors/cli/error_404.php';
		}
		else
		{
			require APPPATH.'Views/errors/html/error_404.php';
		}

		$buffer = ob_get_contents();
		ob_end_clean();

		echo $buffer;
		$this->callExit(EXIT_UNKNOWN_FILE);    // Unknown file
	}

	//--------------------------------------------------------------------

	/**
	 * Gathers the script output from the buffer, replaces some execution
	 * time tag in the output and displays the debug toolbar, if required.
	 */
	protected function gatherOutput($cacheConfig = null, $returned = null)
	{
		$this->output = ob_get_contents();
		ob_end_clean();

		if (is_string($returned))
		{
			$this->output .= $returned;
		}

		// Cache it without the performance metrics replaced
		// so that we can have live speed updates along the way.
		if (self::$cacheTTL > 0)
		{
			$this->cachePage($cacheConfig);
		}

		$this->output = $this->displayPerformanceMetrics($this->output);

		$this->response->setBody($this->output);
	}

	//--------------------------------------------------------------------

    /**
     * If we have a session object to use, store the current URI
     * as the previous URI. This is called just prior to sending the
     * response to the client, and will make it available next request.
     *
     * This helps provider safer, more reliable previous_url() detection.
     *
     * @param \CodeIgniter\HTTP\URI $uri
     */
    public function storePreviousURL($uri)
    {
        // This is mainly needed during testing...
        if (is_string($uri))
        {
            $uri = new URI($uri);
        }

        if (isset($_SESSION))
        {
            $_SESSION['_ci_previous_url'] = (string)$uri;
        }
    }

    //--------------------------------------------------------------------

	/**
	 * Sends the output of this request back to the client.
	 * This is what they've been waiting for!
	 */
	protected function sendResponse()
	{
		$this->response->send();
	}

	//--------------------------------------------------------------------

	/**
	 * Exits the application, setting the exit code for CLI-based applications
	 * that might be watching.
	 *
	 * Made into a separate method so that it can be mocked during testing
	 * without actually stopping script execution.
	 *
	 * @param $code
	 */
	protected function callExit($code)
	{
		exit($code);
	}

	//--------------------------------------------------------------------
}
