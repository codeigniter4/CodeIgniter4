<?php

// In app/Config/Routing.php
use CodeIgniter\Config\Routing as BaseRouting;

// ...
class Routing extends BaseRouting
{
    // ...
    public string $defaultNamespace = '';
    // ...
}

// In app/Config/Routes.php
// Controller is \Users
$routes->get('users', 'Users::index');

// Controller is \Admin\Users
$routes->get('users', 'Admin\Users::index');
