<?php

use App\Controllers\Pages;

$routes->get('pages', [Pages::class, 'index']);
$routes->get('(:any)', [Pages::class, 'view']);
