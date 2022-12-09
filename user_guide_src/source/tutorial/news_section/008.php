<?php

// ...

use App\Controllers\News;
use App\Controllers\Pages;

$routes->get('news/(:segment)', [News::class, 'view']);
$routes->get('news', [News::class, 'index']);
$routes->get('pages', [Pages::class, 'index']);
$routes->get('(:segment)', [Pages::class, 'view']);

// ...
