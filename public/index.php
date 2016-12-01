<?php

// Set default timezone on the server
// Defaults to UTC to make time conversions work well
date_default_timezone_set('UTC');

// Used by the debug toolbar. Do not remove.
$startMemory = memory_get_usage();
$startTime   = microtime(true);

$useKint = false;

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

// running under Continuous Integration server?
if (getenv('CI') !== false)
{
	define('ENVIRONMENT', 'testing');
}
else
{
	define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'production');
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
$system_directory = '../system';

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
$application_directory = '../application';

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
$writable_directory = '../writable';

/*
 * ---------------------------------------------------------------
 * TESTS DIRECTORY NAME
 * ---------------------------------------------------------------
 *
 * This variable must contain the name of your "tests" directory.
 * The writable directory allows you to group all directories that
 * need write permission to a single place that can be tucked away
 * for maximum security, keeping it out of the application and/or
 * system directories.
 */
$tests_directory = '../tests';

// --------------------------------------------------------------------
// END OF USER CONFIGURABLE SETTINGS.  DO NOT EDIT BELOW THIS LINE
// --------------------------------------------------------------------

/*
 * ---------------------------------------------------------------
 *  Resolve the system path for increased reliability
 * ---------------------------------------------------------------
 */

// Ensure the current directory is pointing to the front controller's directory
chdir(__DIR__);

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

/*
 * -------------------------------------------------------------------
 *  Now that we know the path, set the main path constants
 * -------------------------------------------------------------------
 */
// The name of THIS file
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

// Path to the system folder
define('BASEPATH', realpath($system_directory).DIRECTORY_SEPARATOR);

// Path to the front controller (this file)
define('FCPATH', __DIR__.DIRECTORY_SEPARATOR);

// Path to code root folder (just up from public)
define('ROOTPATH', str_replace('public'.DIRECTORY_SEPARATOR, '', FCPATH));

// Path to the writable directory.
define('WRITEPATH', realpath($writable_directory).DIRECTORY_SEPARATOR);

// The path to the "application" folder
define('APPPATH', realpath($application_directory).DIRECTORY_SEPARATOR);

// The path to the "tests" directory
define('TESTPATH', realpath($tests_directory).DIRECTORY_SEPARATOR);

/*
 *---------------------------------------------------------------
 * BOOT THE ENVIRONMENT
 *---------------------------------------------------------------
 *
 * The boot files allow you completely customize the working
 * conditions in this environment, including turning error
 * reporting on or off, loading up extra debugging tools,
 * and more.
 *
 * A file matching the name of the current environment must
 * be found under application/Config/Boot or the system
 * will stop execution.
 */
if (file_exists(APPPATH.'Config/Boot/'.ENVIRONMENT.'.php'))
{
	require APPPATH.'Config/Boot/'.ENVIRONMENT.'.php';
}
else
{
	header('HTTP/1.1 503 Service Unavailable.', true, 503);
	echo 'The application environment is not set correctly.';
	exit(1); // EXIT_ERROR
}

/*
 * ------------------------------------------------------
 * Load the Kint Debugger
 * ------------------------------------------------------
 */
if ($useKint === true)
{
	require_once BASEPATH.'ThirdParty/Kint/Kint.class.php';
}

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

// Use Config\Services as CodeIgniter\Services
class_alias('Config\Services', 'CodeIgniter\Services');

// The Autoloader class only handles namespaces
// and "legacy" support.
$loader = CodeIgniter\Services::autoloader();
$loader->initialize(new Config\Autoload());

// The register function will prepend
// the psr4 loader.
$loader->register();

/*
 * ------------------------------------------------------
 *  Load the global functions
 * ------------------------------------------------------
 */

require_once BASEPATH.'Common.php';

/*
 * ------------------------------------------------------
 *  Set custom exception handling
 * ------------------------------------------------------
 */
$config = new \Config\App();

CodeIgniter\Services::exceptions($config, true)
	->initialize();

//--------------------------------------------------------------------
// Should we use a Composer autoloader?
//--------------------------------------------------------------------

if ($composer_autoload = $config->composerAutoload)
{
	if ($composer_autoload === TRUE)
	{
		file_exists(APPPATH.'../vendor/autoload.php')
			? require_once(APPPATH.'../vendor/autoload.php')
			: log_message('error', '$config->\'composerAutoload\' is set to TRUE but '.realpath("../").'vendor/autoload.php was not found.');
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

/*
 * --------------------------------------------------------------------
 * LOAD THE BOOTSTRAP FILE
 * --------------------------------------------------------------------
 *
 * And away we go...
 */
$codeigniter = new CodeIgniter\CodeIgniter($startMemory, $startTime, $config);
$codeigniter->run();
