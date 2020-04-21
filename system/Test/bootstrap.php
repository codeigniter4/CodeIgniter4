<?php
ini_set('error_reporting', E_ALL);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Make sure it recognizes that we're testing.
$_SERVER['CI_ENVIRONMENT'] = 'testing';
define('ENVIRONMENT', 'testing');

// Load framework paths from their config file
require CONFIGPATH . 'Paths.php';
$paths = new Config\Paths();

// Define necessary framework path constants
defined('APPPATH')       || define('APPPATH', realpath($paths->appDirectory) . DIRECTORY_SEPARATOR);
defined('WRITEPATH')     || define('WRITEPATH', realpath($paths->writableDirectory) . DIRECTORY_SEPARATOR);
defined('SYSTEMPATH')    || define('SYSTEMPATH', realpath($paths->systemDirectory) . DIRECTORY_SEPARATOR);
defined('ROOTPATH')      || define('ROOTPATH', realpath(APPPATH . '../') . DIRECTORY_SEPARATOR);
defined('CIPATH')        || define('CIPATH', realpath(SYSTEMPATH . '../') . DIRECTORY_SEPARATOR);
defined('FCPATH')        || define('FCPATH', realpath(PUBLICPATH) . DIRECTORY_SEPARATOR);
defined('TESTPATH')      || define('TESTPATH', realpath(HOMEPATH . 'tests/') . DIRECTORY_SEPARATOR);
defined('SUPPORTPATH')   || define('SUPPORTPATH', realpath(TESTPATH . '_support/') . DIRECTORY_SEPARATOR);
defined('COMPOSER_PATH') || define('COMPOSER_PATH', realpath(HOMEPATH . 'vendor/autoload.php'));
defined('VENDORPATH')    || define('VENDORPATH', realpath(HOMEPATH . 'vendor') . DIRECTORY_SEPARATOR);

// Load Common.php from App then System
if (file_exists(APPPATH . 'Common.php'))
{
	require_once APPPATH . 'Common.php';
}

require_once SYSTEMPATH . 'Common.php';

// Set environment values that would otherwise stop the framework from functioning during tests.
if (! isset($_SERVER['app.baseURL']))
{
	$_SERVER['app.baseURL'] = 'http://example.com';
}

// Load necessary components
require_once APPPATH . 'Config/Autoload.php';
require_once APPPATH . 'Config/Constants.php';
require_once APPPATH . 'Config/Modules.php';

require_once SYSTEMPATH . 'Autoloader/Autoloader.php';
require_once SYSTEMPATH . 'Config/BaseService.php';
require_once APPPATH . 'Config/Services.php';

// Use Config\Services as CodeIgniter\Services
if (! class_exists('CodeIgniter\Services', false))
{
	class_alias('Config\Services', 'CodeIgniter\Services');
}

// Launch the autoloader to gather namespaces (includes composer.json's "autoload-dev")
$loader = \CodeIgniter\Services::autoloader();
$loader->initialize(new Config\Autoload(), new Config\Modules());

// Register the loader with the SPL autoloader stack.
$loader->register();

require_once APPPATH . 'Config/Routes.php';
$routes->getRoutes('*');
