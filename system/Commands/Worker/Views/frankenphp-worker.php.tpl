<?php

/**
 * CodeIgniter 4 - FrankenPHP Worker Mode Entry Point
 *
 * This file implements the FrankenPHP worker pattern for CodeIgniter 4.
 * The framework boots once and handles multiple requests in the same process,
 * significantly improving performance (30-50% or more).
 *
 * @see https://frankenphp.dev/docs/worker/
 */

use CodeIgniter\Boot;
use CodeIgniter\Config\Factories;
use CodeIgniter\Config\Services;
use CodeIgniter\Database\Config as DatabaseConfig;
use CodeIgniter\Events\Events;
use Config\Paths;
use Config\WorkerMode;

/*
 *---------------------------------------------------------------
 * CHECK PHP VERSION
 *---------------------------------------------------------------
 */

$minPhpVersion = '8.2';
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    http_response_code(503);
    exit("PHP {$minPhpVersion}+ required. Current: " . PHP_VERSION);
}

/*
 *---------------------------------------------------------------
 * SET THE CURRENT DIRECTORY
 *---------------------------------------------------------------
 */

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

/*
 *---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION (ONCE)
 *---------------------------------------------------------------
 */

// This is the line that might need to be changed, depending on your folder structure.
require FCPATH . '../app/Config/Paths.php';
// ^^^ Change this line if you move your application folder
$paths = new Paths();

require $paths->systemDirectory . '/Boot.php';

// One-time boot - loads autoloader, environment, helpers, etc.
$app = Boot::bootWorker($paths);

// Prevent worker termination on client disconnect
ignore_user_abort(true);

/** @var WorkerMode $workerConfig */
$workerConfig = config('WorkerMode');

/*
 *---------------------------------------------------------------
 * REQUEST HANDLER
 *---------------------------------------------------------------
 */

$handler = static function () use ($app, $workerConfig) {
    // Reconnect database connections before handling request
    DatabaseConfig::reconnectForWorkerMode();

    // Reconnect cache connection before handling request
    Services::reconnectCacheForWorkerMode();

    // Reset request-specific state
    $app->resetForWorkerMode();

    // Update superglobals with fresh request data
    service('superglobals')
        ->setServerArray($_SERVER)
        ->setGetArray($_GET)
        ->setPostArray($_POST)
        ->setCookieArray($_COOKIE)
        ->setFilesArray($_FILES)
        ->setRequestArray($_REQUEST);

    try {
        $app->run();
    } catch (Throwable $e) {
        Services::exceptions()->exceptionHandler($e);
    }

    if ($workerConfig->forceGarbageCollection) {
        // Force garbage collection
        gc_collect_cycles();
    }
};

/*
 *---------------------------------------------------------------
 * WORKER REQUEST LOOP
 *---------------------------------------------------------------
 */

while (frankenphp_handle_request($handler)) {
    // Close session
    if (Services::has('session')) {
        Services::session()->close();
    }

    // Cleanup connections with uncommitted transactions
    DatabaseConfig::cleanupForWorkerMode();

    // Reset factories
    Factories::reset();

    // Reset services except persistent ones
    Services::resetForWorkerMode($workerConfig);

    if (CI_DEBUG) {
        Events::cleanupForWorkerMode();
        Services::toolbar()->reset();
    }
}
