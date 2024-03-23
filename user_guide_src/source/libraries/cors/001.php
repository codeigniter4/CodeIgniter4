<?php

use CodeIgniter\Router\RouteCollection;

$routes->group('', ['filter' => 'cors'], static function (RouteCollection $routes): void {
    $routes->options('product', '\Dummy');
    $routes->options('product/(:any)', '\Dummy');
    $routes->resource('product');
});
