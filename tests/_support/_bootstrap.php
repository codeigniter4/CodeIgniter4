<?php
// Make sure it recognizes that we're testing.
$_SERVER['CI_ENVIRONMENT'] = 'testing';

// Get our system paths
require 'application/Config/Paths.php';
$paths  = new \Config\Paths();

// Path to the front controller (this file)
define('FCPATH', getcwd().'/public'.DIRECTORY_SEPARATOR);

/*
 * ---------------------------------------------------------------
 * SETUP OUR PATH CONSTANTS
 * ---------------------------------------------------------------
 *
 * The path constants provide convenient access to the folders
 * throughout the application. We have to setup them up here
 * so they are available in the config files that are loaded.
 */

// Path to code root folder (just up from public)
$pos = strrpos(FCPATH, 'public'.DIRECTORY_SEPARATOR);
define('ROOTPATH', substr_replace(FCPATH, '', $pos, strlen('public'.DIRECTORY_SEPARATOR)));

// The path to the "application" folder
define('APPPATH', realpath(FCPATH.$paths->applicationDirectory).DIRECTORY_SEPARATOR);

// Path to the system folder
define('BASEPATH', realpath(FCPATH.$paths->systemDirectory).DIRECTORY_SEPARATOR);

// Path to the writable directory.
define('WRITEPATH', realpath(FCPATH.$paths->writableDirectory).DIRECTORY_SEPARATOR);

// The path to the "tests" directory
define('TESTPATH', realpath(FCPATH.$paths->testsDirectory).DIRECTORY_SEPARATOR);

define('SUPPORTPATH', realpath(TESTPATH.'_support/').'/');

// Use special Services for testing. These allow
// insert mocks in place of normal services.
require SUPPORTPATH.'Services.php';

/*
 * ---------------------------------------------------------------
 * GRAB OUR CONSTANTS & COMMON
 * ---------------------------------------------------------------
 */
require APPPATH.'Config/Constants.php';

// Use special global functions for testing.
require_once SUPPORTPATH.'MockCommon.php';
require BASEPATH.'Common.php';

/*
 * ---------------------------------------------------------------
 * LOAD OUR AUTOLOADER
 * ---------------------------------------------------------------
 *
 * The autoloader allows all of the pieces to work together
 * in the framework. We have to load it here, though, so
 * that the config files can use the path constants.
 */

require BASEPATH.'Autoloader/Autoloader.php';
require APPPATH .'Config/Autoload.php';
require APPPATH .'Config/Services.php';

$loader = CodeIgniter\Services::autoloader();
$loader->initialize(new Config\Autoload());
$loader->register();    // Register the loader with the SPL autoloader stack.

// Add namespace paths to autoload mocks for testing.
$loader->addNamespace('CodeIgniter',   SUPPORTPATH);
$loader->addNamespace('Config',        SUPPORTPATH.'Config');
$loader->addNamespace('Tests\Support', SUPPORTPATH);

// Now load Composer's if it's available
if (file_exists(COMPOSER_PATH))
{
    require COMPOSER_PATH;
}

/*
 * ---------------------------------------------------------------
 * GRAB OUR CODEIGNITER INSTANCE
 * ---------------------------------------------------------------
 *
 * The CodeIgniter class contains the core functionality to make
 * the application run, and does all of the dirty work to get
 * the pieces all working together.
 */

$app = new \CodeIgniter\CodeIgniter(new \Config\App());
$app->initialize();

//--------------------------------------------------------------------
// Load our TestCase
//--------------------------------------------------------------------

require  TESTPATH.'_support/CIUnitTestCase.php';
