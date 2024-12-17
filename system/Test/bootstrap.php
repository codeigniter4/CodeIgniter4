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

use CodeIgniter\Boot;
use Config\Paths;

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
 * throughout the application. We have to set them up here
 * so they are available in the config files that are loaded.
 */

/**
 * A real path with identical slashes in Unix/Windows
 */
function _realpath(string $path): bool|string
{
    $realPath = realpath($path);

    if (! $realPath) {
        return false;
    }

    return str_replace('\\', '/', $realPath);
}

// Often these constants are pre-defined, but query the current directory structure as a fallback
defined('HOMEPATH') || define('HOMEPATH', _realpath(rtrim(getcwd(), '\\/ ')) . '/');
$source = is_dir(HOMEPATH . 'app')
    ? HOMEPATH
    : (is_dir('vendor/codeigniter4/framework/') ? 'vendor/codeigniter4/framework/' : 'vendor/codeigniter4/codeigniter4/');
defined('CONFIGPATH') || define('CONFIGPATH', _realpath($source . 'app/Config') . '/');
defined('PUBLICPATH') || define('PUBLICPATH', _realpath($source . 'public') . '/');
unset($source);

// LOAD OUR PATHS CONFIG FILE
// Load framework paths from their config file
require CONFIGPATH . 'Paths.php';
$paths = new Paths();

// Define necessary framework path constants
defined('APPPATH')    || define('APPPATH', _realpath(rtrim($paths->appDirectory, '\\/ ')) . '/');
defined('ROOTPATH')   || define('ROOTPATH', _realpath(APPPATH . '../') . '/');
defined('SYSTEMPATH') || define('SYSTEMPATH', _realpath(rtrim($paths->systemDirectory, '\\/')) . '/');
defined('WRITEPATH')  || define('WRITEPATH', _realpath(rtrim($paths->writableDirectory, '\\/ ')) . '/');
defined('TESTPATH')   || define('TESTPATH', _realpath(HOMEPATH . 'tests/') . '/');

defined('CIPATH') || define('CIPATH', _realpath(SYSTEMPATH . '../') . '/');
defined('FCPATH') || define('FCPATH', _realpath(PUBLICPATH) . '/');

defined('SUPPORTPATH')   || define('SUPPORTPATH', _realpath(TESTPATH . '_support/') . '/');
defined('COMPOSER_PATH') || define('COMPOSER_PATH', (string) _realpath(HOMEPATH . 'vendor/autoload.php'));
defined('VENDORPATH')    || define('VENDORPATH', _realpath(HOMEPATH . 'vendor') . '/');

/*
 *---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION
 *---------------------------------------------------------------
 * This process sets up the path constants, loads and registers
 * our autoloader, along with Composer's, loads our constants
 * and fires up an environment-specific bootstrapping.
 */

// LOAD THE FRAMEWORK BOOTSTRAP FILE
require $paths->systemDirectory . '/Boot.php';
Boot::bootTest($paths);

/*
 * ---------------------------------------------------------------
 * LOAD ROUTES
 * ---------------------------------------------------------------
 */

service('routes')->loadRoutes();
