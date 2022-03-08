<?php

$routes->group('api', ['filter' => 'api-auth'], static function ($routes) {
    $routes->resource('users');
});
