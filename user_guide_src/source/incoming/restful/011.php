<?php

$routes->presenter('photos', ['controller' => 'Gallery']);
// Would create routes like:
$routes->get('photos', '\App\Controllers\Gallery::index');

$routes->presenter('photos', ['controller' => '\App\Gallery']);
// Would create routes like:
$routes->get('photos', '\App\Gallery::index');
