<?php

$routes->get('users/profile', 'Users::profile', ['as' => 'profile']);

// Redirect to a named route
$routes->addRedirect('users/about', 'profile');
// Redirect to a URI
$routes->addRedirect('users/about', 'users/profile');

// Redirect with placeholder
$routes->get('post/(:num)/comment/(:num)', 'PostsComments::index', ['as' => 'post.comment']);

// Redirect to a URI
$routes->addRedirect('article/(:num)/(:num)', 'post/$1/comment/$2');
// Redirect to a named route
$routes->addRedirect('article/(:num)/(:num)', 'post.comment');
