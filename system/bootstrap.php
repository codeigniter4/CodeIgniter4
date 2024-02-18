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

use Config\Autoload;
use Config\Modules;
use Config\Paths;
use Config\Services;

/*
 * ---------------------------------------------------------------
 * SETUP OUR PATH CONSTANTS
 * ---------------------------------------------------------------
 *
 * The path constants provide convenient access to the folders
 * throughout the application. We have to setup them up here
 * so they are available in the config files that are loaded.
 */

/** @var Paths $paths */

// The path to the application directory.
if (! defined('APPPATH')) {
    define('APPPATH', realpath(rtrim($paths->appDirectory, '\\/ ')) . DIRECTORY_SEPARATOR);
}

// The path to the project root directory. Just above APPPATH.
if (! defined('ROOTPATH')) {
    define('ROOTPATH', realpath(APPPATH . '../') . DIRECTORY_SEPARATOR);
}

// The path to the system directory.
if (! defined('SYSTEMPATH')) {
    define('SYSTEMPATH', realpath(rtrim($paths->systemDirectory, '\\/ ')) . DIRECTORY_SEPARATOR);
}

// The path to the writable directory.
if (! defined('WRITEPATH')) {
    define('WRITEPATH', realpath(rtrim($paths->writableDirectory, '\\/ ')) . DIRECTORY_SEPARATOR);
}

// The path to the tests directory
if (! defined('TESTPATH')) {
    define('TESTPATH', realpath(rtrim($paths->testsDirectory, '\\/ ')) . DIRECTORY_SEPARATOR);
}

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
} else {
    // @codeCoverageIgnoreStart
    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo 'The application environment is not set correctly.';

    exit(EXIT_ERROR); // EXIT_ERROR
    // @codeCoverageIgnoreEnd
}

/*
 * ---------------------------------------------------------------
 * GRAB OUR CONSTANTS
 * ---------------------------------------------------------------
 */

if (! defined('APP_NAMESPACE')) {
    require_once APPPATH . 'Config/Constants.php';
}

/*
 * ---------------------------------------------------------------
 * LOAD COMMON FUNCTIONS
 * ---------------------------------------------------------------
 */

// Require app/Common.php file if exists.
if (is_file(APPPATH . 'Common.php')) {
    require_once APPPATH . 'Common.php';
}

// Require system/Common.php
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

if (! class_exists(Autoload::class, false)) {
    require_once SYSTEMPATH . 'Config/AutoloadConfig.php';
    require_once APPPATH . 'Config/Autoload.php';
    require_once SYSTEMPATH . 'Modules/Modules.php';
    require_once APPPATH . 'Config/Modules.php';
}

require_once SYSTEMPATH . 'Autoloader/Autoloader.php';
require_once SYSTEMPATH . 'Config/BaseService.php';
require_once SYSTEMPATH . 'Config/Services.php';
require_once APPPATH . 'Config/Services.php';

// Initialize and register the loader with the SPL autoloader stack.
Services::autoloader()->initialize(new Autoload(), new Modules())->register();
Services::autoloader()->loadHelpers();
Services::autoloader()->initializeKint(CI_DEBUG);
