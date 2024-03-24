<?php

use CodeIgniter\Router\RouteCollection;

$routes->group('api', ['filter' => 'cors:api'], static function (RouteCollection $routes): void {
    $routes->options('user', '\Dummy');
    $routes->options('user/(:any)', '\Dummy');
    $routes->resource('user');
});
