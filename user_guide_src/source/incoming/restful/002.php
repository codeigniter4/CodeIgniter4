<?php

$routes->resource('photos', ['websafe' => 1]);

// The following equivalent routes are created:
$routes->post('photos/(:segment)/delete', 'Photos::delete/$1');
$routes->post('photos/(:segment)', 'Photos::update/$1');
