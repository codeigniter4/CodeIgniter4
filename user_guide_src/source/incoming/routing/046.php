<?php

// This can be overridden in app/Config/Routes.php
$routes->setDefaultNamespace('App');

// Controller is \App\Users
$routes->get('users', 'Users::index');

// Controller is \App\Admin\Users
$routes->get('users', 'Admin\Users::index');
