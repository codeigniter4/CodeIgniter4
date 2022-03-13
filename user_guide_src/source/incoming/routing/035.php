<?php

$routes->post('users/delete/(:segment)', 'AdminController::index', ['filter' => 'admin-auth:dual,noreturn']);
