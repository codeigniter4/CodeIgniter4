<?php
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
		return \App\Config\Services::logger(false)
		                           ->log($level, $message, $context);
	}
}

//--------------------------------------------------------------------

if ( ! function_exists('view'))
{
	/**
	 * Grabs the current RenderableInterface-compatible class
	 * and tells it to render the specified view. Simply provides
	 * a convenience method that can be used in controllers,
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
	function view(string $name, array $data = [], array $options = [])
	{
		/**
		 * @var CodeIgniter\View\View $renderer
		 */
		$renderer = \App\Config\Services::renderer();

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
	 * have a route defined in the routes config file.
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
	 * Allows cleaner access to the Services config file.
	 *
	 * These are equal:
	 *  - $timer = service('timer')
	 *  - $timer = App\Config\Services::timer();
	 *
	 * @param string $name
	 * @param        ...$params
	 *
	 * @return mixed
	 */
	function service(string $name, ...$params)
	{
		return App\Config\Services::$name(...$params);
	}
}

//--------------------------------------------------------------------

if (! function_exists('sharedService'))
{
	function sharedService(string $name, ...$params)
	{
		// Ensure the number of params we are passing
		// meets the number the method expects, since
		// we have to add a 'true' as the final value
		// to return a shared instance.
		$mirror = new ReflectionMethod('App\Config\Services', $name);
		$count = -$mirror->getNumberOfParameters();

		$params = array_pad($params, $count + 1, null);

		// We add true as the final parameter to ensure
		// we are getting a shared instance.
		array_push($params, true);

		return App\Config\Services::$name(...$params);
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
		$loader = \App\Config\Services::loader(true);

		$path = $loader->locateFile($filename, 'helpers');

		if (! empty($path))
		{
			include $path;
		}
	}
}

//--------------------------------------------------------------------
