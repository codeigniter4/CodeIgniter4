<?php

// First you need to enable processing of the routes queue by priority.
$routes->setPrioritize();

// Config\Routes
$routes->get('(.*)', 'Posts::index', ['priority' => 1]);

// Modules\Acme\Config\Routes
$routes->get('admin', 'Admin::index');

// The "admin" route will now be processed before the wildcard route.
