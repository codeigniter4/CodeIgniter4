<?php

/**
 * System Initialization File
 *
 * Loads the base classes and executes the request.
 *
 * @package CodeIgniter
 */

/**
 * CodeIgniter version
 *
 * @var string
 */

define('CI_VERSION', '4.0-dev');

/*
 * ------------------------------------------------------
 *  Load the framework constants
 * ------------------------------------------------------
 */

if (file_exists(APPPATH.'config/'.ENVIRONMENT.'/constants.php'))
{
	require_once APPPATH.'config/'.ENVIRONMENT.'/constants.php';
}

require_once(APPPATH.'config/Constants.php');

/*
 * ------------------------------------------------------
 *  Load the global functions
 * ------------------------------------------------------
 */

require_once BASEPATH.'Common.php';

/*
 * ------------------------------------------------------
 *  Load any environment-specific settings from .env file
 * ------------------------------------------------------
 */
if (ENVIRONMENT !== 'production')
{
	// Load environment settings from .env files
	// into $_SERVER and $_ENV
	require BASEPATH.'Config/DotEnv.php';
	$env = new \CodeIgniter\Config\DotEnv(APPPATH);
	$env->load();
	unset($env);
}

/*
 * ------------------------------------------------------
 *  Get the Services Factory ready for use
 * ------------------------------------------------------
 */

require APPPATH.'config/Services.php';

/*
 * ------------------------------------------------------
 *  Setup the autoloader
 * ------------------------------------------------------
 */

// The autloader isn't initialized yet, so load the file manually.
require BASEPATH.'Autoloader/Autoloader.php';
require APPPATH.'config/AutoloadConfig.php';

// The Autoloader class only handles namespaces
// and "legacy" support.
$loader = \App\Config\Services::autoloader();
$loader->initialize(new App\Config\AutoloadConfig());

// The register function will prepend
// the psr4 loader.
$loader->register();

/*
 * ------------------------------------------------------
 *  Set custom exception handling
 * ------------------------------------------------------
 */
\App\Config\Services::exceptions(true)
   ->initialize();

//--------------------------------------------------------------------
// Start the Benchmark
//--------------------------------------------------------------------

$benchmark = \App\Config\Services::timer(true);
$benchmark->start('total_execution');

//--------------------------------------------------------------------
// CSRF Protection
//--------------------------------------------------------------------

$config = new \App\Config\AppConfig();

if ($config->CSRFProtection === true && ! is_cli())
{
	$security = \App\Config\Services::security($config);

	$security->CSRFVerify();
}

//--------------------------------------------------------------------
// Get our Request and Response objects
//--------------------------------------------------------------------

$request  = is_cli()
		? \App\Config\Services::clirequest($config)
		: \App\Config\Services::request($config);
$response = \App\Config\Services::response();

// Assume success until proven otherwise.
$response->setStatusCode(200);

//--------------------------------------------------------------------
// Try to Route It
//--------------------------------------------------------------------

require APPPATH.'config/Routes.php';

$router = \App\Config\Services::router($routes, true);

$path = is_cli() ? $request->getPath() : $request->uri->getPath();
$controller = $router->handle($path);

ob_start();

// Is it routed to a Closure?
if (is_callable($controller))
{
	echo $controller(...$router->params());
}
else
{
	if (empty($controller))
	{
		// Show the 404 error page
		if (is_cli())
		{
			require APPPATH.'views/errors/cli/error_404.php';
		}
		else
		{
			require APPPATH.'views/errors/html/error_404.php';
		}

		$response->setStatusCode(404);
	}
	else
	{
		if (! class_exists($controller))
		{
			require APPPATH.'controllers/'.$router->directory().$router->controllerName().'.php';
		}

		$class  = new $controller($request, $response);
		$method = $router->methodName();
		$class->$method(...$router->params());
	}
}

$output = ob_get_contents();
ob_end_clean();

$output = str_replace('{elapsed_time}', $benchmark->getElapsedTime('total_execution'), $output);

$response->setBody($output);

$response->send();
