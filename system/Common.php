<?php
/**
 * Common Functions
 *
 * Several application-wide utility methods.
 *
 * @package  CodeIgniter
 * @category Common Functions
 */

if (! function_exists('log_message'))
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
	function log_message(string $level, $message, array $context=[])
	{
		// @todo Don't create a new class each time!
		return \App\Config\Services::logger()->log($level, $message, $context);
	}
}

//--------------------------------------------------------------------

if (! function_exists('view'))
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
	function view(string $name, array $data=[], array $options=[])
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

if ( ! function_exists('is_cli'))
{

	/**
	 * Is CLI?
	 *
	 * Test to see if a request was made from the command line.
	 *
	 * @return 	bool
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
