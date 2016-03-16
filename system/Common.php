<?php
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
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Common Functions
 *
 * Several application-wide utility methods.
 *
 * @package  CodeIgniter
 * @category Common Functions
 */

if ( ! function_exists('log_message'))
{
	/**
	 * A convenience/compatibility method for logging events through
	 * the Log system.
	 *
	 * Allowed log levels are:
	 *  - emergency
	 *  - alert
	 *  - critical
	 *  - error
	 *  - warning
	 *  - notice
	 *  - info
	 *  - debug
	 *
	 * @param string $level
	 * @param        $message
	 * @param array  $context
	 *
	 * @return mixed
	 */
	function log_message(string $level, $message, array $context = [])
	{
		// When running tests, we want to always ensure that the
		// TestLogger is running, which provides utilities for
		// for asserting that logs were called in the test code.
		if (ENVIRONMENT == 'testing')
		{
			$logger = new \CodeIgniter\Log\TestLogger(new \Config\Logger());
			return $logger->log($level, $message, $context);
		}

		return \Config\Services::logger(true)
									->log($level, $message, $context);
	}
}

//--------------------------------------------------------------------

if ( ! function_exists('load_view'))
{
	/**
	 * Grabs the current RenderableInterface-compatible class
	 * and tells it to render the specified view. Simply provides
	 * a convenience method that can be used in Controllers,
	 * libraries, and routed closures.
	 *
	 * NOTE: Does not provide any escaping of the data, so that must
	 * all be handled manually by the developer.
	 *
	 * @param string $name
	 * @param array  $data
	 * @param array  $options Unused - reserved for third-party extensions.
	 *
	 * @return string
	 */
	function load_view(string $name, array $data = [], array $options = [])
	{
		/**
		 * @var CodeIgniter\View\View $renderer
		 */
		$renderer = \Config\Services::renderer(null, true);

		return $renderer->setData($data, 'raw')
		                ->render($name, $options);
	}
}

//--------------------------------------------------------------------

if ( ! function_exists('esc'))
{
	/**
	 * Performs simple auto-escaping of data for security reasons.
	 * Might consider making this more complex at a later date.
	 *
	 * If $data is a string, then it simply escapes and returns it.
	 * If $data is an array, then it loops over it, escaping each
	 * 'value' of the key/value pairs.
	 *
	 * Valid context values: html, js, css, url, attr, raw, null
	 *
	 * @param string|array $data
	 * @param string       $context
	 * @param string       $encoding
	 *
	 * @return $data
	 */
	function esc($data, $context = 'html', $encoding=null)
	{
		if (is_array($data))
		{
			foreach ($data as $key => &$value)
			{
				$value = esc($value, $context);
			}
		}

		if (is_string($data))
		{
			$context = strtolower($context);

			// Provide a way to NOT escape data since
			// this could be called automatically by
			// the View library.
			if (empty($context) || $context == 'raw')
			{
				return $data;
			}

			if ( ! in_array($context, ['html', 'js', 'css', 'url', 'attr']))
			{
				throw new \InvalidArgumentException('Invalid escape context provided.');
			}

			if ($context == 'attr')
			{
				$method = 'escapeHtmlAttr';
			}
			else
			{
				$method = 'escape'.ucfirst($context);
			}

			$escaper = new \Zend\Escaper\Escaper($encoding);

			$data   = $escaper->$method($data);
		}

		return $data;
	}
}

//--------------------------------------------------------------------

if ( ! function_exists('is_cli'))
{

	/**
	 * Is CLI?
	 *
	 * Test to see if a request was made from the command line.
	 *
	 * @return    bool
	 */
	function is_cli()
	{
		return (PHP_SAPI === 'cli' OR defined('STDIN'));
	}
}

//--------------------------------------------------------------------

if ( ! function_exists('route_to'))
{
	/**
	 * Given a controller/method string and any params,
	 * will attempt to build the relative URL to the
	 * matching route.
	 *
	 * NOTE: This requires the controller/method to
	 * have a route defined in the routes Config file.
	 *
	 * @param string $method
	 * @param        ...$params
	 *
	 * @return \CodeIgniter\Router\string
	 */
	function route_to(string $method, ...$params): string
	{
		global $routes;

		return $routes->reverseRoute($method, ...$params);
	}
}

//--------------------------------------------------------------------

if (! function_exists('service'))
{
	/**
	 * Allows cleaner access to the Services Config file.
	 *
	 * These are equal:
	 *  - $timer = service('timer')
	 *  - $timer = Config\Services::timer();
	 *
	 * @param string $name
	 * @param        ...$params
	 *
	 * @return mixed
	 */
	function service(string $name, ...$params)
	{
		return Config\Services::$name(...$params);
	}
}

//--------------------------------------------------------------------

if (! function_exists('shared_service'))
{
	function shared_service(string $name, ...$params)
	{
		// Ensure the number of params we are passing
		// meets the number the method expects, since
		// we have to add a 'true' as the final value
		// to return a shared instance.
		$mirror = new ReflectionMethod('Config\Services', $name);
		$count = -$mirror->getNumberOfParameters();

		$params = array_pad($params, $count + 1, null);

		// We add true as the final parameter to ensure
		// we are getting a shared instance.
		array_push($params, true);

		return Config\Services::$name(...$params);
	}
}

if ( ! function_exists('remove_invisible_characters'))
{
	/**
	 * Remove Invisible Characters
	 *
	 * This prevents sandwiching null characters
	 * between ascii characters, like Java\0script.
	 *
	 * @param	string
	 * @param	bool
	 * @return	string
	 */
	function remove_invisible_characters($str, $url_encoded = TRUE)
	{
		$non_displayables = array();

		// every control character except newline (dec 10),
		// carriage return (dec 13) and horizontal tab (dec 09)
		if ($url_encoded)
		{
			$non_displayables[] = '/%0[0-8bcef]/';	// url encoded 00-08, 11, 12, 14, 15
			$non_displayables[] = '/%1[0-9a-f]/';	// url encoded 16-31
		}

		$non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';	// 00-08, 11, 12, 14-31, 127

		do
		{
			$str = preg_replace($non_displayables, '', $str, -1, $count);
		}
		while ($count);

		return $str;
	}
}

//--------------------------------------------------------------------

if (! function_exists('load_helper'))
{
	/**
	 * Loads a helper file into memory. Supports namespaced helpers,
	 * both in and out of the 'helpers' directory of a namespaced directory.
	 *
	 * @param string $filename
	 *
	 * @return string
	 */
	function load_helper(string $filename): string
	{
		$loader = \Config\Services::loader(true);

		$path = $loader->locateFile($filename, 'helpers');

		if (! empty($path))
		{
			include $path;
		}
	}
}

//--------------------------------------------------------------------

if (! function_exists('get_csrf_token_name'))
{
	/**
	 * Returns the CSRF token name.
	 * Can be used in Views when building hidden inputs manually,
	 * or used in javascript vars when using APIs.
	 *
	 * @return string
	 */
	function get_csrf_token_name()
	{
		$config = new \Config\App();

		return $config->CSRFTokenName;
	}
}

//--------------------------------------------------------------------

if (! function_exists('get_csrf_hash'))
{
	/**
	 * Returns the current hash value for the CSRF protection.
	 * Can be used in Views when building hidden inputs manually,
	 * or used in javascript vars for API usage.
	 *
	 * @return string
	 */
	function get_csrf_hash()
	{
		$security = \Config\Services::security(null, true);

		return $security->getCSRFHash();
	}
}

//--------------------------------------------------------------------

if (! function_exists('force_https'))
{
	/**
	 * Used to force a page to be accessed in via HTTPS.
	 * Uses a standard redirect, plus will set the HSTS header
	 * for modern browsers that support, which gives best
	 * protection against man-in-the-middle attacks.
	 *
	 * @see https://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security
	 *
	 * @param int $duration How long should the SSL header be set for? (in seconds)
	 *                      Defaults to 1 year.
	 */
	function force_https(int $duration = 31536000, RequestInterface $request = null, ResponseInterface $response = null)
	{
		if (is_null($request)) global $request;
		if (is_null($response)) global $response;

		if ($request->isSecure())
		{
			return;
		}

		// If the session library is loaded, we should regenerate
		// the session ID for safety sake.
		if (class_exists('Session', false))
		{
			\Config\Services::session(null, true)->regenerate();
		}

		$uri = $request->uri;
		$uri->setScheme('https');

		$uri = \CodeIgniter\HTTP\URI::createURIString(
			$uri->getScheme(),
			$uri->getAuthority(true),
			$uri->getPath(), // Absolute URIs should use a "/" for an empty path
			$uri->getQuery(),
			$uri->getFragment()
		);

		// Set an HSTS header
		$response->setHeader('Strict-Transport-Security', 'max-age='.$duration);
		$response->redirect($uri);
		exit();
	}
}

//--------------------------------------------------------------------

if (! function_exists('redirect'))
{
	/**
	 * Convenience method that works with the current global $request and
	 * $router instances to redirect using named/reverse-routed routes
	 * to determine the URL to go to. If nothing is found, will treat
	 * as a traditional redirect and pass the string in, letting
	 * $response->redirect() determine the correct method and code.
	 *
	 * If more control is needed, you must use $response->redirect explicitly.
	 *
	 * @param string   $uri
	 */
	function redirect (string $uri, ...$params)
	{
		global $response, $routes;

		if ($route = $routes->reverseRoute($uri, ...$params))
		{
			$uri = $route;
		}

		$response->redirect($uri);
		exit(EXIT_SUCCESS);
	}
}

//--------------------------------------------------------------------

