<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * System URI Routing
 *
 * This file contains any routing to system tools, such as command-line
 * tools for migrations, etc.
 *
 * It is called by Config\Routes, and has the $routes RouteCollection
 * already loaded up and ready for us to use.
 */

// Prevent access to BaseController
$routes->add('BaseController(:any)', static function () {
    throw PageNotFoundException::forPageNotFound();
});

// Prevent access to initController method
$routes->add('(:any)/initController', static function () {
    throw PageNotFoundException::forPageNotFound();
});

// Migrations
$routes->cli('migrations/(:segment)/(:segment)', '\CodeIgniter\Commands\MigrationsCommand::$1/$2');
$routes->cli('migrations/(:segment)', '\CodeIgniter\Commands\MigrationsCommand::$1');
$routes->cli('migrations', '\CodeIgniter\Commands\MigrationsCommand::index');

// CLI Catchall - uses a _remap to call Commands
$routes->cli('ci(:any)', '\CodeIgniter\CLI\CommandRunner::index/$1');
