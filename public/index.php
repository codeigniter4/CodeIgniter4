<?php

/*
 *---------------------------------------------------------------
 * SYSTEM FOLDER NAME
 *---------------------------------------------------------------
 *
 * This variable must contain the name of your "system" folder.
 * Include the path if the folder is not in the same directory
 * as this file.
 */
$system_directory = '../system';

/*
 *---------------------------------------------------------------
 * APPLICATION FOLDER NAME
 *---------------------------------------------------------------
 *
 * If you want this front controller to use a different "application"
 * folder than the default one you can set its name here. The folder
 * can also be renamed or relocated anywhere on your getServer. If
 * you do, use a full getServer path. For more info please see the user guide:
 * http://codeigniter.com/user_guide/general/managing_apps.html
 *
 * NO TRAILING SLASH!
 */
$application_directory = '../application';

/*
 * ---------------------------------------------------------------
 * WRITABLE DIRECTORY NAME
 * ---------------------------------------------------------------
 *
 * This variable must contain the name of your "writable" directory.
 * The writable directory allows you to group all directories that
 * need write permission to a single place that can be tucked away
 * for maximum security, keeping it out of the application and/or
 * system directories.
 */
$writable_directory = '../writable';

/*
 * ---------------------------------------------------------------
 * TESTS DIRECTORY NAME
 * ---------------------------------------------------------------
 *
 * This variable must contain the name of your "tests" directory.
 * The writable directory allows you to group all directories that
 * need write permission to a single place that can be tucked away
 * for maximum security, keeping it out of the application and/or
 * system directories.
 */
$tests_directory = '../tests';

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

$app = require rtrim($system_directory,'/ ').'/bootstrap.php';

/*
 *---------------------------------------------------------------
 * LAUNCH THE APPLICATION
 *---------------------------------------------------------------
 * Now that everything is setup, it's time to actually fire
 * up the engines and make this app do it's thang.
 */
$app->run();
