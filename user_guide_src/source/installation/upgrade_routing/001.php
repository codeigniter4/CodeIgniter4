<?php

namespace Config;

// ...

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// ...

$routes->add('posts/index', 'Posts::index');
$routes->add('teams/create', 'Teams::create');
$routes->add('teams/update', 'Teams::update');

$routes->add('posts/create', 'Posts::create');
$routes->add('posts/update', 'Posts::update');
$routes->add('drivers/create', 'Drivers::create');
$routes->add('drivers/update', 'Drivers::update');
$routes->add('posts/(:segment)', 'Posts::view/$1');

// ...
