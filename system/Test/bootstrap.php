<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use CodeIgniter\Config\DotEnv;
use Config\Autoload;
use Config\Modules;
use Config\Paths;
use Config\Services;

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

/*
 * ---------------------------------------------------------------
 * DEFINE ENVIRONMENT
 * ---------------------------------------------------------------
 */

// Make sure it recognizes that we're testing.
$_SERVER['CI_ENVIRONMENT'] = 'testing';
define('ENVIRONMENT', 'testing');

defined('CI_DEBUG') || define('CI_DEBUG', true);

/*
 * ---------------------------------------------------------------
 * SET UP OUR PATH CONSTANTS
 * ---------------------------------------------------------------
 *
 * The path constants provide convenient access to the folders
 * throughout the application. We have to setup them up here
 * so they are available in the config files that are loaded.
 */

// Often these constants are pre-defined, but query the current directory structure as a fallback
defined('HOMEPATH') || define('HOMEPATH', realpath(rtrim(getcwd(), '\\/ ')) . DIRECTORY_SEPARATOR);
$source = is_dir(HOMEPATH . 'app')
    ? HOMEPATH
    : (is_dir('vendor/codeigniter4/framework/') ? 'vendor/codeigniter4/framework/' : 'vendor/codeigniter4/codeigniter4/');
defined('CONFIGPATH') || define('CONFIGPATH', realpath($source . 'app/Config') . DIRECTORY_SEPARATOR);
defined('PUBLICPATH') || define('PUBLICPATH', realpath($source . 'public') . DIRECTORY_SEPARATOR);
unset($source);

// LOAD OUR PATHS CONFIG FILE
// Load framework paths from their config file
require CONFIGPATH . 'Paths.php';
$paths = new Paths();

// Define necessary framework path constants
defined('APPPATH')    || define('APPPATH', realpath(rtrim($paths->appDirectory, '\\/ ')) . DIRECTORY_SEPARATOR);
defined('ROOTPATH')   || define('ROOTPATH', realpath(APPPATH . '../') . DIRECTORY_SEPARATOR);
defined('SYSTEMPATH') || define('SYSTEMPATH', realpath(rtrim($paths->systemDirectory, '\\/')) . DIRECTORY_SEPARATOR);
defined('WRITEPATH')  || define('WRITEPATH', realpath(rtrim($paths->writableDirectory, '\\/ ')) . DIRECTORY_SEPARATOR);
defined('TESTPATH')   || define('TESTPATH', realpath(HOMEPATH . 'tests/') . DIRECTORY_SEPARATOR);

defined('CIPATH') || define('CIPATH', realpath(SYSTEMPATH . '../') . DIRECTORY_SEPARATOR);
defined('FCPATH') || define('FCPATH', realpath(PUBLICPATH) . DIRECTORY_SEPARATOR);

defined('SUPPORTPATH')   || define('SUPPORTPATH', realpath(TESTPATH . '_support/') . DIRECTORY_SEPARATOR);
defined('COMPOSER_PATH') || define('COMPOSER_PATH', (string) realpath(HOMEPATH . 'vendor/autoload.php'));
defined('VENDORPATH')    || define('VENDORPATH', realpath(HOMEPATH . 'vendor') . DIRECTORY_SEPARATOR);

/*
 *---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION
 *---------------------------------------------------------------
 * This process sets up the path constants, loads and registers
 * our autoloader, along with Composer's, loads our constants
 * and fires up an environment-specific bootstrapping.
 */

// LOAD DOTENV FILE
// Load environment settings from .env files into $_SERVER and $_ENV
require_once $paths->systemDirectory . '/Config/DotEnv.php';
(new DotEnv($paths->appDirectory . '/../'))->load();

/*
 * ---------------------------------------------------------------
 * LOAD ENVIRONMENT BOOTSTRAP
 * ---------------------------------------------------------------
 *
 * Load any custom boot files based upon the current environment.
 * If no boot file exists, we shouldn't continue because something
 * is wrong. At the very least, they should have error reporting setup.
 */

if (is_file(APPPATH . 'Config/Boot/' . ENVIRONMENT . '.php')) {
    require_once APPPATH . 'Config/Boot/' . ENVIRONMENT . '.php';
}

/*
 * ---------------------------------------------------------------
 * GRAB OUR CONSTANTS
 * ---------------------------------------------------------------
 */

require_once APPPATH . 'Config/Constants.php';

/*
 * ---------------------------------------------------------------
 * LOAD COMMON FUNCTIONS
 * ---------------------------------------------------------------
 */

// Load Common.php from App then System
if (is_file(APPPATH . 'Common.php')) {
    require_once APPPATH . 'Common.php';
}

require_once SYSTEMPATH . 'Common.php';

/*
 * ---------------------------------------------------------------
 * LOAD OUR AUTOLOADER
 * ---------------------------------------------------------------
 *
 * The autoloader allows all of the pieces to work together in the
 * framework. We have to load it here, though, so that the config
 * files can use the path constants.
 */

require_once SYSTEMPATH . 'Config/AutoloadConfig.php';
require_once APPPATH . 'Config/Autoload.php';
require_once SYSTEMPATH . 'Modules/Modules.php';
require_once APPPATH . 'Config/Modules.php';

require_once SYSTEMPATH . 'Autoloader/Autoloader.php';
require_once SYSTEMPATH . 'Config/BaseService.php';
require_once SYSTEMPATH . 'Config/Services.php';
require_once APPPATH . 'Config/Services.php';

// Initialize and register the loader with the SPL autoloader stack.
Services::autoloader()->initialize(new Autoload(), new Modules())->register();
Services::autoloader()->loadHelpers();

/*
 * ---------------------------------------------------------------
 * SET EXCEPTION AND ERROR HANDLERS
 * ---------------------------------------------------------------
 */

Services::exceptions()->initialize();

/*
 * ---------------------------------------------------------------
 * INITIALIZE KINT
 * ---------------------------------------------------------------
 */

Services::autoloader()->initializeKint(CI_DEBUG);

/*
 * ---------------------------------------------------------------
 * LOAD ROUTES
 * ---------------------------------------------------------------
 */

Services::routes()->loadRoutes();
