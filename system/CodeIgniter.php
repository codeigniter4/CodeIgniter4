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
 *  Setup the autoloader
 * ------------------------------------------------------
 */

require_once BASEPATH.'Autoloader/Autoloader.php';

$loader = new \CodeIgniter\Autoloader\Autoloader( get_config('autoload') );
$loader->register();

//--------------------------------------------------------------------
// TEMPORARY - SAY HI!
//--------------------------------------------------------------------

die('<h1>Hello CodeIgniter</h1>');