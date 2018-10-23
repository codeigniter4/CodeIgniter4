<?php

// Valid PHP Version?
$minPHPVersion = '7.1';
if (phpversion() < $minPHPVersion)
{
	die("You PHP version must be {$minPHPVersion} or higher to run CodeIgniter. Current version: ". phpversion());
}
unset($minPHPVersion);

// Path to the front controller (this file)
define('FCPATH', __DIR__.DIRECTORY_SEPARATOR);

// Location of the Paths config file.
// This is the first of two lines that might need to be changed, depending on your folder structure.
$pathsPath = FCPATH . '../application/Config/Paths.php';

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

// Location of the framework bootstrap file.
// This is the second of two lines that might need to be changed, depending on your folder structure.
$app = require FCPATH . '../system/bootstrap.php';

/*
 *---------------------------------------------------------------
 * LAUNCH THE APPLICATION
 *---------------------------------------------------------------
 * Now that everything is setup, it's time to actually fire
 * up the engines and make this app do its thang.
 */
$app->run();
