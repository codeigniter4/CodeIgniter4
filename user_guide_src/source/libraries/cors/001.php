<?php

use CodeIgniter\Router\RouteCollection;

$routes->group('', ['filter' => 'cors'], static function (RouteCollection $routes): void {
    $routes->resource('product');

    $routes->options('product', static function () {
        // Implement processing for normal non-preflight OPTIONS requests,
        // if necessary.
        $response = response();
        $response->setStatusCode(204);
        $response->setHeader('Allow:', 'OPTIONS, GET, POST, PUT, PATCH, DELETE');

        return $response;
    });
    $routes->options('product/(:any)', static function () {});
});
