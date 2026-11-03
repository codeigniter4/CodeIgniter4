<?php

$routes->resource('photos', ['placeholder' => '(:num)']);

// Generates routes like:
$routes->get('photos/(:num)', 'Photos::show/$1');
