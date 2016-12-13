#!/usr/bin/env php
<?php

use CodeIgniter\CLI\CLI;

/**
 * --------------------------------------------------------------------
 * CodeIgniter command-line tools
 * --------------------------------------------------------------------
 * The main entry point into the CLI system and allows you to run
 * commands and perform maintenance on your application.
 *
 * Because CodeIgniter can handle CLI requests as just another web request
 * this class mainly acts as a passthru to the framework itself.
 */

// Grab the CLI class, though, so we can use it to provide user feedback.
require __DIR__.'/system/CLI/CLI.php';

// Grab our Console
require __DIR__.'/system/CLI/Console.php';
$console = new \CodeIgniter\CLI\Console();

// Refuse to run when called from php-cgi
if (substr(php_sapi_name(), 0, 3) == 'cgi')
{
    die("The cli tool is not supported when running php-cgi. It needs php-cli to function!\n\n");
}

// We want errors to be shown when using it from the CLI.
error_reporting(-1);
ini_set('display_errors', 1);

// Show basic information before we do anything else.
$console->showHeader();

// fire off the command the main framework.
$console->run();
