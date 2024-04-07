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

/**
 * ---------------------------------------------------------------
 * This file cannot be used. The code has moved to Boot.php.
 * ---------------------------------------------------------------
 */

use CodeIgniter\Exceptions\FrameworkException;
use Config\Autoload;
use Config\Modules;
use Config\Paths;
use Config\Services;

header('HTTP/1.1 503 Service Unavailable.', true, 503);

$message = 'This "system/bootstrap.php" is no longer used. If you are seeing this error message,
the upgrade is not complete. Please refer to the upgrade guide and complete the upgrade.
See https://codeigniter4.github.io/userguide/installation/upgrade_450.html' . PHP_EOL;
echo $message;

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

/*
 * ---------------------------------------------------------------
 * SET EXCEPTION AND ERROR HANDLERS
 * ---------------------------------------------------------------
 */

Services::exceptions()->initialize();

/*
 * ---------------------------------------------------------------
 * CHECK SYSTEM FOR MISSING REQUIRED PHP EXTENSIONS
 * ---------------------------------------------------------------
 */

// Run this check for manual installations
if (! is_file(COMPOSER_PATH)) {
    $missingExtensions = [];

    foreach ([
        'intl',
        'json',
        'mbstring',
    ] as $extension) {
        if (! extension_loaded($extension)) {
            $missingExtensions[] = $extension;
        }
    }

    if ($missingExtensions !== []) {
        throw FrameworkException::forMissingExtension(implode(', ', $missingExtensions));
    }

    unset($missingExtensions);
}

/*
 * ---------------------------------------------------------------
 * INITIALIZE KINT
 * ---------------------------------------------------------------
 */

Services::autoloader()->initializeKint(CI_DEBUG);

exit(1);
