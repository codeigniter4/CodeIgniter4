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

// This is the only time that services array will need
// to be passed into the class. All other uses can
// simply call getInstance().
$di = CodeIgniter\DI\DI::getInstance(get_config('services'));

/*
 * ------------------------------------------------------
 *  Setup the autoloader
 * ------------------------------------------------------
 */

// The autloader isn't initialized yet, so load the file manually.
require_once BASEPATH.'Autoloader/Autoloader.php';

$loader = $di->single('autoloader');
$loader->initialize(get_config('autoload'));

// Assign us to the SPL autoload stack
$loader->register();

//--------------------------------------------------------------------
// TEMPORARY - SAY HI!
//--------------------------------------------------------------------

die('<h1>Hello CodeIgniter</h1>');