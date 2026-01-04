<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Auth Routes
$routes->group('', ['namespace' => 'App\Controllers\Auth'], function ($routes) {
    $routes->get('login', 'LoginController::index');
    $routes->post('login', 'LoginController::login');
    $routes->get('register', 'RegisterController::index');
    $routes->post('register', 'RegisterController::register');
    $routes->get('logout', 'LoginController::logout');
    $routes->get('forgot-password', 'PasswordController::forgot');
});

// Dashboard Routes
$routes->group('dashboard', ['filter' => 'auth', 'namespace' => 'App\Controllers\Dashboard'], function ($routes) {
    $routes->get('/', 'DashboardController::index');
    $routes->get('projects', '\App\Controllers\Project\ProjectController::index');
});

// Project Routes
$routes->group('projects', ['filter' => 'auth', 'namespace' => 'App\Controllers\Project'], function ($routes) {
    $routes->get('create', 'ProjectController::create');
    $routes->post('create', 'ProjectController::store');
    $routes->get('(:num)', 'ProjectController::show/$1'); // Workspace
});

// API Routes
$routes->group('api', ['filter' => 'auth', 'namespace' => 'App\Controllers\Api'], function ($routes) {
    $routes->post('upload', 'UploadController::upload');
    $routes->post('chat/send', 'ChatController::sendMessage');
    $routes->post('ai/analyze', 'ChatController::analyze');
    $routes->get('chat/history/(:num)', 'ChatController::history/$1');
    $routes->get('pages/(:num)', 'PageController::getPages/$1');
    $routes->get('page/preview/(:num)', 'PageController::getPreview/$1');
});

// Admin Routes
$routes->group('admin', ['filter' => 'auth', 'namespace' => 'App\Controllers\Admin'], function ($routes) {
    // $routes->get('/', 'AdminDashboard::index');
});
