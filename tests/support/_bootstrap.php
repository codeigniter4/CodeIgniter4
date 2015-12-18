<?php

//--------------------------------------------------------------------
// CodeIgniter Compatibility Setup
//--------------------------------------------------------------------
// This section gets the environment setup and ready so that your
// tests should have all they need at their fingertips.
//


if (! defined('ENVIRONMENT'))
{
	define('ENVIRONMENT', 'testing');
}

switch (ENVIRONMENT)
{
	case 'testing':
	case 'development':
		error_reporting(-1);
		ini_set('display_errors', 1);
		break;

	case 'production':
		ini_set('display_errors', 0);
		error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
		break;

	default:
		header('HTTP/1.1 503 Service Unavailable.', true, 503);
		echo 'The application environment is not set correctly.';
		exit(1); // EXIT_ERROR
}

$system_path = '../../system';

$application_folder = '../../application';

$writable_directory = '../../writable';


// Set the current directory correctly for CLI requests
if (defined('STDIN'))
{
	chdir(dirname(__FILE__));
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

// Path to the system folder
define('BASEPATH', str_replace('\\', '/', $system_path));

// Path to the front controller (this file)
define('FCPATH', realpath(dirname(__FILE__).'/../../') .'/');

// The name of the INDEX file
define('SELF', pathinfo(FCPATH.'index.php', PATHINFO_BASENAME));

// Path to the writable directory.
define('WRITEPATH', realpath(str_replace('\\', '/', $writable_directory)).'/');

// Name of the "system folder"
define('SYSDIR', trim(strrchr(trim(BASEPATH, '/'), '/'), '/'));

// The path to the "application" folder
if (is_dir($application_folder))
{
	if (($_temp = realpath($application_folder)) !== false)
	{
		$application_folder = $_temp;
	}

	define('APPPATH', $application_folder.DIRECTORY_SEPARATOR);
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

	define('APPPATH', BASEPATH.$application_folder.DIRECTORY_SEPARATOR);
}

//--------------------------------------------------------------------
// Load Autoloaders
//--------------------------------------------------------------------
// CodeIgniter uses 2 autoloaders - a classmap and a PSR4-compatible
// autoloader, to help it load files in your application and the
// framework itself. To make testing easier, we need to get these
// loaded up so the files can be found without us having to require
// a lot of files in the tests.
//
// Below we load a fair chunk of the CodeIgniter.php file to
// get lots of moving pieces up and ready.
//

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

if (file_exists(APPPATH.'config/'.ENVIRONMENT.'/Constants.php'))
{
	require_once APPPATH.'config/'.ENVIRONMENT.'/Constants.php';
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
	require_once BASEPATH.'Config/DotEnv.php';
	$env = new \CodeIgniter\Config\DotEnv(APPPATH);
	$env->load();
	unset($env);
}

/*
 * ------------------------------------------------------
 *  Get the DI Container ready for use
 * ------------------------------------------------------
 */

require_once APPPATH.'config/Services.php';

/*
 * ------------------------------------------------------
 *  Setup the autoloader
 * ------------------------------------------------------
 */

// The autloader isn't initialized yet, so load the file manually.
require_once BASEPATH.'Autoloader/Autoloader.php';
require_once APPPATH.'config/AutoloadConfig.php';

// The Autoloader class only handles namespaces
// and "legacy" support.
$loader = new \CodeIgniter\Autoloader\Autoloader();
$loader->initialize(new App\Config\AutoloadConfig());

// The register function will prepend
// the psr4 loader.
$loader->register();


//--------------------------------------------------------------------
// Load our TestCase
//--------------------------------------------------------------------



