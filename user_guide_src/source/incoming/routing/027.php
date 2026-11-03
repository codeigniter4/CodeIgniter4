<?php

$routes->group('', ['namespace' => 'Myth\Auth\Controllers'], static function ($routes) {
    $routes->get('login', 'AuthController::login', ['as' => 'login']);
    $routes->post('login', 'AuthController::attemptLogin');
    $routes->get('logout', 'AuthController::logout');
});
