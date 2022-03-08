<?php

$routes->resource('photos', ['controller' => 'App\Gallery']);

// Would create routes like:
$routes->get('photos', 'App\Gallery::index');
