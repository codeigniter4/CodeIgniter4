<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019 CodeIgniter Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

/*
 * ---------------------------------------------------------------
 * SETUP OUR PATH CONSTANTS
 * ---------------------------------------------------------------
 *
 * The path constants provide convenient access to the folders
 * throughout the application. We have to setup them up here
 * so they are available in the config files that are loaded.
 */

/**
 * The path to the application directory.
 */
if (! defined('APPPATH'))
{
	define('APPPATH', realpath($paths->appDirectory) . DIRECTORY_SEPARATOR);
}

/**
 * The path to the project root directory. Just above APPPATH.
 */
if (! defined('ROOTPATH'))
{
	define('ROOTPATH', realpath(APPPATH . '../') . DIRECTORY_SEPARATOR);
}

/**
 * The path to the system directory.
 */
if (! defined('SYSTEMPATH'))
{
	define('SYSTEMPATH', realpath($paths->systemDirectory) . DIRECTORY_SEPARATOR);
}

/**
 * The path to the writable directory.
 */
if (! defined('WRITEPATH'))
{
	define('WRITEPATH', realpath($paths->writableDirectory) . DIRECTORY_SEPARATOR);
}

/**
 * The path to the tests directory
 */
if (! defined('TESTPATH'))
{
	define('TESTPATH', realpath($paths->testsDirectory) . DIRECTORY_SEPARATOR);
}

/*
 * ---------------------------------------------------------------
 * GRAB OUR CONSTANTS & COMMON
 * ---------------------------------------------------------------
 */
if (! defined('APP_NAMESPACE'))
{
	require_once APPPATH . 'Config/Constants.php';
}

// Let's see if an app/Common.php file exists
if (file_exists(APPPATH . 'Common.php'))
{
	require_once APPPATH . 'Common.php';
}

// Require system/Common.php
require_once SYSTEMPATH . 'Common.php';

/*
 * ---------------------------------------------------------------
 * LOAD OUR AUTOLOADER
 * ---------------------------------------------------------------
 *
 * The autoloader allows all of the pieces to work together
 * in the framework. We have to load it here, though, so
 * that the config files can use the path constants.
 */

if (! class_exists(Config\Autoload::class, false))
{
	require_once APPPATH . 'Config/Autoload.php';
	require_once APPPATH . 'Config/Modules.php';
}

require_once SYSTEMPATH . 'Autoloader/Autoloader.php';
require_once SYSTEMPATH . 'Config/BaseService.php';
require_once APPPATH . 'Config/Services.php';

// Use Config\Services as CodeIgniter\Services
if (! class_exists('CodeIgniter\Services', false))
{
	class_alias('Config\Services', 'CodeIgniter\Services');
}

$loader = CodeIgniter\Services::autoloader();
$loader->initialize(new Config\Autoload(), new Config\Modules());
$loader->register();    // Register the loader with the SPL autoloader stack.

// Now load Composer's if it's available
if (is_file(COMPOSER_PATH))
{
	require_once COMPOSER_PATH;
}

// Load environment settings from .env files
// into $_SERVER and $_ENV
require_once SYSTEMPATH . 'Config/DotEnv.php';

$env = new \CodeIgniter\Config\DotEnv(ROOTPATH);
$env->load();

// Always load the URL helper -
// it should be used in 90% of apps.
helper('url');

/*
 * ---------------------------------------------------------------
 * GRAB OUR CODEIGNITER INSTANCE
 * ---------------------------------------------------------------
 *
 * The CodeIgniter class contains the core functionality to make
 * the application run, and does all of the dirty work to get
 * the pieces all working together.
 */

$appConfig = config(\Config\App::class);
$app       = new \CodeIgniter\CodeIgniter($appConfig);
$app->initialize();

return $app;
