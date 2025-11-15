<?php

use App\Controllers\Product;

$routes->get('product/(:num)/(:num)', [[Product::class, 'index'], '$2/$1']);

// The above code is the same as the following:
$routes->get('product/(:num)/(:num)', 'Product::index/$2/$1');
