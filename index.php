<?php

// Used by the debug toolbar. Do not remove.
$startMemory = memory_get_usage();
$startTime   = microtime(true);

/*
 *---------------------------------------------------------------
 * APPLICATION ENVIRONMENT
 *---------------------------------------------------------------
 *
 * You can load different configurations depending on your
 * current environment. Setting the environment also influences
 * things like logging and error reporting.
 *
 * This can be set to anything, but default usage is:
 *
 *     development
 *     testing
 *     production
 *
 * NOTE: If you change these, also change the error_reporting() code below
 */
define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');

/*
 *---------------------------------------------------------------
 * ERROR REPORTING
 *---------------------------------------------------------------
 *
 * Different environments will require different levels of error reporting.
 * By default development will show errors but testing and live will hide them.
 */
switch (ENVIRONMENT)
{
	case 'development':
	case 'testing':
		error_reporting(-1);
		ini_set('display_errors', 1);
		define('CI_DEBUG', 1);
		break;

	case 'production':
		ini_set('display_errors', 0);
		error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
		define('CI_DEBUG', 0);
		break;

	default:
		header('HTTP/1.1 503 Service Unavailable.', true, 503);
		echo 'The application environment is not set correctly.';
		exit(1); // EXIT_ERROR
}

/*
 *---------------------------------------------------------------
 * SYSTEM FOLDER NAME
 *---------------------------------------------------------------
 *
 * This variable must contain the name of your "system" folder.
 * Include the path if the folder is not in the same directory
 * as this file.
 */
$system_path = 'system';

/*
 *---------------------------------------------------------------
 * APPLICATION FOLDER NAME
 *---------------------------------------------------------------
 *
 * If you want this front controller to use a different "application"
 * folder than the default one you can set its name here. The folder
 * can also be renamed or relocated anywhere on your getServer. If
 * you do, use a full getServer path. For more info please see the user guide:
 * http://codeigniter.com/user_guide/general/managing_apps.html
 *
 * NO TRAILING SLASH!
 */
$application_folder = 'application';

/*
 * ---------------------------------------------------------------
 * WRITABLE DIRECTORY NAME
 * ---------------------------------------------------------------
 *
 * This variable must contain the name of your "writable" directory.
 * The writable directory allows you to group all directories that
 * need write permission to a single place that can be tucked away
 * for maximum security, keeping it out of the application and/or
 * system directories.
 */
$writable_directory = 'writable';

// --------------------------------------------------------------------
// END OF USER CONFIGURABLE SETTINGS.  DO NOT EDIT BELOW THIS LINE
// --------------------------------------------------------------------

/*
 * ---------------------------------------------------------------
 *  Resolve the system path for increased reliability
 * ---------------------------------------------------------------
 */

// Set the current directory correctly for CLI requests
if (defined('STDIN'))
{
	chdir(__DIR__);
}

if (($_temp = realpath($system_path)) !== false)
{
	$system_path = $_temp.'/';
}
else
{
	// Ensure there's a trailing slash
	$system_path = rtrim($system_path, '/').'/';
}

// Is the system path correct?
if ( ! is_dir($system_path))
{
	header('HTTP/1.1 503 Service Unavailable.', true, 503);
	echo 'Your system folder path does not appear to be set correctly. Please open the following file and correct this: '.
	     pathinfo(__FILE__, PATHINFO_BASENAME);
	exit(3); // EXIT_CONFIG
}

/*
 * -------------------------------------------------------------------
 *  Now that we know the path, set the main path constants
 * -------------------------------------------------------------------
 * Always use 'realpath' on the paths to help ensure that we can
 * take advantage of PHP's realpath cache for slight performance boost.
 */
// The name of THIS file
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

// Path to the system folder
define('BASEPATH', realpath(str_replace('\\', '/', $system_path)).DIRECTORY_SEPARATOR);

// Path to the front controller (this file)
define('FCPATH', realpath(__DIR__).DIRECTORY_SEPARATOR);

// Path to the writable directory.
define('WRITEPATH', realpath(str_replace('\\', '/', $writable_directory)).DIRECTORY_SEPARATOR);

// The path to the "application" folder
if (is_dir($application_folder))
{
	if (($_temp = realpath($application_folder)) !== false)
	{
		$application_folder = $_temp;
	}

	define('APPPATH', realpath($application_folder).DIRECTORY_SEPARATOR);
}
else
{
	if ( ! is_dir(BASEPATH.$application_folder.DIRECTORY_SEPARATOR))
	{
		header('HTTP/1.1 503 Service Unavailable.', true, 503);
		echo 'Your application folder path does not appear to be set correctly. Please open the following file and correct this: '.
		     SELF;
		exit(3); // EXIT_CONFIG
	}

	define('APPPATH', realpath(BASEPATH.$application_folder).DIRECTORY_SEPARATOR);
}

/*
 * --------------------------------------------------------------------
 * LOAD THE BOOTSTRAP FILE
 * --------------------------------------------------------------------
 *
 * And away we go...
 */
require BASEPATH.'CodeIgniter.php';
$codeigniter = new CodeIgniter\CodeIgniter($startMemory);
$codeigniter->run();
