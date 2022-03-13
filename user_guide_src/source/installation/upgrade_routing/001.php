<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

// ...

$routes->add('posts/index', 'Posts::index');
$routes->add('teams/create', 'Teams::create');
$routes->add('teams/update', 'Teams::update');

$routes->add('posts/create', 'Posts::create');
$routes->add('posts/update', 'Posts::update');
$routes->add('drivers/create', 'Drivers::create');
$routes->add('drivers/update', 'Drivers::update');
$routes->add('posts/(:any)', 'Posts::view/$1');
