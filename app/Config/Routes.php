<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

/**
 *HOT RELOAD ROUTE
 */
$routes->get('hotreload/check', 'HotReloadController::check');
