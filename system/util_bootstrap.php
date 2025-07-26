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
 *
 * As this bootstrap file is primarily used by internal scripts
 * across the framework and other CodeIgniter projects, we need
 * to make sure it recognizes that we're in development.
 */

$_SERVER['CI_ENVIRONMENT'] = 'development';
define('ENVIRONMENT', 'development');
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

defined('HOMEPATH') || define('HOMEPATH', realpath(rtrim(getcwd(), '\\/ ')) . DIRECTORY_SEPARATOR);

$source = match (true) {
    is_dir(HOMEPATH . 'app/')                   => HOMEPATH,
    is_dir('vendor/codeigniter4/framework/')    => 'vendor/codeigniter4/framework/',
    is_dir('vendor/codeigniter4/codeigniter4/') => 'vendor/codeigniter4/codeigniter4/',
    default                                     => throw new RuntimeException('Unable to determine the source directory.'),
};

defined('CONFIGPATH') || define('CONFIGPATH', realpath($source . 'app/Config') . DIRECTORY_SEPARATOR);
defined('PUBLICPATH') || define('PUBLICPATH', realpath($source . 'public') . DIRECTORY_SEPARATOR);
unset($source);

require CONFIGPATH . 'Paths.php';
$paths = new Paths();

defined('CIPATH') || define('CIPATH', realpath($paths->systemDirectory . '/../') . DIRECTORY_SEPARATOR);
defined('FCPATH') || define('FCPATH', PUBLICPATH);

if (is_dir($paths->testsDirectory . '/_support/') && ! defined('SUPPORTPATH')) {
    define('SUPPORTPATH', realpath($paths->testsDirectory . '/_support/') . DIRECTORY_SEPARATOR);
}

if (is_dir(HOMEPATH . 'vendor/')) {
    define('VENDORPATH', realpath(HOMEPATH . 'vendor/') . DIRECTORY_SEPARATOR);
    define('COMPOSER_PATH', (string) realpath(HOMEPATH . 'vendor/autoload.php'));
}

/*
 *---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION
 *---------------------------------------------------------------
 *
 * This process sets up the path constants, loads and registers
 * our autoloader, along with Composer's, loads our constants
 * and fires up an environment-specific bootstrapping.
 */

require $paths->systemDirectory . '/Boot.php';
Boot::bootConsole($paths);
