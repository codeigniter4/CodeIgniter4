<?php

$routes->group('admin', ['filter' => 'myfilter:config'], static function ($routes) {
    $routes->get('/', 'Admin\Admin::index');

    $routes->group('users', ['filter' => 'myfilter:region'], static function ($routes) {
        $routes->get('list', 'Admin\Users::list');
    });
});
