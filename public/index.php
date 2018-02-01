<?php

// Location to the Paths config file.
// This should be the only line you need to
// edit in this file.
$pathsPath = '../application/Config/Paths.php';

// Path to the front controller (this file)
define('FCPATH', __DIR__.DIRECTORY_SEPARATOR);

/*
 *---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION
 *---------------------------------------------------------------
 * This process sets up the path constants, loads and registers
 * our autoloader, along with Composer's, loads our constants
 * and fires up an environment-specific bootstrapping.
 */

// Ensure the current directory is pointing to the front controller's directory
chdir(__DIR__);

// Load our paths config file
require $pathsPath;
$paths = new Config\Paths();

$app = require rtrim($paths->systemDirectory,DIRECTORY_SEPARATOR) . '/bootstrap.php';

/*
 *---------------------------------------------------------------
 * LAUNCH THE APPLICATION
 *---------------------------------------------------------------
 * Now that everything is setup, it's time to actually fire
 * up the engines and make this app do it's thang.
 */
$app->run();
