<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/i/(:num)', 'Home::order/$1'); // Assuming order page is handled in Home or PaymentController, prompt says /i/{number}
$routes->get('/AjaxSearchNumber', 'Home::ajaxSearchNumber');

// Payment routes
$routes->post('/payment/start', 'PaymentController::start');
$routes->get('/payment/callback', 'PaymentController::callback');

// Admin Routes
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    // Auth
    $routes->get('login', 'AuthController::login');
    $routes->post('login', 'AuthController::attemptLogin');
    $routes->get('logout', 'AuthController::logout');

    // Dashboard & Protected Routes
    $routes->group('', ['filter' => 'adminAuth'], function($routes) {
        $routes->get('dashboard', 'DashboardController::index');

        // Admins Management
        $routes->get('admins', 'AdminsController::index');
        $routes->post('admins/create', 'AdminsController::create');
        $routes->post('admins/update/(:num)', 'AdminsController::update/$1');

        // Simcards Management
        $routes->get('simcards', 'SimcardsController::index');
        $routes->post('simcards/create', 'SimcardsController::create');
        $routes->post('simcards/update/(:num)', 'SimcardsController::update/$1');
        $routes->get('simcards/delete/(:num)', 'SimcardsController::delete/$1');
        $routes->post('simcards/import', 'SimcardsController::import');

        // Orders Management
        $routes->get('orders', 'OrdersController::index');
    });
});
