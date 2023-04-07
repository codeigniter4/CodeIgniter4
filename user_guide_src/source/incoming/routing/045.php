<?php

// In app/Config/Routing.php
$defaultNamespace = '';

// Controller is \Users
$routes->get('users', 'Users::index');

// Controller is \Admin\Users
$routes->get('users', 'Admin\Users::index');
