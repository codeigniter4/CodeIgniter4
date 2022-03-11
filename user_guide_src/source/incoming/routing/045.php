<?php

$routes->setDefaultNamespace('');

// Controller is \Users
$routes->get('users', 'Users::index');

// Controller is \Admin\Users
$routes->get('users', 'Admin\Users::index');
