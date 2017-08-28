<?php

/**
 * CodeIgniter PHP-Development Server Launcher
 *
 * This script launches the built-in PHP development server
 * making sure that it knows the webroot is in the public folder,
 * and using the rewrite.php file to mimic mod_rewrite functionality.
 *
 * The script is automatically set to the development environment
 * within the rewrite.php file.
 */

$php  = PHP_BINARY;  // command to call PHP

/*
 * Collect any user-supplied options and apply them
 */
$options = getopt(null, ['host:', 'port:']);

$host = $options['host'] ?? 'localhost';
$port = $options['port'] ?? '8080';

/*
 * Get the party started
 */
require_once __DIR__.'/system/CLI/CLI.php';
\CodeIgniter\CLI\CLI::write("CodeIgniter development server started on http://{$host}:{$port}", 'green');
\CodeIgniter\CLI\CLI::write("Press Control-C to stop.");

/*
 * Call PHP's built-in webserver, making sure to set our
 * base path to the public folder, and to use the rewrite file
 * to ensure our environment is set and it simulates basic mod_rewrite.
 */
passthru("{$php} -S {$host}:{$port} -t public/ rewrite.php");
