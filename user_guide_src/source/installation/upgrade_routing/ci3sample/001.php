<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// ...

$route['posts/index']  = 'posts/index';
$route['teams/create'] = 'teams/create';
$route['teams/update'] = 'teams/update';

$route['posts/create']   = 'posts/create';
$route['posts/update']   = 'posts/update';
$route['drivers/create'] = 'drivers/create';
$route['drivers/update'] = 'drivers/update';
$route['posts/(:any)']   = 'posts/view/$1';
