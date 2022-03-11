<?php

$routes->group('admin', static function ($routes) {
    $routes->group('users', static function ($routes) {
        $routes->get('list', 'Admin\Users::list');
    });
});
