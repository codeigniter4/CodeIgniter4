<?php
/**
 * CodeIgniter PHP-Development Server Rewrite Rules
 *
 * This script works with serve.php to help run a seamless
 * development server based around PHP's built-in development
 * server. This file simply tries to mimic Apache's mod_rewrite
 * functionality so the site will operate as normal.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

$path = __DIR__.'/public/'.ltrim($uri,'/');

// If $path is an existing file or folder within the public folder
// then let the request handle it like normal.
if ($uri !== '/' && (is_file($path) || is_dir($path)))
{
    return false;
}

// Otherwise, we'll load the index file and let
// the framework handle the request from here.

// If we're serving the site locally, then we need
// to let the application know that we're in development mode
$_SERVER['CI_ENVIRONMENT'] = 'development';

require_once __DIR__.'/public/index.php';
