<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2018 British Columbia Institute of Technology
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
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
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

$public = trim($paths->publicDirectory, '/');

$pos = strrpos(FCPATH, $public.DIRECTORY_SEPARATOR);

/**
 * The path to the main application directory. Just above public.
 */
if (! defined('ROOTPATH'))
{
	define('ROOTPATH', substr_replace(FCPATH, '', $pos, strlen($public.DIRECTORY_SEPARATOR)));
}

/**
 * The path to the application directory.
 */
if (! defined('APPPATH'))
{
	define('APPPATH', realpath(ROOTPATH . $paths->applicationDirectory).DIRECTORY_SEPARATOR);
}

/**
 * The path to the system directory.
 */
if (! defined('BASEPATH'))
{
	define('BASEPATH', realpath(ROOTPATH . $paths->systemDirectory).DIRECTORY_SEPARATOR);
}

/**
 * The path to the writable directory.
 */
if (! defined('WRITEPATH'))
{
	define('WRITEPATH', realpath(ROOTPATH . $paths->writableDirectory).DIRECTORY_SEPARATOR);
}

/**
 * The path to the tests directory
 */
if (! defined('TESTPATH'))
{
	define('TESTPATH', realpath(ROOTPATH . $paths->testsDirectory).DIRECTORY_SEPARATOR);
}

/*
 * ---------------------------------------------------------------
 * GRAB OUR CONSTANTS & COMMON
 * ---------------------------------------------------------------
 */
require_once APPPATH.'Config/Constants.php';

require_once BASEPATH.'Common.php';

/*
 * ---------------------------------------------------------------
 * LOAD OUR AUTOLOADER
 * ---------------------------------------------------------------
 *
 * The autoloader allows all of the pieces to work together
 * in the framework. We have to load it here, though, so
 * that the config files can use the path constants.
 */

require_once BASEPATH.'Autoloader/Autoloader.php';
require_once APPPATH .'Config/Autoload.php';
require_once BASEPATH .'Config/BaseService.php';
require_once APPPATH .'Config/Services.php';

// Use Config\Services as CodeIgniter\Services
if (! class_exists('CodeIgniter\Services', false))
{
	class_alias('Config\Services', 'CodeIgniter\Services');
}

$loader = CodeIgniter\Services::autoloader();
$loader->initialize(new Config\Autoload());
$loader->register();    // Register the loader with the SPL autoloader stack.

// Now load Composer's if it's available
if (file_exists(COMPOSER_PATH))
{
	require_once COMPOSER_PATH;
}

// Load environment settings from .env files
// into $_SERVER and $_ENV
require_once BASEPATH . 'Config/DotEnv.php';

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
$app = new \CodeIgniter\CodeIgniter($appConfig);
$app->initialize();

return $app;
