<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index'); // Commented out or removed

$routes->get('/', 'Login::index');
$routes->get('/login', 'Login::index');
$routes->post('/login/authenticate', 'Login::authenticate');

// Placeholder for chat route removed.

$routes->get('/chat', 'Chat::index'); // Auth check is in Chat::initController
$routes->get('/chat/messages', 'Chat::getMessages');
$routes->post('/chat/send', 'Chat::sendMessage');
$routes->post('/chat/send_private', 'Chat::sendPrivateMessage');
$routes->post('/chat/rewrite', 'Chat::getOpenAIRewrite'); // For later step
