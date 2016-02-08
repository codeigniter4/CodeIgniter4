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

/**
 * System Initialization File
 *
 * Loads the base classes and executes the request.
 *
 * @package CodeIgniter
 */

use CodeIgniter\Config\DotEnv;
use App\Config\Services;
use CodeIgniter\Hooks\Hooks;
use App\Config\AutoloadConfig;
use App\Config\AppConfig;

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

// Load environment settings from .env files
// into $_SERVER and $_ENV
require BASEPATH.'Config/DotEnv.php';
$env = new DotEnv(APPPATH);
$env->load();
unset($env);

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

// The autoloader isn't initialized yet, so load the file manually.
require BASEPATH.'Autoloader/Autoloader.php';
require APPPATH.'config/AutoloadConfig.php';

// The Autoloader class only handles namespaces
// and "legacy" support.
$loader = Services::autoloader();
$loader->initialize(new AutoloadConfig());

// The register function will prepend
// the psr4 loader.
$loader->register();

/*
 * ------------------------------------------------------
 *  Set custom exception handling
 * ------------------------------------------------------
 */
Services::exceptions(true)
   ->initialize();

//--------------------------------------------------------------------
// Start the Benchmark
//--------------------------------------------------------------------

// Record app start time here. It's a little bit off, but
// keeps it lining up with the benchmark timers.
$startTime   = microtime(true);

$benchmark = Services::timer(true);
$benchmark->start('total_execution');
$benchmark->start('bootstrap');

//--------------------------------------------------------------------
// Is there a "pre-system" hook?
//--------------------------------------------------------------------

Hooks::trigger('pre_system');

//--------------------------------------------------------------------
// Get our Request and Response objects
//--------------------------------------------------------------------

$config = new AppConfig();

$request  = is_cli()
		? Services::clirequest($config)
		: Services::request($config);
$request->setProtocolVersion($_SERVER['SERVER_PROTOCOL']);
$response = Services::response();

// Assume success until proven otherwise.
$response->setStatusCode(200);

//--------------------------------------------------------------------
// CSRF Protection
//--------------------------------------------------------------------

if ($config->CSRFProtection === true && ! is_cli())
{
	$security = Services::security($config);

	$security->CSRFVerify($request);
}

//--------------------------------------------------------------------
// Try to Route It
//--------------------------------------------------------------------

require APPPATH.'config/Routes.php';

$router = Services::router($routes, true);

$path = is_cli() ? $request->getPath() : $request->uri->getPath();

$benchmark->stop('bootstrap');
$benchmark->start('routing');

$controller = $router->handle($path);

$benchmark->stop('routing');

//--------------------------------------------------------------------
// Are there any "pre-system" hooks?
//--------------------------------------------------------------------

Hooks::trigger('pre_system');

ob_start();

$benchmark->start('controller');

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

		//--------------------------------------------------------------------
		// Is there a "post_controller_constructor" hook?
		//--------------------------------------------------------------------
		Hooks::trigger('post_controller_constructor');

		$method = $router->methodName();
		$class->$method(...$router->params());
	}
}

$benchmark->stop('controller');

//--------------------------------------------------------------------
// Is there a "post_controller" hook?
//--------------------------------------------------------------------

Hooks::trigger('post_controller');

//--------------------------------------------------------------------
// Output gathering and cleanup
//--------------------------------------------------------------------

$output = ob_get_contents();
ob_end_clean();

$totalTime = $benchmark->stop('total_execution')
					   ->getElapsedTime('total_execution');
$output = str_replace('{elapsed_time}', $totalTime, $output);

//--------------------------------------------------------------------
// Display the Debug Toolbar?
//--------------------------------------------------------------------

if (ENVIRONMENT != 'production' && $config->toolbarEnabled)
{
	$toolbar = Services::toolbar($config);
	$output .= $toolbar->run();
}

$response->setBody($output);

$response->send();

//--------------------------------------------------------------------
// Is there a post-system hook?
//--------------------------------------------------------------------

Hooks::trigger('post_system');
