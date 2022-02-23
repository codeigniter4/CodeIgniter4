<?php

$routes->group('blog', ['namespace' => 'Acme\Blog\Controllers'], function ($routes) {
    $routes->get('/', 'Blog::index');
});
