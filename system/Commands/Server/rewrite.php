<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*
 * CodeIgniter PHP-Development Server Rewrite Rules
 *
 * This script works with the CLI serve command to help run a seamless
 * development server based around PHP's built-in development
 * server. This file simply tries to mimic Apache's mod_rewrite
 * functionality so the site will operate as normal.
 */

// @codeCoverageIgnoreStart
// Avoid this file run when listing commands
if (PHP_SAPI === 'cli') {
    return;
}

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Front Controller path - expected to be in the default folder
$fcpath = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR;

// Full path
$path = $fcpath . ltrim($uri, '/');

// If $path is an existing file or folder within the public folder
// then let the request handle it like normal.
if ($uri !== '/' && (is_file($path) || is_dir($path))) {
    return false;
}

// Otherwise, we'll load the index file and let
// the framework handle the request from here.
require_once $fcpath . 'index.php';
// @codeCoverageIgnoreEnd
