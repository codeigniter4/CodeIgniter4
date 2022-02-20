<?php

$routes->group('admin', function ($routes) {
    $routes->group('users', function ($routes) {
        $routes->get('list', 'Admin\Users::list');
    });
});
