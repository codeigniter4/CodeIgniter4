<?php

$routes->resource('photos', ['controller' => 'Gallery']);
// Would create routes like:
$routes->get('photos', '\App\Controllers\Gallery::index');
