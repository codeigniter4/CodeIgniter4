<?php

$routes->presenter('photos', ['controller' => 'Gallery']);
// Would create routes like:
$routes->get('photos', '\App\Controllers\Gallery::index');
