<?php

use CodeIgniter\Router\RouteCollection;

$routes->group('api', ['filter' => 'cors:api'], static function (RouteCollection $routes): void {
    $routes->resource('user');

    $routes->options('user', static function () {
        // Implement processing for normal non-preflighted OPTIONS requests,
        // if necessary.
        $response = response();
        $response->setStatusCode(204);
        $response->setHeader('Allow:', 'OPTIONS, GET, POST, PUT, PATCH, DELETE');

        return $response;
    });
    $routes->options('user/(:any)', static function () {
        // Implement processing for normal non-preflight OPTIONS requests,
        // if necessary.
        $response = response();
        $response->setStatusCode(204);
        $response->setHeader('Allow:', 'OPTIONS, GET, POST, PUT, PATCH, DELETE');

        return $response;
    });
});
