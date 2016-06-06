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


use CodeIgniter\Router\RouteCollectionInterface;
use Config\App;
use CodeIgniter\Services;
use CodeIgniter\Hooks\Hooks;

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
		$this->forceSecureAccess();

		try
		{
			$this->tryToRouteIt($routes);

			//--------------------------------------------------------------------
			// Are there any "pre-controller" hooks?
			//--------------------------------------------------------------------
			Hooks::trigger('pre_controller');

			$this->startController();

			// Closure controller has run in startController().
			if ( ! is_callable($this->controller))
			{
				$controller = $this->createController();

				//--------------------------------------------------------------------
				// Is there a "post_controller_constructor" hook?
				//--------------------------------------------------------------------
				Hooks::trigger('post_controller_constructor');

				$this->runController($controller);
			}

			//--------------------------------------------------------------------
			// Is there a "post_controller" hook?
			//--------------------------------------------------------------------
			Hooks::trigger('post_controller');

			$this->gatherOutput();
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
	 * CSRF Protection. Checks if it's enabled globally, and
	 * enforces the presence of CSRF tokens.
	 */
	protected function CsrfProtection()
	{
		if ($this->config->CSRFProtection !== true || is_cli())
		{
			return;
		}

		$security = Services::security($this->config);

		$security->CSRFVerify($this->request);
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
		if (is_callable($this->controller))
		{
			$controller = $this->controller;
			echo $controller(...$this->router->params());
		}
		else
		{
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
	 */
	protected function runController($class)
	{
		if (method_exists($class, '_remap'))
		{
			$class->_remap($this->method, ...$this->router->params());
		}
		else
		{
			$class->{$this->method}(...$this->router->params());
		}

		$this->benchmark->stop('controller');
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
	protected function gatherOutput()
	{
		$this->output = ob_get_contents();
		ob_end_clean();

		$totalTime    = $this->benchmark->getElapsedTime('total_execution');

		$this->output = str_replace('{elapsed_time}', $totalTime, $this->output);

		//--------------------------------------------------------------------
		// Display the Debug Toolbar?
		//--------------------------------------------------------------------
		if ( ! is_cli() && ENVIRONMENT != 'production' && $this->config->toolbarEnabled)
		{
			$toolbar = Services::toolbar($this->config);
			$this->output .= $toolbar->run($this->startTime, $totalTime,
				$this->startMemory, $this->request,
				$this->response);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Sends the output of this request back to the client.
	 * This is what they've been waiting for!
	 */
	protected function sendResponse()
	{
		$this->response->setBody($this->output);

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
