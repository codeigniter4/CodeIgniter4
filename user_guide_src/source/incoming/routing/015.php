<?php

use App\Controllers\Product;

$routes->get('product/(:num)/(:num)', [Product::class, 'index']);

// The above code is the same as the following:
$routes->get('product/(:num)/(:num)', 'Product::index/$1/$2');
