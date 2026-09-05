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

/*
 * CodeIgniter PHP-Development Server Rewrite Rules
 *
 * This script works with the CLI serve command to help run a seamless
 * development server based around PHP's built-in development
 * server. This file simply tries to mimic Apache's mod_rewrite
 * functionality so the site will operate as normal.
 */

// @codeCoverageIgnoreStart
$uri = urldecode(
    parse_url('https://codeigniter.com' . $_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '',
);

// All request handle by index.php file.
$_SERVER['SCRIPT_NAME'] = '/index.php';

// Full path
$path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . ltrim($uri, '/');

// Security: prevent path traversal outside DOCUMENT_ROOT.
// realpath() resolves ../, symlinks and returns false for non-existing paths.
$realPath    = realpath($path);
$realDocRoot = realpath($_SERVER['DOCUMENT_ROOT']);

// If $path is an existing file or folder within the public folder
// then let the request handle it like normal.
if (
    $uri !== '/'
    && $realPath !== false
    && $realDocRoot !== false
    && ($realPath === $realDocRoot || str_starts_with($realPath, $realDocRoot . DIRECTORY_SEPARATOR))
    && (is_file($realPath) || is_dir($realPath))
) {
    return false;
}

unset($uri, $path, $realPath, $realDocRoot);

// Otherwise, we'll load the index file and let
// the framework handle the request from here.
require_once $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'index.php';
// @codeCoverageIgnoreEnd
