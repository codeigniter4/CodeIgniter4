<?php

$routes->group('blog', ['namespace' => 'Acme\Blog\Controllers'], static function ($routes) {
    $routes->get('/', 'Blog::index');
});
