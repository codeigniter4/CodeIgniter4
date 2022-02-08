<?php

$routes->group('api', ['namespace' => 'App\API\v1'], function ($routes) {
    $routes->resource('users');
});
