<?php

$routes->group('api', ['filter' => 'api-auth'], function ($routes) {
    $routes->resource('users');
});
