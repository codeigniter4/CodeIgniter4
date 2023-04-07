<?php

// In app/Config/Routing.php
class Routing extends BaseRouting
{
    // ...
    public string $defaultNamespace = '';
    // ...
}

// Controller is \Users
$routes->get('users', 'Users::index');

// Controller is \Admin\Users
$routes->get('users', 'Admin\Users::index');
