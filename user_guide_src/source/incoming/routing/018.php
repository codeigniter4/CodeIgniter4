<?php

$routes->get('users/profile', 'Users::profile', ['as' => 'profile']);

// Redirect to a named route
$routes->addRedirect('users/about', 'profile');
// Redirect to a URI
$routes->addRedirect('users/about', 'users/profile');
