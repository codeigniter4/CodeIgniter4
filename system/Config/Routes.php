<?php

/**
 * --------------------------------------------------------------------
 * System URI Routing
 * --------------------------------------------------------------------
 * This file contains any routing to system tools, such as command-line
 * tools for migrations, etc.
 *
 * It is called by Config\Routes, and has the $routes RouteCollection
 * already loaded up and ready for us to use.
 */

// Migrations
$routes->cli('migrations/(:segment)/(:segment)', '\CodeIgniter\Commands\MigrationsCommand::$1/$2');
$routes->cli('migrations/(:segment)',            '\CodeIgniter\Commands\MigrationsCommand::$1');
