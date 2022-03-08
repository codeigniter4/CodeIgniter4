<?php

$routes->presenter('photos');

// Equivalent to the following:
$routes->get('photos/new', 'Photos::new');
$routes->post('photos/create', 'Photos::create');
$routes->post('photos', 'Photos::create');   // alias
$routes->get('photos', 'Photos::index');
$routes->get('photos/show/(:segment)', 'Photos::show/$1');
$routes->get('photos/(:segment)', 'Photos::show/$1');  // alias
$routes->get('photos/edit/(:segment)', 'Photos::edit/$1');
$routes->post('photos/update/(:segment)', 'Photos::update/$1');
$routes->get('photos/remove/(:segment)', 'Photos::remove/$1');
$routes->post('photos/delete/(:segment)', 'Photos::delete/$1');
