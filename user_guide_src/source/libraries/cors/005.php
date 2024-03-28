<?php

use CodeIgniter\Router\RouteCollection;

$routes->group('api', ['filter' => 'cors:api'], static function (RouteCollection $routes): void {
    $routes->resource('user');

    $routes->options('user', static function () {});
    $routes->options('user/(:any)', static function () {});
});
