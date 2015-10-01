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

//require_once(APPPATH.'config/constants.php');

/*
 * ------------------------------------------------------
 *  Load the global functions
 * ------------------------------------------------------
 */

require_once BASEPATH.'Common.php';

/*
 * ------------------------------------------------------
 *  Get the DI Container ready for use
 * ------------------------------------------------------
 */

require_once BASEPATH.'DI/DI.php';
require_once APPPATH.'config/services.php';

// This is the only time that services array will need
// to be passed into the class. All other uses can
// simply call getInstance().
$di = CodeIgniter\DI\DI::getInstance(new ServicesConfig());

/*
 * ------------------------------------------------------
 *  Setup the autoloader
 * ------------------------------------------------------
 */

// The autloader isn't initialized yet, so load the file manually.
require_once BASEPATH.'Autoloader/Autoloader.php';
require_once APPPATH.'config/autoload.php';

// The Autoloader class only handles namespaces
// and "legacy" support.
$loader = $di->single('autoloader');
$loader->initialize(new AutoloadConfig());

// The register function will prepend
// the psr4 loader.
$loader->register();

//--------------------------------------------------------------------
// Start the Benchmark
//--------------------------------------------------------------------

$benchmark = new CodeIgniter\Benchmark\Timer();
$benchmark->start('total_execution');

//--------------------------------------------------------------------
// Try to Route It
//--------------------------------------------------------------------

require APPPATH.'config/routes.php';

$router = $di->single('router');

$controller = $router->controllerName();

// Is it routed to a Closure?
if (is_callable($controller))
{
	$controller(...$router->params());
}
else
{
	$class  = $di->make($controller);
	$method = $router->methodName();
	$class->$method(...$router->params());
}

echo $benchmark->elapsedTime('total_execution');
