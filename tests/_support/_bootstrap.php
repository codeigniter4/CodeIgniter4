<?php

//--------------------------------------------------------------------
// CodeIgniter Compatibility Setup
//--------------------------------------------------------------------
// This section gets the environment setup and ready so that your
// tests should have all they need at their fingertips.
//
$startMemory = memory_get_usage();
$startTime   = microtime(true);

if (! defined('ENVIRONMENT'))
{
	define('ENVIRONMENT', 'testing');
}

switch (ENVIRONMENT)
{
	case 'testing':
		error_reporting(-1);
		ini_set('display_errors', 1);
		break;
}

define('CI_DEBUG', 1);
define('SHOW_DEBUG_BACKTRACE', TRUE);

$system_directory = 'system';

$application_directory = 'application';

$writable_directory = 'writable';

$tests_directory = 'tests';

// Ensure the current directory is pointing to the front controller's directory
//chdir(__DIR__);

// Are the system and application paths correct?
if ( ! realpath($system_directory) OR ! is_dir($system_directory))
{
	header('HTTP/1.1 503 Service Unavailable.', true, 503);
	echo 'Your system folder path does not appear to be set correctly. Please open the following file and correct this: '.
		pathinfo(__FILE__, PATHINFO_BASENAME);
	exit(3); // EXIT_CONFIG
}

if ( ! realpath($application_directory) OR ! is_dir($application_directory))
{
	header('HTTP/1.1 503 Service Unavailable.', true, 503);
	echo 'Your application folder path does not appear to be set correctly. Please open the following file and correct this: '.
		pathinfo(__FILE__, PATHINFO_BASENAME);
	exit(3); // EXIT_CONFIG
}

// The name of THIS file
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

// Path to the system folder
define('BASEPATH', realpath($system_directory).DIRECTORY_SEPARATOR);

// Path to the front controller (this file)
define('FCPATH', __DIR__.DIRECTORY_SEPARATOR);

// Path to the writable directory.
define('WRITEPATH', realpath($writable_directory).DIRECTORY_SEPARATOR);

// The path to the "application" folder
define('APPPATH', realpath($application_directory).DIRECTORY_SEPARATOR);

// The path to the "tests" directory
define('TESTPATH', realpath($tests_directory).DIRECTORY_SEPARATOR);

define('SUPPORTPATH', realpath(TESTPATH.'_support/').'/');

/*
 * ------------------------------------------------------
 *  Load any environment-specific settings from .env file
 * ------------------------------------------------------
 */

// Load environment settings from .env files
// into $_SERVER and $_ENV
require BASEPATH.'Config/DotEnv.php';
$env = new CodeIgniter\Config\DotEnv(APPPATH);
$env->load();
unset($env);

/*
 * ------------------------------------------------------
 *  Load the framework constants
 * ------------------------------------------------------
 */
if (file_exists(APPPATH.'Config/'.ENVIRONMENT.'/Constants.php'))
{
	require_once APPPATH.'Config/'.ENVIRONMENT.'/Constants.php';
}

require_once(APPPATH.'Config/Constants.php');

/*
 * ------------------------------------------------------
 *  Setup the autoloader
 * ------------------------------------------------------
 */
// The autoloader isn't initialized yet, so load the file manually.
require BASEPATH.'Autoloader/Autoloader.php';
require APPPATH.'Config/Autoload.php';
require APPPATH.'Config/Services.php';
// Use special Services for testing.
require SUPPORTPATH.'Services.php';

// The Autoloader class only handles namespaces
// and "legacy" support.
$loader = CodeIgniter\Services::autoloader();
$loader->initialize(new Config\Autoload());

// The register function will prepend
// the psr4 loader.
$loader->register();

// Add namespace paths to autoload mocks for testing.
$loader->addNamespace('CodeIgniter', SUPPORTPATH);
$loader->addNamespace('Config', SUPPORTPATH.'Config');

/*
 * ------------------------------------------------------
 *  Load the global functions
 * ------------------------------------------------------
 */

// Use special global functions for testing.
require_once SUPPORTPATH.'MockCommon.php';
require_once BASEPATH.'Common.php';

/*
 * ------------------------------------------------------
 *  Set custom exception handling
 * ------------------------------------------------------
 */
$config = new \Config\App();

Config\Services::exceptions($config, true)
	->initialize();

//--------------------------------------------------------------------
// Should we use a Composer autoloader?
//--------------------------------------------------------------------

if ($composer_autoload = $config->composerAutoload)
{
	if ($composer_autoload === TRUE)
	{
		file_exists(APPPATH.'vendor/autoload.php')
			? require_once(APPPATH.'vendor/autoload.php')
			: log_message('error', '$config->\'composerAutoload\' is set to TRUE but '.APPPATH.'vendor/autoload.php was not found.');
	}
	elseif (file_exists($composer_autoload))
	{
		require_once($composer_autoload);
	}
	else
	{
		log_message('error', 'Could not find the specified $config->\'composerAutoload\' path: '.$composer_autoload);
	}
}

//--------------------------------------------------------------------
// Load our TestCase
//--------------------------------------------------------------------

require_once __DIR__ .'/CIUnitTestCase.php';
